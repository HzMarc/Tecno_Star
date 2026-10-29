<?php
declare(strict_types=1);
final class Cash
{
    public static function cents(mixed $value):int
    {
        if(!is_string($value) || !preg_match('/^([0-9]{1,10})(?:\.([0-9]{1,2}))?$/D',$value,$parts))throw new InvalidArgumentException('Introduce un importe válido con hasta dos decimales.');
        return (int)$parts[1]*100+(int)str_pad($parts[2]??'',2,'0');
    }
    public static function decimal(int $value):string{return intdiv($value,100).'.'.str_pad((string)($value%100),2,'0',STR_PAD_LEFT);}
}
