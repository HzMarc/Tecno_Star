<?php
declare(strict_types=1);
require_once __DIR__.'/MigrationRunner.php';

final class ShoppingUpgrade
{
    public static function apply(PDO $db): void
    {
        $tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if(in_array('schema_migrations',$tables,true)) {(new MigrationRunner($db,__DIR__.'/migrations'))->migrate();return;}
        $name=(string)$db->query('SELECT DATABASE()')->fetchColumn();
        $lock='tecno_star_migrate_'.substr(hash('sha256',$name),0,32);$query=$db->prepare('SELECT GET_LOCK(?,0)');$query->execute([$lock]);
        if((int)$query->fetchColumn()!==1) throw new RuntimeException('Otra actualización está en curso.');
        try {
            foreach(['tbusuarios','tbcarritos','tbdetallecarritos','tbproductos'] as $table) {
                $query=$db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?');$query->execute([$name,$table]);
                if($query->fetchColumn()!=='InnoDB') throw new RuntimeException('Se requieren tablas InnoDB compatibles.');
            }
            if((int)$db->query('SELECT COUNT(*) FROM tbdetallecarritos WHERE cantidad<1 OR cantidad<>FLOOR(cantidad) OR cantidad>1000000')->fetchColumn()!==0) throw new RuntimeException('Hay cantidades antiguas no compatibles con unidades enteras. Revísalas antes de actualizar.');
            $query=$db->prepare("SELECT COUNT(*) FROM (SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME='tbcarritos' AND NON_UNIQUE=0 GROUP BY INDEX_NAME HAVING GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) IN ('id_usuario','id_usuario,estado','estado,id_usuario')) unique_cart");$query->execute([$name]);
            if((int)$query->fetchColumn()===0) throw new RuntimeException('Se requiere un índice único de carrito por usuario o usuario/estado.');
            $version='004_shopping_consistency.sql';$file=__DIR__.'/migrations/'.$version;$hash=hash_file('sha256',$file);
            if(!in_array('shopping_module_migrations',$tables,true)) {
                $query=$db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND ((TABLE_NAME='tbcarritos' AND COLUMN_NAME='revision') OR (TABLE_NAME='tbdetallecarritos' AND COLUMN_NAME='precio_visto'))");$query->execute([$name]);
                if(in_array('tbshoppingoperaciones',$tables,true) || (int)$query->fetchColumn()!==0) throw new RuntimeException('Actualización parcial o esquema incompatible. No se sobrescribe.');
                $db->exec('CREATE TABLE shopping_module_migrations(version VARCHAR(150) PRIMARY KEY,checksum CHAR(64) NOT NULL,status VARCHAR(20) NOT NULL,applied_at DATETIME NULL) ENGINE=InnoDB');
            }
            $query=$db->prepare('SELECT checksum,status FROM shopping_module_migrations WHERE version=?');$query->execute([$version]);$record=$query->fetch(PDO::FETCH_ASSOC);
            if($record) {if($record['status']!=='applied' || !hash_equals($record['checksum'],$hash)) throw new RuntimeException('Actualización incompleta o modificada. Requiere revisión.');return;}
            $query=$db->prepare("INSERT INTO shopping_module_migrations VALUES (?,?,'applying',NULL)");$query->execute([$version,$hash]);
            foreach(explode(';',file_get_contents($file)) as $statement) if(trim($statement)!=='') $db->exec($statement);
            $query=$db->prepare("UPDATE shopping_module_migrations SET status='applied',applied_at=CURRENT_TIMESTAMP WHERE version=?");$query->execute([$version]);
        } finally {$query=$db->prepare('SELECT RELEASE_LOCK(?)');$query->execute([$lock]);}
    }
}
