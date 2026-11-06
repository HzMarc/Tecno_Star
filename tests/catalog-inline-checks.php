<?php
declare(strict_types=1);

$inlineHeaders=['Accept: application/json'];
foreach(['categories'=>'Categoría desde producto','brands'=>'Marca desde producto'] as $inlineKind=>$inlineName){
    $inlinePost=['kind'=>$inlineKind,'name'=>$inlineName,'estado'=>'activo','_csrf'=>$token];
    [$status,$json]=catalogRequest('/admin/catalog/save',$cookie,$inlinePost,null,$inlineHeaders);
    $result=json_decode($json,true,512,JSON_THROW_ON_ERROR);
    catalogCheck($status===201 && $result['ok'] && is_int($result['option']['id']) && $result['option']['id']>0 && $result['option']['name']===$inlineName,'creación rápida de '.$inlineKind.' devuelve la opción guardada sin redirigir');
    $options=$model->options($inlineKind);
    catalogCheck(count(array_filter($options,static fn($option)=>$option['name']===$inlineName))===1,'nueva opción disponible para seleccionar en el producto');
    [$status,$json]=catalogRequest('/admin/catalog/save',$cookie,$inlinePost,null,$inlineHeaders);
    catalogCheck($status===422 && isset(json_decode($json,true,512,JSON_THROW_ON_ERROR)['errors']['name']),'duplicado devuelve error de validación JSON sin crear otra opción');
}
[$status,$json]=catalogRequest('/admin/catalog/save',$cookie,['kind'=>'brands','name'=>'','estado'=>'activo','_csrf'=>$token],null,$inlineHeaders);
catalogCheck($status===422 && isset(json_decode($json,true,512,JSON_THROW_ON_ERROR)['errors']['name']),'nombre vacío se rechaza en creación rápida');
[$status]=catalogRequest('/admin/catalog/save',$cookie,['kind'=>'brands','name'=>'Sin token','estado'=>'activo'],null,$inlineHeaders);
catalogCheck($status===403,'creación rápida exige CSRF');
[$status]=catalogRequest('/admin/catalog/save',$client,['kind'=>'brands','name'=>'Sin permiso','estado'=>'activo','_csrf'=>$clientToken],null,$inlineHeaders);
catalogCheck($status===403,'cliente no puede crear opciones aunque solicite JSON');
