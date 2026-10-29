<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/database/cli.php';
try {
    $options=migrationOptions();
    if(!preg_match('/^tecno_star_test_catalog_[a-zA-Z0-9_]{1,16}$/D',$options['database'])) throw new RuntimeException('Solo datos sintéticos del catálogo.');
    $db=migrationConnection($options);
    $id=(int)$db->query("SELECT id_producto FROM tbproductos WHERE codigo_producto='CAT-001'")->fetchColumn();
    if(!$id) throw new RuntimeException('Falta el producto sintético.');
    if(in_array('--unavailable',$argv,true)) $db->exec("UPDATE tbproductos SET estado='inactivo' WHERE id_producto=".$id);
    else {$db->exec('UPDATE tbproductos SET precio_venta=30.50 WHERE id_producto='.$id);$db->exec('UPDATE tbinventario SET stock_actual=1,stock_reservado=0 WHERE id_producto='.$id);}
    echo "Escenario sintético actualizado. No se modificó la tienda real.\n";
} catch(Throwable $error) {fwrite(STDERR,$error->getMessage()."\n");exit(1);}
