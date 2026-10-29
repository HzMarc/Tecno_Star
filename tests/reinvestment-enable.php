<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/database/cli.php';require_once BASE_PATH.'/database/MigrationRunner.php';require_once BASE_PATH.'/database/RestrictedUser.php';
$db=null;$account=null;
try{
    $options=migrationOptions();if(!preg_match('/^tecno_star_test_[a-zA-Z0-9_]{1,25}$/D',$options['database']) || (int)$options['port']!==13308)throw new RuntimeException('Solo una base nueva del servidor aislado 13308.');
    $server=migrationConnection(array_replace($options,['database'=>'information_schema']));$q=$server->prepare('SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME=?');$q->execute([$options['database']]);if((int)$q->fetchColumn())throw new RuntimeException('Usa una base nueva.');
    $db=migrationConnection($options+['create'=>true],true);(new MigrationRunner($db,BASE_PATH.'/database/migrations'))->migrate();
    $user='ts_enable_'.bin2hex(random_bytes(5));$password=bin2hex(random_bytes(32));RestrictedUser::create($db,$options['database'],$user,'localhost',$password);$account=$db->quote($user)."@'localhost'";
    $db->exec('REVOKE SELECT,INSERT ON `'.$options['database'].'`.`tbreinversiones` FROM '.$account);
    $environment=array_replace(getenv(),['DB_HOST'=>$options['host'],'DB_PORT'=>(string)$options['port'],'DB_NAME'=>$options['database'],'DB_USER'=>$user,'DB_PASSWORD'=>$password,'MIGRATION_DB_USER'=>$options['user'],'MIGRATION_DB_PASSWORD'=>migrationCredentials()[1],'TECNO_STAR_MIGRATION_PASSWORD'=>migrationCredentials()[1]]);
    foreach(['--apply','--verify'] as $mode){$child=proc_open([PHP_BINARY,BASE_PATH.'/database/enable-reinvestment.php',$mode],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,BASE_PATH,$environment);fclose($pipes[0]);$output=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($child)!==0)throw new RuntimeException('Activación sintética falló: '.$error);}
    echo "OK: activador probado exclusivamente en 13308: copia previa, adopción del esquema y permisos SELECT/INSERT verificados.\n";
}catch(Throwable $error){fwrite(STDERR,$error->getMessage()."\n");exit(1);}finally{if($account && $db)$db->exec('DROP USER '.$account);}
