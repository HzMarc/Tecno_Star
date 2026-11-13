<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/app/Models/OrderModel.php';

final class AdminController
{
    private function authorize(): bool
    {
        return Auth::requireRole('Administrador') !== null;
    }

    public function index(): void
    {
        if (!$this->authorize()) {
            return;
        }

        $database = Database::connection();
        (new OrderModel($database))->expireDue();
        $metrics = [
            'products' => (int) $database->query("SELECT COUNT(*) FROM tbproductos WHERE estado = 'activo'")->fetchColumn(),
            'users' => (int) $database->query("SELECT COUNT(*) FROM tbusuarios WHERE estado = 'activo'")->fetchColumn(),
            'stock' => (float) $database->query('SELECT COALESCE(SUM(stock_actual - stock_reservado), 0) FROM tbinventario')->fetchColumn(),
            'pending_orders' => (int) $database->query("SELECT COUNT(*) FROM tbpedidos WHERE estado = 'pendiente'")->fetchColumn(),
            'sales_total' => (float) $database->query("SELECT COALESCE(SUM(total), 0) FROM tbventas WHERE estado = 'pagada'")->fetchColumn(),
        ];

        $categoryStatement = $database->query(
            "SELECT c.nombre_categoria AS name, COUNT(p.id_producto) AS total
             FROM tbcategorias c
             LEFT JOIN tbproductos p ON p.id_categoria = c.id_categoria AND p.estado = 'activo'
             WHERE c.estado = 'activo'
             GROUP BY c.id_categoria, c.nombre_categoria
             HAVING total > 0
             ORDER BY total DESC
             LIMIT 6"
        );
        $categories = $categoryStatement->fetchAll();

        $orderStatement = $database->query(
            "SELECT estado AS name, COUNT(*) AS total
             FROM tbpedidos
             GROUP BY estado
             ORDER BY total DESC"
        );
        $ordersByStatus = $orderStatement->fetchAll();
        $orderLabels = OrderModel::LABELS;

        $zone = new DateTimeZone('America/El_Salvador');
        $firstMonth = (new DateTimeImmutable('today', $zone))->modify('first day of this month')->modify('-5 months');
        $salesStart = $firstMonth->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $monthNames = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $monthlySalesMap = [];
        $topProducts = [];
        if (OrderModel::enabled($database) && OrderModel::cashEnabled($database)) {
            $trendStatement = $database->prepare(
                "SELECT DATE_FORMAT(DATE_SUB(fecha_pago, INTERVAL 6 HOUR),'%Y-%m') AS month,COALESCE(SUM(total),0) AS total
                 FROM tbventas
                 WHERE cobro_solicitud IS NOT NULL AND estado='pagada' AND fecha_pago>=?
                 GROUP BY month ORDER BY month"
            );
            $trendStatement->execute([$salesStart]);
            foreach ($trendStatement->fetchAll() as $row) $monthlySalesMap[$row['month']] = (float)$row['total'];

            $topProductsStatement = $database->prepare(
                "SELECT d.nombre_producto AS name,SUM(d.cantidad) AS units
                 FROM tbventas s
                 JOIN tbretirodetalles d ON d.id_pedido=s.id_pedido
                 WHERE s.cobro_solicitud IS NOT NULL AND s.estado='pagada' AND s.id_pedido IS NOT NULL AND s.fecha_pago>=?
                 GROUP BY d.id_producto,d.nombre_producto
                 ORDER BY units DESC,d.nombre_producto ASC LIMIT 6"
            );
            $topProductsStatement->execute([$salesStart]);
            $topProducts = $topProductsStatement->fetchAll();
        }
        $monthlySales = [];
        for ($month = clone $firstMonth; $month <= new DateTimeImmutable('first day of this month', $zone); $month = $month->modify('+1 month')) {
            $key = $month->format('Y-m');
            $monthlySales[] = ['month'=>$key,'label'=>$monthNames[(int)$month->format('n')-1].' '.$month->format('Y'),'total'=>$monthlySalesMap[$key]??0.0];
        }

        $stockStatusStatement = $database->query(
            "SELECT CASE
                WHEN i.stock_actual-i.stock_reservado<=0 THEN 'Agotado'
                WHEN i.stock_actual-i.stock_reservado<=i.stock_minimo THEN 'Pocas unidades'
                ELSE 'Disponible'
             END AS name,COUNT(*) AS total
             FROM tbinventario i
             GROUP BY name
             ORDER BY FIELD(name,'Agotado','Pocas unidades','Disponible')"
        );
        $stockStatuses = $stockStatusStatement->fetchAll();

        require BASE_PATH . '/app/Views/admin/index.php';
    }

    public function products(): void
    {
        require_once BASE_PATH . '/app/Controllers/CatalogAdminController.php';
        (new CatalogAdminController())->products();
    }

    public function inventory(): void
    {
        if (!$this->authorize()) {
            return;
        }

        $database = Database::connection();
        (new OrderModel($database))->expireDue();
        $inventorySummary = $database->query('SELECT COUNT(*) AS total, COALESCE(SUM(stock_actual-stock_reservado),0) AS available, COALESCE(SUM((stock_actual-stock_reservado)<=stock_minimo),0) AS low_stock FROM tbinventario')->fetch();
        $statement = $database->query(
            "SELECT p.id_producto, p.codigo_producto, p.nombre_producto,
                    COALESCE(m.nombre_marca, 'Sin marca') AS marca,
                    i.stock_actual, i.stock_reservado, i.stock_minimo,
                    (i.stock_actual - i.stock_reservado) AS stock_disponible,
                    CASE
                        WHEN (i.stock_actual - i.stock_reservado) <= 0 THEN 'Agotado'
                        WHEN (i.stock_actual - i.stock_reservado) <= i.stock_minimo THEN 'Bajo'
                        ELSE 'Disponible'
                    END AS stock_status
             FROM tbinventario i
             INNER JOIN tbproductos p ON p.id_producto = i.id_producto
             LEFT JOIN tbmarcas m ON m.id_marca = p.id_marca
             ORDER BY stock_disponible ASC, p.nombre_producto LIMIT 12"
        );
        $inventory = $statement->fetchAll();
        $historyProducts = $database->query('SELECT p.id_producto,p.nombre_producto FROM tbproductos p JOIN tbinventario i ON i.id_producto=p.id_producto ORDER BY p.nombre_producto')->fetchAll();
        $historyActors = $database->query('SELECT DISTINCT id_usuario,responsable FROM tbstockmovimientos WHERE id_usuario IS NOT NULL ORDER BY responsable')->fetchAll();

        require BASE_PATH . '/app/Views/admin/inventory.php';
    }
}
