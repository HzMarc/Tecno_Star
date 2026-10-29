<?php
declare(strict_types=1);
require_once __DIR__.'/MigrationRunner.php';

final class OrderUpgrade
{
    public static function apply(PDO $db): void
    {
        $tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if(in_array('schema_migrations',$tables,true)) {(new MigrationRunner($db,__DIR__.'/migrations'))->migrate();return;}
        $lock='tecno_star_migrate_'.substr(hash('sha256',(string)$db->query('SELECT DATABASE()')->fetchColumn()),0,32);
        $query=$db->prepare('SELECT GET_LOCK(?,0)');$query->execute([$lock]);
        if((int)$query->fetchColumn()!==1) throw new RuntimeException('Otra actualización está en curso.');
        try {
            foreach(['tbpedidos','tbinventario','tbproductos','tbcarritos','tbdetallecarritos','tbstockmovimientos'] as $table) {
                $query=$db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$query->execute([$table]);
                if($query->fetchColumn()!=='InnoDB') throw new RuntimeException('Se requieren los planes anteriores y tablas InnoDB.');
            }
            $version='005_pickup_orders.sql';$sql=file_get_contents(__DIR__.'/migrations/'.$version);$hash=hash('sha256',$sql);
            $columns=$db->query('SHOW COLUMNS FROM tbpedidos')->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
            if(!in_array('orders_module_migrations',$tables,true)) {
                if(isset($columns['retiro_solicitud']) || in_array('tbretirodetalles',$tables,true) || in_array('tbretirohistorial',$tables,true) || in_array('tbretiocorreos',$tables,true)) throw new RuntimeException('Esquema parcial; requiere revisión sin sobrescribir datos.');
                if(isset($columns['codigo_pedido']) && $db->query('SELECT codigo_pedido FROM tbpedidos WHERE codigo_pedido IS NOT NULL GROUP BY codigo_pedido HAVING COUNT(*)>1 LIMIT 1')->fetchColumn()!==false) throw new RuntimeException('Hay números de pedido duplicados; revisa antes de actualizar.');
                $db->exec('CREATE TABLE orders_module_migrations(version VARCHAR(150) PRIMARY KEY,checksum CHAR(64) NOT NULL,status VARCHAR(20) NOT NULL,applied_at DATETIME NULL) ENGINE=InnoDB');
            }
            $query=$db->prepare('SELECT checksum,status FROM orders_module_migrations WHERE version=?');$query->execute([$version]);$previous=$query->fetch();
            if($previous) {if($previous['status']!=='applied' || !hash_equals($previous['checksum'],$hash)) throw new RuntimeException('Actualización incompleta o modificada.');return;}
            // El esquema antiguo usa id_cliente y conserva sus estados históricos.
            foreach(['codigo_pedido'=>'VARCHAR(30) NULL','id_cliente'=>'INT UNSIGNED NULL','fecha_vencimiento'=>'DATETIME NULL'] as $name=>$type) if(isset($columns[$name])) $sql=str_replace(' ADD COLUMN '.$name.' '.$type.',', '',$sql);
            $query=$db->prepare("INSERT INTO orders_module_migrations VALUES (?,?,'applying',NULL)");$query->execute([$version,$hash]);
            if(!isset($columns['id_usuario'])) $db->exec('ALTER TABLE tbpedidos ADD COLUMN id_usuario INT UNSIGNED NULL, ADD CONSTRAINT fk_retiro_owner FOREIGN KEY(id_usuario) REFERENCES tbusuarios(id_usuario)');
            $states=$columns['estado']['Type'];
            if(!str_starts_with($states,'enum(')) throw new RuntimeException('Estado de pedido incompatible.');
            foreach(['pendiente','confirmado','en_preparacion','listo','entregado','cancelado'] as $state) if(!str_contains($states,"'".$state."'")) $states=substr($states,0,-1).",'".$state."')";
            $db->exec("ALTER TABLE tbpedidos MODIFY estado $states NOT NULL DEFAULT 'pendiente'");
            foreach(explode(';',$sql) as $statement) if(trim($statement)!=='') $db->exec($statement);
            $query=$db->prepare("UPDATE orders_module_migrations SET status='applied',applied_at=CURRENT_TIMESTAMP WHERE version=?");$query->execute([$version]);
        } finally {$query=$db->prepare('SELECT RELEASE_LOCK(?)');$query->execute([$lock]);}
    }
}
