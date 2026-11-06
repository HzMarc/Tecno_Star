# Recuperación y activación · 31 de octubre de 2026

Se completó la activación de servicios en la base local. La migración 010 usa `tbserviciocatalogo` y conserva `tbservicios` y `tbdetalleventaservicio` del esquema heredado. El catálogo tiene ocho servicios y se verificó con la cuenta SQL limitada y por HTTP en Apache.

## Cierre inesperado de MariaDB

El arranque normal precargaba una página dañada del espacio de `phpmyadmin.pma__userconfig`. InnoDB registraba errores de identidad de página y terminaba el servidor después de cien intentos de lectura. Se arrancó temporalmente sin precarga para recuperar disponibilidad; no se activó `innodb_force_recovery` ni se redujeron las comprobaciones de seguridad.

Antes de intervenir se obtuvo una copia física con MySQL detenido en `storage/backups/mysql-services-20261031-111551/`. También se crearon respaldos SQL completos del negocio antes de migrar y antes de reparar las preferencias.

La reparación se ensayó en una copia independiente en el puerto 13308. El cambio de nombre no era posible por la corrupción del espacio. Recrear exclusivamente la tabla de preferencias permitió un arranque normal y conservó la huella de todas las tablas comerciales de la copia.

El usuario autorizó restablecer esas preferencias. Se recreó únicamente `phpmyadmin.pma__userconfig` con la definición de phpMyAdmin, se comprobó `CHECK TABLE` y se compararon las huellas de todas las tablas de `tecno_star` antes y después: iguales. Se apagó MariaDB de forma normal, se esperó la terminación del proceso y se reinició con `my.ini` sin opciones especiales. La precarga está habilitada y el registro confirma servidor listo y carga completada sin errores.

Las preferencias de phpMyAdmin se restablecieron; no se recuperó su contenido ilegible. Los archivos originales permanecen respaldados. No se alteraron cuentas, pedidos, ventas, inventario ni servicios comerciales para esta reparación.

## Login y correo

Google fallaba con `BeforeValidException` porque el reloj local estaba unos cuatro segundos atrasado. La verificación admite un máximo de 60 segundos de diferencia de emisión, conserva firma, audiencia, emisor, nonce y correo verificado y rechaza tokens vencidos. Se verificó el acceso real en Chrome y se añadieron pruebas con JWT firmados.

El único correo real de prueba fue autorizado por el usuario, enviado a su cuenta Gmail remitente y localizado en Recibidos. No se enviaron correos de prueba a clientes. La constancia privada está en `storage/services/mail-verification.json`.

Los avisos de servicios se intentan enviar tras guardar cada operación. La tarea `TecnoStar-Services-F8CE0914` procesa reintentos y retención cada minuto con el usuario conectado, permisos limitados, ventana oculta y sin contraseñas almacenadas. Su instalador/verificador está en `database/install-services-task.ps1`.
