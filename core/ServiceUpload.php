<?php
declare(strict_types=1);

/** Archivos privados: nunca se ejecutan ni se publican por una URL de disco. */
final class ServiceUpload
{
    public const MAX_FILES = 5;
    public const MAX_BYTES = 10485760;
    public const MAX_TOTAL = 31457280;
    public const TYPES = ['pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

    public static function directory(): string
    {
        $root=realpath(APP_STORAGE_PATH);
        $public=realpath(BASE_PATH.'/public');
        if($root===false || ($public!==false && str_starts_with(strtolower(str_replace('\\','/',$root)).'/',strtolower(str_replace('\\','/',$public)).'/')))throw new RuntimeException('El almacenamiento de servicios debe existir fuera de la carpeta pública.');
        return $root . '/services/files';
    }

    public static function inspect(string $path, string $name): array
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $bytes = filesize($path);
        if (!isset(self::TYPES[$ext]) || !$bytes || $bytes > self::MAX_BYTES) {
            throw new InvalidArgumentException('Usa PDF, JPG, PNG, WebP, DOCX o XLSX de hasta 10 MiB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (in_array($ext, ['docx','xlsx'], true)) {
            self::office($path, $ext);
        } elseif ($ext === 'pdf') {
            $data = file_get_contents($path);
            if ($mime !== 'application/pdf' || !str_starts_with($data, '%PDF-') || !str_contains($data, '%%EOF') || preg_match('~/Encrypt\b~', $data)) {
                throw new InvalidArgumentException('El PDF no es válido o está protegido. Exporta una copia sin contraseña.');
            }
        } else {
            $size = @getimagesize($path);
            if (!$size || $mime !== self::TYPES[$ext] || $size['mime'] !== $mime || $size[0] * $size[1] > 25000000) {
                throw new InvalidArgumentException('La imagen no es válida o supera 25 megapíxeles.');
            }
        }
        $safeName = mb_substr(str_replace(["\r","\n",'"','\\','/'], '_', $name), 0, 180);
        return ['nombre'=>$safeName,'mime'=>self::TYPES[$ext],'bytes'=>$bytes,'sha256'=>hash_file('sha256', $path),'ext'=>$ext];
    }

    /** Inspecciona ZIP/OOXML sin extraer archivos ni necesitar ext-zip en XAMPP. */
    private static function office(string $path, string $ext): void
    {
        $data = file_get_contents($path);
        $end = strrpos($data, "PK\x05\x06");
        if ($end === false || strlen($data) - $end < 22) throw new InvalidArgumentException('Documento de Office inválido.');
        $dir = unpack('vdisk/vdiskStart/vonDisk/vcount/Vsize/Voffset/vcomment', substr($data, $end + 4, 18));
        if ($dir['disk'] || $dir['diskStart'] || !$dir['count'] || $dir['count'] > 2000 || $dir['offset'] + $dir['size'] > $end) throw new InvalidArgumentException('Documento de Office no compatible.');
        $offset = $dir['offset']; $expanded = 0; $files = [];
        for ($i=0; $i<$dir['count']; $i++) {
            if (substr($data, $offset, 4) !== "PK\x01\x02" || $offset + 46 > $end) throw new InvalidArgumentException('Documento de Office dañado.');
            $h = unpack('vflags/vmethod', substr($data, $offset+8, 4));
            $s = unpack('Vcrc/Vcompressed/Vexpanded/vname/vextra/vcomment', substr($data, $offset+16, 18));
            $local = unpack('Voffset', substr($data, $offset+42, 4))['offset'];
            $name = substr($data, $offset+46, $s['name']);
            $expanded += $s['expanded'];
            if (($h['flags'] & 1) || !in_array($h['method'], [0,8], true) || $expanded > 104857600 || preg_match('~(^/|(^|/)\.\.(/|$)|\\\\|vbaProject|macroEnabled|\.bin$)~i', $name) || isset($files[$name])) throw new InvalidArgumentException('No se admiten macros, archivos cifrados ni paquetes de Office inseguros.');
            if (substr($data,$local,4)!=="PK\x03\x04" || $local+30>strlen($data)) throw new InvalidArgumentException('Documento de Office dañado.');
            $l=unpack('vname/vextra',substr($data,$local+26,4));
            $start=$local+30+$l['name']+$l['extra'];
            if($start+$s['compressed']>$dir['offset']) throw new InvalidArgumentException('Documento de Office dañado.');
            $files[$name]=[$start,$s['compressed'],$s['expanded'],$h['method'],$s['crc']];
            $offset+=46+$s['name']+$s['extra']+$s['comment'];
        }
        foreach (['[Content_Types].xml', $ext==='docx'?'word/document.xml':'xl/workbook.xml'] as $required) {
            if (!isset($files[$required])) throw new InvalidArgumentException('El contenido no corresponde al formato de Office indicado.');
            [$start,$compressed,$expanded,$method,$crc]=$files[$required];
            if($expanded>20971520) throw new InvalidArgumentException('El documento de Office es demasiado complejo.');
            $xml=substr($data,$start,$compressed);
            if($method===8)$xml=@gzinflate($xml,20971520);
            if(!is_string($xml) || strlen($xml)!==$expanded || sprintf('%u',crc32($xml))!==sprintf('%u',$crc) || preg_match('~<!DOCTYPE|<!ENTITY|macroEnabled~i',$xml)) throw new InvalidArgumentException('XML de Office inválido.');
            $previous=libxml_use_internal_errors(true);$document=new DOMDocument();$valid=$document->loadXML($xml,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($previous);
            if(!$valid)throw new InvalidArgumentException('XML de Office inválido.');
        }
    }

    public static function receive(array $upload): array
    {
        if (!$upload) return [];
        if (!is_array($upload['name']??null) || !is_array($upload['tmp_name']??null) || count($upload['name']) > self::MAX_FILES) throw new InvalidArgumentException('Adjunta como máximo 5 archivos.');
        $result=[];$total=0;
        try {
            foreach($upload['name'] as $i=>$name){
                $error=$upload['error'][$i]??UPLOAD_ERR_NO_FILE;
                if($error===UPLOAD_ERR_NO_FILE)continue;
                if($error!==UPLOAD_ERR_OK || !is_string($name) || !is_string($upload['tmp_name'][$i]??null) || !is_uploaded_file($upload['tmp_name'][$i])) throw new InvalidArgumentException('No se pudo recibir un archivo. Revisa el límite de 10 MiB y vuelve a enviarlo.');
                $file=self::inspect($upload['tmp_name'][$i],$name);$total+=$file['bytes'];
                if($total>self::MAX_TOTAL)throw new InvalidArgumentException('Los adjuntos no pueden superar 30 MiB en total.');
                if(!is_dir(self::directory()) && !mkdir(self::directory(),0700,true) && !is_dir(self::directory()))throw new RuntimeException('No se pudo preparar el almacenamiento privado.');
                $file['ruta']=bin2hex(random_bytes(24)).'.'.$file['ext'];
                if(!move_uploaded_file($upload['tmp_name'][$i],self::path($file['ruta'])))throw new RuntimeException('No se pudo guardar el archivo.');
                $result[]=$file;
            }
            return $result;
        }catch(Throwable $error){self::discard($result);throw $error;}
    }

    public static function path(string $name): string
    {
        if(!preg_match('/^[a-f0-9]{48}\.(pdf|jpe?g|png|webp|docx|xlsx)$/D',$name))throw new RuntimeException('Referencia de archivo inválida.');
        return self::directory().'/'.$name;
    }

    public static function discard(array $files): void
    {
        foreach($files as $file){$path=self::path($file['ruta']);if(is_file($path))unlink($path);}
    }
}
