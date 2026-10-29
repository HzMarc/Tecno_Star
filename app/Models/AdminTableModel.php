<?php

declare(strict_types=1);

final class AdminTableModel
{
    public function __construct(private PDO $database) {}

    private function integer(array $input, string $key, int $default, int $min, int $max): int
    {
        $value = $input[$key] ?? (string) $default;
        if (!is_string($value) || !ctype_digit($value) || (float) $value < $min || (float) $value > $max) throw new InvalidArgumentException('Parámetros de paginación inválidos.');
        return (int) $value;
    }

    public function data(string $kind, array $input, ?int $owner=null): array
    {
        $draw = $this->integer($input, 'draw', 0, 0, 2147483647);
        $start = $this->integer($input, 'start', 0, 0, 1000000000);
        $length = $this->integer($input, 'length', 12, 1, 100);
        $stock = 'COALESCE(i.stock_actual - i.stock_reservado, 0)';
        $joins = ' FROM tbproductos p INNER JOIN tbcategorias c ON c.id_categoria=p.id_categoria LEFT JOIN tbmarcas m ON m.id_marca=p.id_marca LEFT JOIN tbinventario i ON i.id_producto=p.id_producto';
        if(in_array($kind,['sales','purchases'],true)){
            $joins=' FROM tbventas s JOIN tbpedidos o ON o.id_pedido=s.id_pedido';$select='s.*,o.codigo_pedido';
            $columns=$kind==='purchases'?['s.codigo_venta','o.codigo_pedido','s.fecha_pago','s.total','s.estado',null]:['s.codigo_venta','o.codigo_pedido','s.fecha_pago','s.total','s.recibido','s.cambio','s.estado',null];$searchColumns=['s.codigo_venta','o.codigo_pedido','s.estado'];$defaultOrder='s.id_venta DESC';$tie='s.id_venta';
        }elseif($kind==='users'){
            $joins=' FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol LEFT JOIN tbclientes c ON c.id_usuario=u.id_usuario LEFT JOIN tbpersonalpermisos pe ON pe.id_usuario=u.id_usuario';
            $select='u.id_usuario,u.nombres,u.apellidos,u.correo,u.estado,u.cuenta_revision,r.nombre_rol,c.telefono,pe.pedidos AS permiso_pedidos';$columns=['u.nombres','u.correo','r.nombre_rol','u.estado','pe.pedidos',null];$searchColumns=['u.nombres','u.apellidos','u.correo','r.nombre_rol','u.estado'];$defaultOrder='u.id_usuario DESC';$tie='u.id_usuario';
        }elseif ($kind === 'orders') {
            $joins=' FROM tbpedidos o';$select="o.*,(SELECT s.estado FROM tbventas s WHERE s.id_pedido=o.id_pedido AND s.cobro_solicitud IS NOT NULL LIMIT 1) AS payment_state";
            $columns=['o.codigo_pedido','o.contacto_nombre','o.created_at','o.total','o.estado',null];
            $searchColumns=['o.codigo_pedido','o.contacto_nombre','o.contacto_correo','o.estado'];
            $defaultOrder='o.id_pedido DESC';$tie='o.id_pedido';
        } elseif ($kind === 'products') {
            $select = "p.*, c.nombre_categoria AS categoria, c.estado AS category_state, COALESCE(m.nombre_marca,'Sin marca') AS marca, COALESCE(m.estado,'activo') AS brand_state, $stock AS stock";
            $columns = ['p.nombre_producto','p.codigo_producto','c.nombre_categoria','p.precio_venta',$stock,'p.estado',null];
            $searchColumns = ['p.nombre_producto','p.codigo_producto','c.nombre_categoria','m.nombre_marca','CAST(p.precio_venta AS CHAR)',"CAST($stock AS CHAR)",'p.estado'];
            $defaultOrder = 'p.id_producto DESC'; $tie = 'p.id_producto';
        } elseif ($kind === 'inventory') {
            $select = "p.id_producto,p.codigo_producto,p.nombre_producto,COALESCE(m.nombre_marca,'Sin marca') AS marca,COALESCE(i.stock_actual,0) AS stock_actual,COALESCE(i.stock_reservado,0) AS stock_reservado,COALESCE(i.stock_minimo,0) AS stock_minimo,$stock AS stock_disponible,CASE WHEN $stock <= 0 THEN 'Agotado' WHEN $stock <= COALESCE(i.stock_minimo,0) THEN 'Bajo' ELSE 'Disponible' END AS stock_status";
            $columns = ['p.nombre_producto','p.codigo_producto','i.stock_actual','i.stock_reservado',$stock,'i.stock_minimo',"CASE WHEN $stock <= 0 THEN 0 WHEN $stock <= COALESCE(i.stock_minimo,0) THEN 1 ELSE 2 END",null];
            $searchColumns = ['p.nombre_producto','p.codigo_producto','m.nombre_marca','CAST(i.stock_actual AS CHAR)','CAST(i.stock_reservado AS CHAR)',"CAST($stock AS CHAR)",'CAST(i.stock_minimo AS CHAR)',"CASE WHEN $stock <= 0 THEN 'Agotado' WHEN $stock <= COALESCE(i.stock_minimo,0) THEN 'Bajo' ELSE 'Disponible' END"];
            $defaultOrder = "$stock ASC,p.nombre_producto ASC"; $tie = 'p.id_producto';
            // El inventario mantiene el mismo universo de registros que su listado HTML.
            $joins = str_replace('LEFT JOIN tbinventario', 'INNER JOIN tbinventario', $joins);
        } elseif ($kind === 'movements') {
            $joins = ' FROM tbstockmovimientos h JOIN tbproductos p ON p.id_producto=h.id_producto';
            $select = 'h.*,p.nombre_producto,p.codigo_producto';
            $columns = ['h.created_at','p.nombre_producto','h.tipo','h.cantidad','h.stock_antes','h.stock_despues','h.reservado','h.responsable','h.motivo'];
            $searchColumns = ['p.nombre_producto','p.codigo_producto','h.tipo','h.responsable','h.motivo'];
            $defaultOrder = 'h.id_movimiento DESC'; $tie = 'h.id_movimiento';
        } elseif (in_array($kind, ['categories','brands'], true)) {
            [$table,$id,$name] = $kind === 'categories' ? ['tbcategorias','id_categoria','nombre_categoria'] : ['tbmarcas','id_marca','nombre_marca'];
            $joins = " FROM $table"; $select = "$id AS id,$name AS name,estado";
            $columns = [$name,'estado',null]; $searchColumns = [$name,'estado'];
            $defaultOrder = "$name ASC"; $tie = $id;
        } else throw new InvalidArgumentException('Tabla desconocida.');

        $where = []; $params = [];
        $base='';$baseParams=[];
        if(in_array($kind,['sales','purchases'],true)){
            $where[]='s.cobro_solicitud IS NOT NULL';if($owner!==null){$where[]='o.id_usuario=?';$params[]=$owner;}$base=' WHERE '.implode(' AND ',$where);$baseParams=$params;
            foreach(['from'=>'>=','to'=>'<'] as $key=>$operator){$value=$input[$key]??'';if($value==='')continue;if(!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$value) || !checkdate((int)substr($value,5,2),(int)substr($value,8,2),(int)substr($value,0,4)))throw new InvalidArgumentException('Fecha inválida.');$date=new DateTimeImmutable($value.' 00:00:00',new DateTimeZone('America/El_Salvador'));if($key==='to')$date=$date->modify('+1 day');$where[]="s.fecha_pago $operator ?";$params[]=$date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');}
            if(!empty($input['from']) && !empty($input['to']) && $input['from']>$input['to'])throw new InvalidArgumentException('El rango de fechas está invertido.');
            if(($input['method']??'')!=='' && ($input['method']??'')!=='efectivo')throw new InvalidArgumentException('Solo se registra efectivo.');
        }
        if($kind==='orders') {
            $where[]='o.retiro_solicitud IS NOT NULL';
            if($owner!==null) {$where[]='o.id_usuario=?';$params[]=$owner;}
            $bucket=$input['bucket']??'';if(!is_string($bucket))throw new InvalidArgumentException('Bandeja inválida.');
            if($bucket!==''){$buckets=['pending'=>"'pendiente'",'confirmed'=>"'confirmado','en_preparacion','listo'",'history'=>"'entregado','cancelado'"];if(!isset($buckets[$bucket]))throw new InvalidArgumentException('Bandeja inválida.');$where[]='o.estado IN ('.$buckets[$bucket].')';}
            $base=' WHERE '.implode(' AND ',$where);$baseParams=$params;
        }
        if ($kind === 'inventory' && ($input['low'] ?? '') !== '') {
            if (($input['low'] ?? '') !== '1') throw new InvalidArgumentException('Filtro de stock inválido.');
            $where[] = 'i.stock_actual-i.stock_reservado<=i.stock_minimo';
        }
        if ($kind === 'movements') {
            foreach (['product'=>'h.id_producto','actor'=>'h.id_usuario'] as $key=>$column) {
                $value = $input[$key] ?? '';
                if ($value === '') continue;
                if (!is_string($value) || !ctype_digit($value) || (float)$value < 1 || (float)$value > 2147483647) throw new InvalidArgumentException('Filtro de historial inválido.');
                $where[] = "$column=?"; $params[] = (int)$value;
            }
            $type = $input['type'] ?? '';
            if ($type !== '') {
                if (!is_string($type) || !in_array($type,['inicial','entrada','salida','ajuste','minimo','reserva','liberacion','entrega','devolucion'],true)) throw new InvalidArgumentException('Tipo inválido.');
                $where[] = 'h.tipo=?'; $params[] = $type;
            }
            foreach (['from'=>'>=','to'=>'<='] as $key=>$operator) {
                $value = $input[$key] ?? '';
                if ($value === '') continue;
                if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$value) || !checkdate((int)substr($value,5,2),(int)substr($value,8,2),(int)substr($value,0,4))) throw new InvalidArgumentException('Fecha inválida.');
                $where[] = "h.created_at $operator ?"; $params[] = $value . ($key==='to'?' 23:59:59':' 00:00:00');
            }
            if (!empty($input['from']) && !empty($input['to']) && $input['from'] > $input['to']) throw new InvalidArgumentException('El rango de fechas está invertido.');
        }
        $search = $input['search'] ?? [];
        if (!is_array($search) || (isset($search['value']) && !is_string($search['value']))) throw new InvalidArgumentException('Búsqueda inválida.');
        $term = trim($search['value'] ?? '');
        if (mb_strlen($term) > 150) throw new InvalidArgumentException('La búsqueda admite hasta 150 caracteres.');
        if (isset($search['regex']) && !in_array($search['regex'], ['false','0'], true)) throw new InvalidArgumentException('No se admiten expresiones regulares.');
        if ($term !== '') {
            $pattern = '%' . str_replace(['!','%','_'], ['!!','!%','!_'], $term) . '%';
            $where[] = '(' . implode(' OR ', array_map(static fn ($column) => "$column LIKE ? ESCAPE '!'", $searchColumns)) . ')';
            foreach ($searchColumns as $_) $params[] = $pattern;
        }
        foreach (['status','category','brand'] as $filter) {
            $value = $input[$filter] ?? '';
            if (!is_string($value)) throw new InvalidArgumentException('Filtro inválido.');
            if ($value === '') continue;
            if ($filter === 'status') {
                if(in_array($kind,['sales','purchases'],true)){if(!in_array($value,['pagada','anulada'],true))throw new InvalidArgumentException('Estado inválido.');$where[]='s.estado=?';$params[]=$value;continue;}
                if($kind==='orders') {
                    if(!in_array($value,['pendiente','confirmado','en_preparacion','listo','entregado','cancelado'],true)) throw new InvalidArgumentException('Estado inválido.');
                    $where[]='o.estado=?';$params[]=$value;continue;
                }
                if (!in_array($value, ['activo','inactivo'], true) || in_array($kind,['inventory','movements'],true)) throw new InvalidArgumentException('Estado inválido.');
                $where[] = ($kind === 'products' ? 'p.estado' : ($kind==='users'?'u.estado':'estado')) . '=?'; $params[] = $value;
            } else {
                if ($kind !== 'products' || !ctype_digit($value) || (float)$value < 1 || (float)$value > 2147483647) throw new InvalidArgumentException('Filtro de catálogo inválido.');
                $where[] = ($filter === 'category' ? 'p.id_categoria' : 'p.id_marca') . '=?'; $params[] = (int)$value;
            }
        }
        $requestedOrder = $input['order'] ?? [];
        if (!is_array($requestedOrder) || count($requestedOrder) > 9) throw new InvalidArgumentException('Orden inválido.');
        $order = [];
        foreach ($requestedOrder as $item) {
            if (!is_array($item) || !isset($item['column'],$item['dir']) || !is_string($item['column']) || !ctype_digit($item['column']) || !is_string($item['dir']) || !in_array($item['dir'], ['asc','desc'], true) || !array_key_exists((int)$item['column'], $columns)) throw new InvalidArgumentException('Orden inválido.');
            $column = $columns[(int)$item['column']];
            if ($column !== null) $order[] = $column . ' ' . strtoupper($item['dir']);
        }
        $ordering = $order ? implode(',', $order) . ',' . $tie . ' ASC' : $defaultOrder;
        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $totalQuery=$this->database->prepare('SELECT COUNT(*)'.$joins.$base);$totalQuery->execute($baseParams);$total=(int)$totalQuery->fetchColumn();
        $count = $this->database->prepare('SELECT COUNT(*)' . $joins . $clause); $count->execute($params);
        $filtered = (int)$count->fetchColumn();
        $query = $this->database->prepare('SELECT ' . $select . $joins . $clause . " ORDER BY $ordering LIMIT $length OFFSET $start");
        $query->execute($params);
        $result=['draw'=>$draw,'recordsTotal'=>$total,'recordsFiltered'=>$filtered,'rows'=>$query->fetchAll()];
        if(in_array($kind,['sales','purchases'],true)){$stats=$this->database->prepare("SELECT COALESCE(SUM(CASE WHEN s.estado='pagada' THEN s.total ELSE 0 END),0) AS paid,COALESCE(SUM(CASE WHEN s.estado='anulada' THEN s.total ELSE 0 END),0) AS voided".$joins.$clause);$stats->execute($params);$result['summary']=$stats->fetch();}
        return $result;
    }
}
