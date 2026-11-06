<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';require_once BASE_PATH.'/config/Database.php';require_once BASE_PATH.'/core/ServiceMail.php';require_once BASE_PATH.'/core/Logger.php';
try{
    $db=Database::connection();if(!ServiceModel::installed($db))throw new RuntimeException('Servicios no está instalado.');
    $mail=new ServiceMail($db);$states=[];for($i=0;$i<20;$i++){$state=$mail->sendOne();$states[$state]=($states[$state]??0)+1;if(in_array($state,['none','pending','failed'],true))break;}
    $q=$db->query("SELECT a.id_archivo,a.ruta FROM tbservicioarchivos a JOIN tbserviciosolicitudes s ON s.id_solicitud=a.id_solicitud WHERE a.eliminado_at IS NULL AND s.estado IN ('entregada','cancelada') AND s.updated_at<DATE_SUB(UTC_TIMESTAMP(),INTERVAL 30 DAY) LIMIT 500");$removed=0;
    foreach($q->fetchAll() as $f){$path=ServiceUpload::path($f['ruta']);if(is_file($path) && !unlink($path))continue;$u=$db->prepare('UPDATE tbservicioarchivos SET eliminado_at=UTC_TIMESTAMP() WHERE id_archivo=? AND eliminado_at IS NULL');$u->execute([$f['id_archivo']]);$removed+=$u->rowCount();}
    if(is_dir(ServiceUpload::directory()))foreach(new DirectoryIterator(ServiceUpload::directory()) as $entry){if(!$entry->isFile() || $entry->isLink() || $entry->getMTime()>time()-86400 || !preg_match('/^[a-f0-9]{48}\.(pdf|jpe?g|png|webp|docx|xlsx)$/D',$entry->getFilename()))continue;$q=$db->prepare('SELECT COUNT(*) FROM tbservicioarchivos WHERE ruta=?');$q->execute([$entry->getFilename()]);if(!(int)$q->fetchColumn())unlink(ServiceUpload::path($entry->getFilename()));}
    echo 'Cola: '.json_encode($states,JSON_THROW_ON_ERROR).'; archivos vencidos eliminados: '.$removed."\n";
}catch(Throwable $error){fwrite(STDERR,"No se pudo procesar servicios.\n".($error instanceof PDOException?'SQLSTATE '.$error->getCode():$error->getMessage())."\n");exit(1);}
