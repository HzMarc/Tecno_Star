<?php
declare(strict_types=1);
require_once __DIR__.'/MigrationRunner.php';

/** Actualización específica para el esquema antiguo. No adopta ni ejecuta 001/002. */
final class InventoryUpgrade
{
    public static function apply(PDO $db): void
    {
        $tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if(in_array('schema_migrations',$tables,true)) {
            (new MigrationRunner($db,__DIR__.'/migrations'))->migrate();
            return;
        }
        $name=(string)$db->query('SELECT DATABASE()')->fetchColumn();
        $lock='tecno_star_migrate_'.substr(hash('sha256',$name),0,32);
        $query=$db->prepare('SELECT GET_LOCK(?,0)'); $query->execute([$lock]);
        if((int)$query->fetchColumn()!==1) throw new RuntimeException('Otra actualización está en curso.');
        try {
            $types=$db->prepare('SELECT COLUMN_NAME,COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?');
            foreach(['tbproductos','tbusuarios','tbinventario'] as $table) {
                $engine=$db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?'); $engine->execute([$name,$table]);
                if($engine->fetchColumn()!=='InnoDB') throw new RuntimeException('La actualización requiere tablas InnoDB compatibles.');
                $types->execute([$name,$table]); $columns=$types->fetchAll(PDO::FETCH_KEY_PAIR);
                $key=$table==='tbusuarios'?'id_usuario':'id_producto';
                if(!preg_match('/^int(?:\(\d+\))? unsigned$/D',$columns[$key]??'')) throw new RuntimeException('Identificadores incompatibles con el historial de stock.');
                if($table==='tbinventario') foreach(['stock_actual','stock_reservado','stock_minimo'] as $column) {
                    if(!preg_match('/^decimal\(12,3\)(?: unsigned)?$/D',$columns[$column]??'')) throw new RuntimeException('El esquema antiguo debe tener stock DECIMAL(12,3). No se convierte automáticamente.');
                }
            }
            if((int)$db->query('SELECT COUNT(*) FROM tbinventario WHERE stock_actual<0 OR stock_reservado<0 OR stock_minimo<0 OR stock_reservado>stock_actual')->fetchColumn()!==0) throw new RuntimeException('Hay saldos inconsistentes. Revísalos antes de habilitar el módulo.');
            if((int)$db->query('SELECT COUNT(*) FROM (SELECT id_producto FROM tbinventario GROUP BY id_producto HAVING COUNT(*)>1) duplicate_stock')->fetchColumn()!==0) throw new RuntimeException('El inventario tiene productos duplicados.');
            $unique=$db->prepare("SELECT COUNT(*) FROM (SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME='tbinventario' AND NON_UNIQUE=0 GROUP BY INDEX_NAME HAVING COUNT(*)=1 AND MAX(COLUMN_NAME)='id_producto') inventory_unique"); $unique->execute([$name]);
            if((int)$unique->fetchColumn()===0) throw new RuntimeException('El inventario necesita un índice único por producto.');
            $file=__DIR__.'/migrations/003_inventory_movements.sql'; $checksum=hash_file('sha256',$file);
            if(!in_array('inventory_module_migrations',$tables,true)) {
                if(in_array('tbstockmovimientos',$tables,true)) throw new RuntimeException('El historial nuevo existe sin registro de actualización. Requiere revisión.');
                $db->exec("CREATE TABLE inventory_module_migrations (version VARCHAR(150) PRIMARY KEY,checksum CHAR(64) NOT NULL,status VARCHAR(20) NOT NULL,applied_at DATETIME NULL) ENGINE=InnoDB");
            }
            $version='003_inventory_movements.sql';
            $query=$db->prepare('SELECT checksum,status FROM inventory_module_migrations WHERE version=?'); $query->execute([$version]); $record=$query->fetch(PDO::FETCH_ASSOC);
            if($record) {
                if($record['status']!=='applied' || !hash_equals($record['checksum'],$checksum)) throw new RuntimeException('La actualización de inventario está incompleta o fue modificada. Requiere revisión.');
                $db->query('SELECT id_movimiento FROM tbstockmovimientos LIMIT 0');
                return;
            }
            if(in_array('tbstockmovimientos',$tables,true)) throw new RuntimeException('La tabla de movimientos ya existe. No se sobrescribe.');
            $query=$db->prepare("INSERT INTO inventory_module_migrations(version,checksum,status) VALUES (?,?,'applying')"); $query->execute([$version,$checksum]);
            // El esquema antiguo ya tiene DECIMAL(12,3). Solo crea el historial nuevo y su apertura.
            $sql=file_get_contents($file); $statements=explode(';',$sql);
            foreach(array_slice($statements,1) as $statement) if(trim($statement)!=='') $db->exec($statement);
            $query=$db->prepare("UPDATE inventory_module_migrations SET status='applied',applied_at=CURRENT_TIMESTAMP WHERE version=?"); $query->execute([$version]);
        } finally { $query=$db->prepare('SELECT RELEASE_LOCK(?)'); $query->execute([$lock]); }
    }
}
