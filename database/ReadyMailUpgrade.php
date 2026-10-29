<?php
declare(strict_types=1);
require_once __DIR__.'/MigrationRunner.php';
final class ReadyMailUpgrade
{
    public static function verify(PDO $db):void
    {
        $db->query('SELECT tipo FROM tbretiocorreos LIMIT 0');$indexes=[];
        foreach($db->query('SHOW INDEX FROM tbretiocorreos')->fetchAll(PDO::FETCH_ASSOC) as $index)if(!(int)$index['Non_unique'])$indexes[$index['Key_name']][]=$index['Column_name'];
        if(!in_array(['id_pedido','tipo'],$indexes,true) || in_array(['id_pedido'],$indexes,true))throw new RuntimeException('La cola requiere unicidad por pedido y tipo de aviso.');
    }
    public static function apply(PDO $db):void
    {
        $tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if(in_array('schema_migrations',$tables,true)){(new MigrationRunner($db,__DIR__.'/migrations'))->migrate();self::verify($db);return;}
        $q=$db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbretiocorreos'");if($q->fetchColumn()!=='InnoDB')throw new RuntimeException('Se requiere la cola de pedidos existente en InnoDB.');
        $lock='ts_ready_mail_'.substr(hash('sha256',(string)$db->query('SELECT DATABASE()')->fetchColumn()),0,32);$q=$db->prepare('SELECT GET_LOCK(?,0)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1)throw new RuntimeException('Otra actualización está en curso.');
        try{
            $db->exec('CREATE TABLE IF NOT EXISTS ready_mail_module_migrations(version VARCHAR(150) PRIMARY KEY,checksum CHAR(64) NOT NULL,status VARCHAR(20) NOT NULL,applied_at DATETIME NULL) ENGINE=InnoDB');
            $version='008_ready_notifications.sql';$sql=file_get_contents(__DIR__.'/migrations/'.$version);$checksum=hash('sha256',$sql);$q=$db->prepare('SELECT * FROM ready_mail_module_migrations WHERE version=?');$q->execute([$version]);$old=$q->fetch();
            if($old){if($old['status']!=='applied' || !hash_equals($old['checksum'],$checksum))throw new RuntimeException('Actualización de correos incompleta o modificada.');self::verify($db);return;}
            $columns=$db->query('SHOW COLUMNS FROM tbretiocorreos')->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);if(isset($columns['tipo']))throw new RuntimeException('Existe una actualización parcial sin historial; revisa antes de continuar.');
            $q=$db->prepare("INSERT INTO ready_mail_module_migrations VALUES (?,?,'applying',NULL)");$q->execute([$version,$checksum]);$db->exec($sql);self::verify($db);
            $q=$db->prepare("UPDATE ready_mail_module_migrations SET status='applied',applied_at=UTC_TIMESTAMP() WHERE version=?");$q->execute([$version]);
        }finally{$q=$db->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$lock]);}
    }
}
