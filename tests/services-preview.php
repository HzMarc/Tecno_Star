<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/database/cli.php';require_once BASE_PATH.'/database/RestrictedUser.php';
$process=null;$db=null;$account=null;
try{
    $options=migrationOptions();if(!preg_match('/^tecno_star_test_catalog_[a-zA-Z0-9_]{1,16}$/D',$options['database']))throw new RuntimeException('La vista solo admite una base sintética preparada por tests/catalog.php.');
    $db=migrationConnection($options);$user='ts_preview_'.bin2hex(random_bytes(5));$password=bin2hex(random_bytes(32));RestrictedUser::create($db,$options['database'],$user,'localhost',$password);$account=$db->quote($user)."@'localhost'";
    $storage=sys_get_temp_dir().'/tecno_service_preview_'.bin2hex(random_bytes(8));mkdir($storage,0700);mkdir($storage.'/sessions',0700);
    $env=array_replace(getenv(),['APP_URL'=>'/','DB_HOST'=>$options['host'],'DB_PORT'=>(string)$options['port'],'DB_NAME'=>$options['database'],'DB_USER'=>$user,'DB_PASSWORD'=>$password,'APP_STORAGE_PATH'=>$storage,'MAIL_ENABLED'=>'0','ORDER_PUBLIC_URL'=>'http://127.0.0.1:18014','GOOGLE_CLIENT_ID'=>'','GOOGLE_CLIENT_SECRET'=>'','GOOGLE_REDIRECT_URI'=>'']);
    $process=proc_open([PHP_BINARY,'-d','session.save_path='.$storage.'/sessions','-d','upload_max_filesize=10M','-d','post_max_size=32M','-S','127.0.0.1:18014','-t','public','tests/storefront-router.php'],[0=>['pipe','r'],1=>['file',$storage.'/http.log','a'],2=>['file',$storage.'/http-error.log','a']],$pipes,BASE_PATH,$env);if(!is_resource($process))throw new RuntimeException('No se pudo abrir la vista.');fclose($pipes[0]);
    echo "Vista sintética en http://127.0.0.1:18014/services. Correo real desactivado.\nPulsa Enter para cerrar y retirar la cuenta SQL temporal.\n";fgets(STDIN);
}catch(Throwable $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}finally{if(is_resource($process)){proc_terminate($process);proc_close($process);}if($db && $account)$db->exec('DROP USER '.$account);}
