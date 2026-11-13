<?php

declare(strict_types=1);

final class AdminReportsModel
{
    private const ZONE = 'America/El_Salvador';

    public function __construct(private PDO $database) {}

    public function report(array $input, bool $services, bool $reinvestment): array
    {
        $today = new DateTimeImmutable('today', new DateTimeZone(self::ZONE));
        $from = $this->day($input['from'] ?? $today->modify('first day of this month')->format('Y-m-d'));
        $to = $this->day($input['to'] ?? $today->format('Y-m-d'));
        if ($from > $to) throw new InvalidArgumentException('El rango de fechas está invertido.');
        if ($from->diff($to)->days > 3660) throw new InvalidArgumentException('El rango del reporte no puede superar diez años.');

        $utc = new DateTimeZone('UTC');
        $start = $from->setTime(0, 0)->setTimezone($utc)->format('Y-m-d H:i:s');
        $end = $to->modify('+1 day')->setTime(0, 0)->setTimezone($utc)->format('Y-m-d H:i:s');
        $salesWhere = ' WHERE s.cobro_solicitud IS NOT NULL AND s.fecha_pago >= ? AND s.fecha_pago < ?';

        $serviceSales = $services
            ? "(SELECT COALESCE(SUM(s.total),0) FROM tbventas s WHERE s.cobro_solicitud IS NOT NULL AND s.estado='pagada' AND s.id_solicitud_servicio IS NOT NULL AND s.fecha_pago >= ? AND s.fecha_pago < ?) AS service_sales,
                (SELECT COUNT(*) FROM tbventas s WHERE s.cobro_solicitud IS NOT NULL AND s.estado='pagada' AND s.id_solicitud_servicio IS NOT NULL AND s.fecha_pago >= ? AND s.fecha_pago < ?) AS service_count"
            : '0 AS service_sales, 0 AS service_count';
        $summary = $this->database->prepare(
            "SELECT
                (SELECT COALESCE(SUM(s.total),0) FROM tbventas s WHERE s.cobro_solicitud IS NOT NULL AND s.estado='pagada' AND s.fecha_pago >= ? AND s.fecha_pago < ?) AS paid,
                (SELECT COALESCE(SUM(s.total),0) FROM tbventas s WHERE s.cobro_solicitud IS NOT NULL AND s.estado='pagada' AND s.id_pedido IS NOT NULL AND s.fecha_pago >= ? AND s.fecha_pago < ?) AS product_sales,
                $serviceSales,
                (SELECT COUNT(*) FROM tbventas s WHERE s.cobro_solicitud IS NOT NULL AND s.estado='pagada' AND s.fecha_pago >= ? AND s.fecha_pago < ?) AS paid_count"
        );
        $summaryParams = [$start,$end,$start,$end];
        if ($services) array_push($summaryParams,$start,$end,$start,$end);
        array_push($summaryParams,$start,$end);
        $summary->execute($summaryParams);
        $totals = $summary->fetch() ?: [];
        foreach (['paid','product_sales','service_sales'] as $key) $totals[$key] = (float) ($totals[$key] ?? 0);
        foreach (['paid_count','service_count'] as $key) $totals[$key] = (int) ($totals[$key] ?? 0);
        $totals['average'] = $totals['paid_count'] > 0 ? $totals['paid'] / $totals['paid_count'] : 0.0;

        $investment = ['replacement'=>0.0,'growth'=>0.0,'total'=>0.0,'missing'=>0];
        if ($reinvestment) {
            $query = $this->database->prepare(
                "SELECT COALESCE(SUM(CASE WHEN s.estado='pagada' THEN r.monto_reposicion ELSE 0 END),0) AS replacement,
                        COALESCE(SUM(CASE WHEN s.estado='pagada' THEN r.monto_crecimiento ELSE 0 END),0) AS growth,
                        COALESCE(SUM(CASE WHEN s.estado='pagada' THEN r.total_reinversion ELSE 0 END),0) AS total,
                        SUM(CASE WHEN s.estado='pagada' AND s.id_pedido IS NOT NULL AND r.id_venta IS NULL THEN 1 ELSE 0 END) AS missing
                 FROM tbventas s
                 LEFT JOIN tbreinversiones r ON r.id_venta=s.id_venta" . $salesWhere
            );
            $query->execute([$start, $end]);
            $investment = $query->fetch() ?: $investment;
            foreach (['replacement','growth','total'] as $key) $investment[$key] = (float) ($investment[$key] ?? 0);
            $investment['missing'] = (int) ($investment['missing'] ?? 0);
        }

        $byMonth = [];
        $trend = $this->database->prepare(
            "SELECT DATE_FORMAT(DATE_SUB(s.fecha_pago, INTERVAL 6 HOUR),'%Y-%m') AS month,COALESCE(SUM(s.total),0) AS amount
             FROM tbventas s WHERE s.cobro_solicitud IS NOT NULL AND s.estado='pagada' AND s.fecha_pago >= ? AND s.fecha_pago < ? GROUP BY month ORDER BY month"
        );
        $trend->execute([$start,$end]);
        foreach ($trend->fetchAll() as $row) $byMonth[$row['month']] = (float)$row['amount'];
        $months = [];
        $monthNames = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $cursor = $from->modify('first day of this month');
        $last = $to->modify('first day of this month');
        while ($cursor <= $last) {
            $key = $cursor->format('Y-m');
            $months[] = ['month'=>$key, 'label'=>$monthNames[(int)$cursor->format('n')-1] . ' ' . $cursor->format('Y'), 'paid'=>$byMonth[$key] ?? 0.0];
            $cursor = $cursor->modify('+1 month');
        }

        $best = $this->database->prepare(
            "SELECT d.codigo_producto AS code,d.nombre_producto AS name,
                    SUM(d.cantidad) AS units,SUM(d.subtotal) AS revenue
             FROM tbventas s
             JOIN tbpedidos o ON o.id_pedido=s.id_pedido
             JOIN tbretirodetalles d ON d.id_pedido=o.id_pedido" .
            $salesWhere . " AND s.estado='pagada'
             GROUP BY d.id_producto,d.codigo_producto,d.nombre_producto
             ORDER BY units DESC,revenue DESC,d.nombre_producto ASC LIMIT 10"
        );
        $best->execute([$start, $end]);

        $stockSummary = $this->database->query(
            "SELECT COUNT(*) AS products,
                    COALESCE(SUM(i.stock_actual-i.stock_reservado),0) AS units,
                    COALESCE(SUM(CASE WHEN i.stock_actual-i.stock_reservado=0 THEN 1 ELSE 0 END),0) AS out_count,
                    COALESCE(SUM(CASE WHEN i.stock_actual-i.stock_reservado>0 AND i.stock_actual-i.stock_reservado<=i.stock_minimo THEN 1 ELSE 0 END),0) AS low_count,
                    COALESCE(SUM((i.stock_actual-i.stock_reservado)*p.precio_compra),0) AS cost_value
             FROM tbinventario i JOIN tbproductos p ON p.id_producto=i.id_producto"
        )->fetch() ?: [];
        foreach (['products','units','out_count','low_count'] as $key) $stockSummary[$key] = (float) ($stockSummary[$key] ?? 0);
        $stockSummary['products'] = (int) $stockSummary['products'];
        $stockSummary['out_count'] = (int) $stockSummary['out_count'];
        $stockSummary['low_count'] = (int) $stockSummary['low_count'];
        $stockSummary['cost_value'] = (float) ($stockSummary['cost_value'] ?? 0);

        $stockRows = $this->database->query(
            "SELECT p.codigo_producto AS code,p.nombre_producto AS name,
                    i.stock_actual-i.stock_reservado AS available,i.stock_minimo AS minimum
             FROM tbinventario i JOIN tbproductos p ON p.id_producto=i.id_producto
             WHERE i.stock_actual-i.stock_reservado<=i.stock_minimo
             ORDER BY (i.stock_actual-i.stock_reservado) ASC,p.nombre_producto ASC"
        )->fetchAll();
        $outOfStock = array_values(array_filter($stockRows, static fn(array $row): bool => (float)$row['available'] <= 0));
        $lowStock = array_values(array_filter($stockRows, static fn(array $row): bool => (float)$row['available'] > 0));

        return [
            'from'=>$from->format('Y-m-d'), 'to'=>$to->format('Y-m-d'),
            'summary'=>$totals, 'reinvestment'=>$investment,
            'reinvestment_enabled'=>$reinvestment, 'services_enabled'=>$services,
            'months'=>$months, 'best_sellers'=>$best->fetchAll(),
            'stock'=>$stockSummary, 'out_of_stock'=>$outOfStock, 'low_stock'=>$lowStock,
        ];
    }

    private function day(mixed $value): DateTimeImmutable
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) throw new InvalidArgumentException('Selecciona un rango de fechas válido.');
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone(self::ZONE));
        if (!$date || $date->format('Y-m-d') !== $value) throw new InvalidArgumentException('Selecciona un rango de fechas válido.');
        return $date;
    }
}
