<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/config/config.php';
require_once BASE_PATH.'/app/Models/OrderModel.php';
require_once BASE_PATH.'/core/OrderMail.php';
require_once BASE_PATH.'/app/Models/SaleModel.php';
$data=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
try {
    $db=new PDO('mysql:host='.$data['host'].';port='.$data['port'].';dbname='.$data['database'].';charset=utf8mb4',$data['user'],$data['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    $model=new OrderModel($db);
    if($data['action']==='mail') {
        putenv('ORDER_PUBLIC_URL=http://127.0.0.1:18014');putenv('MAIL_USERNAME=sender@example.test');
        $mail=new OrderMail($db,static function() use($data):void{file_put_contents($data['file'],"sent\n",FILE_APPEND|LOCK_EX);usleep(200000);});
        echo $mail->sendOne($data['id'])==='enviado'?'saved':'busy';exit;
    }
    if($data['action']==='create') $model->create($data['actor'],$data['input']);
    elseif($data['action']==='expire') $model->expireDue();
    elseif($data['target']==='entregado' && OrderModel::cashEnabled($db)){
        $order=$model->find($data['id'],0,true);(new SaleModel($db))->pay($data['actor'],['id_pedido'=>(string)$data['id'],'solicitud'=>hash('sha256','worker-entrega|'.$data['database'].'|'.$data['id']),'recibido'=>$order['total'],'confirmado'=>'1']);
    }else $model->transition($data['actor'],$data['id'],$data['target'],$data['expected'],'Operación concurrente de prueba');
    echo 'saved';
}catch(OrderConflict|DomainException $error){echo 'conflict';}catch(Throwable $error){fwrite(STDERR,$error->getMessage());exit(1);}
