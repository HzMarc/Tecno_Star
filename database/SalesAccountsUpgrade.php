<?php
declare(strict_types=1);
require_once __DIR__.'/MigrationRunner.php';
final class SalesAccountsUpgrade
{
    public static function apply(PDO $db):void
    {
        $tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if(in_array('schema_migrations',$tables,true)){(new MigrationRunner($db,__DIR__.'/migrations'))->migrate();return;}
        $lock='tecno_star_migrate_'.substr(hash('sha256',(string)$db->query('SELECT DATABASE()')->fetchColumn()),0,32);$q=$db->prepare('SELECT GET_LOCK(?,0)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1)throw new RuntimeException('Otra actualización está en curso.');
        try{
            foreach(['tbventas','tbusuarios','tbclientes','tbpedidos','tbstockmovimientos','tbretirodetalles'] as $table){$q=$db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$table]);if($q->fetchColumn()!=='InnoDB')throw new RuntimeException('Se requieren los planes anteriores y tablas InnoDB.');}
            $version='006_sales_accounts.sql';$sql=file_get_contents(__DIR__.'/migrations/'.$version);$checksum=hash('sha256',$sql);
            $users=$db->query('SHOW COLUMNS FROM tbusuarios')->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);$columns=$db->query('SHOW COLUMNS FROM tbventas')->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
            if(!in_array('sales_accounts_module_migrations',$tables,true)){
                if(isset($users['session_version']) || isset($users['cuenta_revision']) || isset($columns['cobro_solicitud']) || array_intersect(['tbpersonalpermisos','tbventasanulaciones','tbcuentashistorial','tbclaverecuperacion'],$tables))throw new RuntimeException('Actualización parcial; no se sobrescribe.');
                $db->exec('CREATE TABLE sales_accounts_module_migrations(version VARCHAR(150) PRIMARY KEY,checksum CHAR(64) NOT NULL,status VARCHAR(20) NOT NULL,applied_at DATETIME NULL) ENGINE=InnoDB');
            }
            $q=$db->prepare('SELECT checksum,status FROM sales_accounts_module_migrations WHERE version=?');$q->execute([$version]);$old=$q->fetch();if($old){if($old['status']!=='applied' || !hash_equals($old['checksum'],$checksum))throw new RuntimeException('Actualización incompleta o modificada.');return;}
            foreach(['codigo_venta'=>'VARCHAR(30) NULL','id_usuario'=>'INT UNSIGNED NULL','id_cliente'=>'INT UNSIGNED NULL','fecha_pago'=>'DATETIME NULL','metodo_pago'=>'VARCHAR(30) NULL'] as $column=>$definition)if(isset($columns[$column]))$sql=str_replace(' ADD COLUMN '.$column.' '.$definition.',','',$sql);
            $state=$columns['estado']['Type'];if(!str_starts_with($state,'enum('))throw new RuntimeException('Estado de venta incompatible.');if(!str_contains($state,"'anulada'"))$state=substr($state,0,-1).",'anulada')";
            $q=$db->prepare("INSERT INTO sales_accounts_module_migrations VALUES (?,?,'applying',NULL)");$q->execute([$version,$checksum]);
            $db->exec("ALTER TABLE tbventas MODIFY estado $state NOT NULL DEFAULT ".$db->quote((string)$columns['estado']['Default']));
            foreach(explode(';',$sql) as $statement)if(trim($statement)!=='')$db->exec($statement);
            $q=$db->prepare("UPDATE sales_accounts_module_migrations SET status='applied',applied_at=CURRENT_TIMESTAMP WHERE version=?");$q->execute([$version]);
        }finally{$q=$db->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$lock]);}
    }
}
