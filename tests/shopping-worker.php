<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') exit(1);
require_once dirname(__DIR__).'/config/config.php';
require_once dirname(__DIR__).'/app/Models/ShoppingModel.php';
$settings=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
if(!str_starts_with($settings['database'],'tecno_star_test_catalog_')) exit(1);
$db=new PDO('mysql:host='.$settings['host'].';port='.$settings['port'].';dbname='.$settings['database'].';charset=utf8mb4',$settings['user'],$settings['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
try {(new ShoppingModel($db))->mutate($settings['actor'],$settings['input']);echo 'saved';}
catch(ShoppingConflict) {echo 'conflict';}
catch(Throwable $error) {fwrite(STDERR,get_class($error).': '.$error->getMessage());echo 'failed';exit(2);}
