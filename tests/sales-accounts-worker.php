<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/config/config.php';require_once BASE_PATH.'/app/Models/SaleModel.php';require_once BASE_PATH.'/app/Models/AccountModel.php';
$data=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);if(!preg_match('/^tecno_star_test_/', $data['database']))exit(1);
try{$db=new PDO('mysql:host='.$data['host'].';port='.$data['port'].';dbname='.$data['database'].';charset=utf8mb4',$data['user'],$data['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);if($data['action']==='reset')(new AccountModel($db))->reset($data['input']);elseif($data['action']==='cancel')(new SaleModel($db))->cancel($data['actor'],$data['input']);else(new SaleModel($db))->pay($data['actor'],$data['input']);echo 'saved';}catch(OrderConflict|DomainException|InvalidArgumentException){echo 'conflict';}catch(Throwable $error){fwrite(STDERR,$error->getMessage());exit(1);}
