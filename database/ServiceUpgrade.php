<?php
declare(strict_types=1);

final class ServiceUpgrade
{
    public const PERMISSIONS=['tbserviciocategorias'=>'SELECT, INSERT, UPDATE','tbserviciocatalogo'=>'SELECT, INSERT, UPDATE','tbserviciosolicitudes'=>'SELECT, INSERT, UPDATE','tbserviciocotizaciones'=>'SELECT, INSERT, UPDATE','tbservicioarchivos'=>'SELECT, INSERT, UPDATE (eliminado_at)','tbserviciohistorial'=>'SELECT, INSERT','tbserviciocorreos'=>'SELECT, INSERT, UPDATE'];
    public static function apply(PDO $db):void
    {
        $lock='ts_services_'.substr(hash('sha256',(string)$db->query('SELECT DATABASE()')->fetchColumn()),0,32);$q=$db->prepare('SELECT GET_LOCK(?,0)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1)throw new RuntimeException('Otra actualización de servicios está en curso.');
        try{
            $tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);$table=in_array('schema_migrations',$tables,true)?'schema_migrations':'service_module_migrations';
            if(!in_array($table,$tables,true))$db->exec('CREATE TABLE service_module_migrations(version VARCHAR(150) PRIMARY KEY,checksum CHAR(64) NOT NULL,status VARCHAR(20) NOT NULL,applied_at DATETIME NULL) ENGINE=InnoDB');
            $version='010_services.sql';$sql=file_get_contents(__DIR__.'/migrations/'.$version);$checksum=hash('sha256',$sql);$q=$db->prepare('SELECT * FROM '.$table.' WHERE version=?');$q->execute([$version]);
            if($old=$q->fetch()){if($old['status']!=='applied' || !hash_equals($old['checksum'],$checksum))throw new RuntimeException('Migración de servicios modificada o incompleta. Restaura el respaldo antes de reintentar.');self::verify($db);return;}
            if(in_array('tbserviciocatalogo',$tables,true))throw new RuntimeException('Hay tablas de servicios sin historial; revisa la instalación antes de continuar.');
            $db->query('SELECT pedidos FROM tbpersonalpermisos LIMIT 0');$db->query('SELECT cobro_solicitud FROM tbventas LIMIT 0');
            $q=$db->prepare('INSERT INTO '.$table."(version,checksum,status) VALUES (?,?,'applying')");$q->execute([$version,$checksum]);$db->exec($sql);self::verify($db);$q=$db->prepare('UPDATE '.$table." SET status='applied',applied_at=UTC_TIMESTAMP() WHERE version=?");$q->execute([$version]);
        }finally{$q=$db->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$lock]);}
    }
    public static function verify(PDO $db):void
    {
        foreach(array_keys(self::PERMISSIONS) as $table)$db->query('SELECT * FROM '.$table.' LIMIT 0');$db->query('SELECT servicios FROM tbpersonalpermisos LIMIT 0');$db->query('SELECT id_solicitud_servicio FROM tbventas LIMIT 0');
        $q=$db->query("SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbventas' AND COLUMN_NAME='id_pedido'");if($q->fetchColumn()!=='YES')throw new RuntimeException('El origen de venta no admite servicios.');
    }
}
