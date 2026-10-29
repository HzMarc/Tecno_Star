<?php
declare(strict_types=1);
require_once __DIR__.'/MigrationRunner.php';
final class ReinvestmentUpgrade
{
    public static function validate(PDO $db):void
    {
        $q=$db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbreinversiones'");
        if($q->fetchColumn()!=='InnoDB')throw new RuntimeException('La reinversión requiere InnoDB.');
        $columns=$db->query('SHOW COLUMNS FROM tbreinversiones')->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
        foreach(['id_reinversion','id_venta','monto_venta','porcentaje_reposicion','porcentaje_crecimiento','monto_reposicion','monto_crecimiento','total_reinversion','created_at'] as $name)if(!isset($columns[$name]))throw new RuntimeException('Tabla de reinversión incompatible: '.$name);
        foreach(['monto_reposicion','monto_crecimiento','total_reinversion'] as $name)if(!str_contains($columns[$name]['Extra'],'STORED GENERATED'))throw new RuntimeException('Los importes deben conservar el cálculo histórico generado.');
        $unique=false;$indexes=[];foreach($db->query('SHOW INDEX FROM tbreinversiones')->fetchAll() as $index)if(!(int)$index['Non_unique'])$indexes[$index['Key_name']][]=$index['Column_name'];foreach($indexes as $index)if($index===['id_venta'])$unique=true;
        if(!$unique)throw new RuntimeException('Se requiere una reinversión única por venta.');
        $q=$db->query("SELECT COLUMN_NAME,GENERATION_EXPRESSION FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbreinversiones' AND COLUMN_NAME IN ('monto_reposicion','monto_crecimiento','total_reinversion')");$expressions=$q->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach(['0.00'=>['0.00','0.00','0.00'],'0.01'=>['0.01','0.00','0.01'],'0.10'=>['0.05','0.01','0.06'],'24.99'=>['12.50','1.24','13.74'],'100.00'=>['50.00','5.00','55.00']] as $base=>$expected){$query=$db->prepare('SELECT ('.$expressions['monto_reposicion'].'),('.$expressions['monto_crecimiento'].'),('.$expressions['total_reinversion'].') FROM (SELECT CAST(? AS DECIMAL(12,2)) AS monto_venta,50 AS porcentaje_reposicion,5 AS porcentaje_crecimiento) AS fixture');$query->execute([(string)$base]);if(array_map(static fn($n)=>number_format((float)$n,2,'.',''),$query->fetch(PDO::FETCH_NUM))!==$expected)throw new RuntimeException('La fórmula instalada no conserva el redondeo del plan.');}
        $q=$db->query('SELECT COUNT(*) FROM tbreinversiones WHERE monto_venta < 0 OR porcentaje_reposicion < 0 OR porcentaje_crecimiento < 0 OR porcentaje_reposicion+porcentaje_crecimiento > 100 OR monto_reposicion <> ROUND(monto_venta*porcentaje_reposicion/100,2) OR total_reinversion <> ROUND(monto_venta*(porcentaje_reposicion+porcentaje_crecimiento)/100,2) OR monto_crecimiento <> total_reinversion-monto_reposicion');
        if((int)$q->fetchColumn())throw new RuntimeException('Hay cálculos históricos incompatibles que requieren revisión. No se han corregido automáticamente.');
    }
    public static function apply(PDO $db):void
    {
        $tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if(in_array('tbreinversiones',$tables,true))self::validate($db);
        if(in_array('schema_migrations',$tables,true)){(new MigrationRunner($db,__DIR__.'/migrations'))->migrate();self::validate($db);return;}
        $db->query('SELECT cobro_solicitud FROM tbventas LIMIT 0');
        $lock='ts_reinvestment_'.substr(hash('sha256',(string)$db->query('SELECT DATABASE()')->fetchColumn()),0,32);$q=$db->prepare('SELECT GET_LOCK(?,0)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1)throw new RuntimeException('Otra actualización está en curso.');
        try{
            $db->exec('CREATE TABLE IF NOT EXISTS reinvestment_module_migrations(version VARCHAR(150) PRIMARY KEY,checksum CHAR(64) NOT NULL,status VARCHAR(20) NOT NULL,applied_at DATETIME NULL) ENGINE=InnoDB');
            $version='007_reinvestment.sql';$sql=file_get_contents(__DIR__.'/migrations/'.$version);$checksum=hash('sha256',$sql);$q=$db->prepare('SELECT * FROM reinvestment_module_migrations WHERE version=?');$q->execute([$version]);$old=$q->fetch();
            if($old){if($old['status']!=='applied' || !hash_equals($old['checksum'],$checksum))throw new RuntimeException('Migración incompleta o modificada.');self::validate($db);return;}
            $db->exec($sql);self::validate($db);$q=$db->prepare("INSERT INTO reinvestment_module_migrations VALUES (?,?,'applied',UTC_TIMESTAMP())");$q->execute([$version,$checksum]);
        }finally{$q=$db->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$lock]);}
    }
}
