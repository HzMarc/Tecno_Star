<?php
declare(strict_types=1);
require_once __DIR__.'/OrderModel.php';

final class InventoryModel
{
    public function __construct(private PDO $database) {}

    private static function integer(mixed $value, int $min = 0): int
    {
        if (!is_string($value) || !ctype_digit($value) || (float)$value < $min || (float)$value > 2147483647) throw new InvalidArgumentException('Introduce una cantidad entera válida (máximo 2147483647).');
        return (int)$value;
    }

    /** Aritmética en milésimas enteras: sin errores de coma flotante. */
    private static function quantity(mixed $value): int
    {
        if (!is_string($value) || !preg_match('/^([0-9]{1,9})(?:\.([0-9]{1,3}))?$/D',$value,$parts)) throw new InvalidArgumentException('Introduce una cantidad válida con hasta 3 decimales (máximo 999999999.999).');
        return (int)$parts[1]*1000 + (int)str_pad($parts[2] ?? '',3,'0');
    }

    private static function decimal(int $value): string
    {
        return ($value<0?'-':'').intdiv(abs($value),1000).'.'.str_pad((string)(abs($value)%1000),3,'0',STR_PAD_LEFT);
    }

    public function move(int $actor, array $input): array
    {
        $id = self::integer($input['id_producto'] ?? null, 1);
        $type = $input['tipo'] ?? '';
        if (!is_string($type) || !in_array($type, ['entrada','salida','ajuste','minimo'], true)) throw new InvalidArgumentException('Selecciona un movimiento válido.');
        $quantity = self::quantity($input['cantidad'] ?? null);
        if ($quantity===0 && in_array($type,['entrada','salida'],true)) throw new InvalidArgumentException('La cantidad debe ser mayor que cero.');
        $expected = $type === 'ajuste' ? self::quantity($input['stock_esperado'] ?? null) : null;
        $reason = $input['motivo'] ?? '';
        if (!is_string($reason) || mb_strlen(trim($reason)) < 3 || mb_strlen(trim($reason)) > 500) throw new InvalidArgumentException('Explica el motivo con entre 3 y 500 caracteres.');
        $reason = trim($reason);
        $key = $input['solicitud'] ?? '';
        if (!is_string($key) || !preg_match('/^[a-f0-9]{64}$/D', $key)) throw new InvalidArgumentException('La solicitud no es válida. Abre de nuevo el formulario.');
        $hash = hash('sha256', json_encode([$actor,$id,$type,$quantity,$reason,$expected], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        (new OrderModel($this->database))->expireDue();
        $this->database->beginTransaction();
        try {
            // Bloqueo compartido: una baja o cambio de rol debe esperar al movimiento autorizado.
            $user = $this->database->prepare("SELECT u.nombres,u.apellidos FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol WHERE u.id_usuario=? AND u.estado='activo' AND r.nombre_rol='Administrador' LOCK IN SHARE MODE");
            $user->execute([$actor]); $responsible = $user->fetch();
            if (!$responsible) throw new InvalidArgumentException('No tienes permisos para modificar el inventario.');
            $productLock=$this->database->prepare('SELECT id_producto FROM tbproductos WHERE id_producto=? FOR UPDATE');$productLock->execute([$id]);
            $lock = $this->database->prepare('SELECT * FROM tbinventario WHERE id_producto=? FOR UPDATE');
            $lock->execute([$id]); $stock = $lock->fetch();
            if (!$stock) throw new InvalidArgumentException('No se encontró el inventario del producto.');
            $previous = $this->database->prepare('SELECT id_movimiento,huella FROM tbstockmovimientos WHERE solicitud=? FOR UPDATE');
            $previous->execute([$key]); $existing = $previous->fetch();
            if ($existing) {
                if (!hash_equals($existing['huella'], $hash)) throw new InvalidArgumentException('Esta solicitud ya se utilizó con otros datos. Abre de nuevo el formulario.');
                $this->database->commit();
                return ['id'=>(int)$existing['id_movimiento'],'repeated'=>true];
            }
            $before = self::quantity((string)$stock['stock_actual']); $reserved = self::quantity((string)$stock['stock_reservado']); $minimum = self::quantity((string)$stock['stock_minimo']);
            if ($expected !== null && $before !== $expected) throw new InvalidArgumentException('Las existencias cambiaron mientras realizabas el conteo. Cierra el formulario, actualiza la tabla y revisa el conteo.');
            $after = match ($type) { 'entrada'=>$before+$quantity, 'salida'=>$before-$quantity, 'ajuste'=>$quantity, default=>$before };
            $newMinimum = $type === 'minimo' ? $quantity : $minimum;
            if ($after < 0 || $after > 999999999999) throw new InvalidArgumentException('El movimiento supera los límites de existencias.');
            if ($after < $reserved) throw new InvalidArgumentException('El stock físico no puede quedar por debajo de las unidades reservadas ('.self::decimal($reserved).').');
            if ($after === $before && $minimum === $newMinimum) throw new InvalidArgumentException('No hay cambios que registrar.');
            $update = $this->database->prepare('UPDATE tbinventario SET stock_actual=?,stock_minimo=? WHERE id_producto=?');
            $update->execute([self::decimal($after),self::decimal($newMinimum),$id]);
            $insert = $this->database->prepare('INSERT INTO tbstockmovimientos (id_producto,id_usuario,responsable,tipo,cantidad,stock_antes,stock_despues,reservado,minimo_antes,minimo_despues,motivo,solicitud,huella) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $insert->execute([$id,$actor,$responsible['nombres'].' '.$responsible['apellidos'],$type,self::decimal($after-$before),self::decimal($before),self::decimal($after),self::decimal($reserved),self::decimal($minimum),self::decimal($newMinimum),$reason,$key,$hash]);
            $movement = (int)$this->database->lastInsertId();
            $this->database->commit();
            return ['id'=>$movement,'repeated'=>false];
        } catch (Throwable $error) {
            if ($this->database->inTransaction()) $this->database->rollBack();
            throw $error;
        }
    }
}
