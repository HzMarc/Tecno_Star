<?php

declare(strict_types=1);

final class CatalogImage
{
    public static function store(?array $file): ?string
    {
        if ($file === null || ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE) return null;
        if (!isset($file['error'], $file['tmp_name']) || !is_int($file['error']) || !is_string($file['tmp_name'])) throw new CatalogValidationException(['image' => 'La carga no es válida.']);
        if ($file['error'] !== UPLOAD_ERR_OK) throw new CatalogValidationException(['image' => 'No se pudo subir la imagen. Máximo 4 MiB; revisa también el límite del servidor.']);
        if (!is_uploaded_file($file['tmp_name'])) throw new CatalogValidationException(['image' => 'La imagen debe provenir del formulario.']);
        $size = filesize($file['tmp_name']);
        if ($size === false || $size < 1 || $size > 4 * 1024 * 1024) throw new CatalogValidationException(['image' => 'La imagen debe ocupar como máximo 4 MiB.']);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $dimensions = @getimagesize($file['tmp_name']);
        if (!isset($extensions[$mime]) || !$dimensions || ($dimensions['mime'] ?? '') !== $mime || $dimensions[0] > 6000 || $dimensions[1] > 6000 || $dimensions[0] * $dimensions[1] > 20000000) throw new CatalogValidationException(['image' => 'Usa JPG, PNG o WebP reales, de hasta 6000 px por lado y 20 megapíxeles.']);
        $directory = BASE_PATH . '/public/uploads/products';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new RuntimeException('No se pudo preparar el almacenamiento de imágenes.');
        $name = bin2hex(random_bytes(24)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) throw new RuntimeException('No se pudo guardar la imagen.');
        return '/uploads/products/' . $name;
    }

    public static function discard(?string $path): void
    {
        if ($path !== null && preg_match('~^/uploads/products/[a-f0-9]{48}\.(jpg|png|webp)$~D', $path)) {
            $file = BASE_PATH . '/public' . $path;
            if (is_file($file)) unlink($file);
        }
    }
}
