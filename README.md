# Tecno Star

Aplicación web de accesorios tecnológicos y servicios de papelería, desarrollada con PHP 8.2, MariaDB y una estructura MVC.

Incluye catálogo, carrito, favoritos, cuentas de clientes, acceso con Google, inventario, pedidos con reserva y QR, cobro en efectivo, ventas, reembolsos y solicitudes de servicios con archivos privados y cotizaciones.

## Requisitos

- PHP 8.2 o superior y Apache 2.4 con `mod_rewrite`.
- MariaDB 10.4 o superior. Las restricciones SQL se verifican con MariaDB; MySQL no se ha validado en esta instalación.
- Extensiones PHP: PDO, pdo_mysql, session, filter, json, hash, mbstring, fileinfo, openssl, gd, dom y zlib.
- Composer para instalar las dependencias de `composer.lock`.
- Navegador moderno con JavaScript y cookies. El diseño utiliza Bootstrap e iconos y fuentes desde CDN; DataTables y el lector ZXing se sirven localmente.

No se necesita compilar el frontend. Node.js se usa únicamente para algunas comprobaciones de JavaScript.

## Instalación

Coloca el proyecto en `C:\xampp\htdocs\Tecno_Star`. En una instalación nueva:

```powershell
composer install
Copy-Item .env.example .env
```

No sobrescribas un `.env` existente. Configura las variables privadas:

| Variable | Uso |
| --- | --- |
| `DB_HOST`, `DB_PORT`, `DB_NAME` | Servidor, puerto y base de datos |
| `DB_USER`, `DB_PASSWORD` | Cuenta SQL limitada de la aplicación |
| `APP_URL` | Ruta pública, por ejemplo `/Tecno_Star/public`, o URL completa |
| `APP_STORAGE_PATH` | Directorio privado absoluto; opcional, utiliza `storage/` por defecto |
| `ORDER_PUBLIC_URL` | URL completa accesible por clientes para enlaces de correo y QR |

Las variables del proceso tienen prioridad sobre `.env`. Los secretos no se incluyen en Git. Las herramientas administrativas leen credenciales separadas desde `.env.migrations` (`MIGRATION_DB_USER`, `MIGRATION_DB_PASSWORD`).

Para una **base nueva**, con MariaDB iniciado:

```powershell
C:\xampp\php\php.exe database/migrate.php --database=tecno_star_nueva --create
C:\xampp\php\php.exe database/create-app-user.php --account=tecno_star_app --activate
```

Ajusta primero `DB_NAME` al destino creado y configura la conexión administrativa antes de crear la cuenta limitada. La cuenta debe ser nueva: la herramienta no reemplaza usuarios SQL existentes. Consulta [la guía de base de datos](database/README.md) para crear el primer administrador, cargar ejemplos y gestionar instalaciones antiguas.

Apache debe permitir `.htaccess`. El directorio público es `public/`; `app/`, `config/`, `core/`, `storage/`, `database/`, `tests/` y `docs/` no se publican. No hagas escribible todo el proyecto: PHP necesita escritura en sus sesiones y en `storage/`.

La instalación local documentada utiliza [http://localhost:8012/Tecno_Star/public/](http://localhost:8012/Tecno_Star/public/). Usa el puerto real de tu Apache. Con `APP_URL` relativo, los enlaces conservan el origen del navegador.

## Operación

### Productos y pedidos

El cliente puede buscar productos, guardar favoritos, preparar su carrito y confirmar un pedido para retiro en tienda. El servidor recalcula precios, valida existencias y conserva nombres y precios históricos.

Los pedidos recorren `pendiente → confirmado → en_preparacion → listo → entregado`. Las reservas vencen después de 24 horas. Cancelar o vencer libera la reserva; cobrar y entregar descuenta el inventario una sola vez. El cliente solo puede cancelar antes de la preparación.

**Pedidos** permite atender solicitudes y marcar trabajos listos. **Caja** busca pedidos mediante código, lector USB o cámara y registra efectivo recibido, cambio y entrega en una transacción. Escanear un código no cobra automáticamente. La cámara requiere localhost o HTTPS.

**Ventas** muestra los cobros y comprobantes. **Mis compras** limita los resultados al propietario. El administrador puede registrar reembolsos completos con motivo; solo devuelve existencias cuando confirma recepción física de los productos. La reinversión de productos conserva el reparto de 50 % para reposición y 5 % para crecimiento.

### Papelería y servicios

El apartado [Servicios](http://localhost:8012/Tecno_Star/public/services) ofrece copias, impresiones, fotografías, stickers, escaneos, digitación, anillado y plastificado.

- Las tarjetas muestran categoría, descripción, unidad, tiempo orientativo y precio desde una tarifa configurada. Sin tarifa muestran **Solicitar cotización**. **Limpiar** restablece la búsqueda y la categoría.
- El cliente inicia sesión, indica cantidad e instrucciones y adjunta los archivos necesarios. El requisito de originales físicos se toma del catálogo del servicio; el formulario no muestra un selector de entrega del material.
- Cada formulario incluye **Regresar** para volver a las tarjetas de servicios y un campo de fecha alineado con la cantidad. El catálogo utiliza la navegación del sitio.
- **Mis solicitudes** muestra estado, archivos, cotizaciones, historial y comprobante. Sus filtros de búsqueda y estado también incluyen **Limpiar**, al igual que la bandeja del personal.
- El personal cotiza por conceptos, confirma fecha estimada y recibe los originales cuando sean necesarios. La cotización vence a los 7 días y requiere aceptación del cliente.
- Una nueva versión antes de comenzar el trabajo vuelve a solicitar aceptación. No se permite cambiar el precio durante la preparación.
- Los estados son `nueva → en_cotizacion → cotizada → aceptada → en_proceso → lista → entregada`; también se puede cancelar según permisos. Se puede cotizar directamente una solicitud nueva.
- El trabajo solo comienza con aceptación y, si corresponde, recepción de originales. El cliente puede cancelar antes de comenzar; un trabajo iniciado requiere intervención del administrador.
- El pago completo se registra en efectivo al retirar. Cobro y entrega son atómicos y toleran reintentos. El reembolso de servicios no repone inventario.

Los servicios tienen catálogo y solicitudes propios. Sus cobros aparecen diferenciados en Ventas y Mis compras, forman parte de los totales de ventas y no generan movimientos de stock ni reinversión de productos.

El administrador mantiene servicios, categorías, estados, unidades y tarifas en **Servicios → Catálogo y precios**. Las ocho tarifas iniciales están vacías para evitar publicar precios inventados.

En **Solicitudes**, los accesos por estado muestran cuántos trabajos hay en cada etapa. Una sola búsqueda permite localizar cliente, código o servicio. **Abrir** muestra los detalles y una indicación de **Siguiente paso**; las acciones indican su propósito, como **Preparar cotización**, **Iniciar preparación** o **Cancelar solicitud**. El contenido respeta el menú lateral tanto expandido como contraído, y el menú se pliega en móvil.

#### Archivos privados

Se aceptan PDF, JPG, PNG, WebP, DOCX y XLSX: hasta 5 archivos por envío, 10 MiB cada uno y 30 MiB en total. El contenido se valida en el servidor; no se admiten macros ni PDF con contraseña. Word y Excel se reciben para revisión manual, sin conversión ni vista previa automática. El personal puede adjuntar archivos de resultado.

Los archivos se guardan en `storage/services/files/` y solo se descargan a través de una ruta que verifica propietario o permiso del personal. Se sirven como adjuntos, nunca como páginas ejecutables. Se eliminan 30 días después de cerrar el trabajo; el registro comercial se conserva. Las cargas abandonadas se limpian después de 24 horas.

Apache con `mod_php` tiene límites de 10 MiB por archivo y 32 MiB por formulario en `public/.htaccess`. En PHP-FPM u otro servidor configura también `upload_max_filesize=10M` y `post_max_size=32M`.

La preparación de fotografías y stickers se realiza manualmente por el personal de la tienda.

#### Habilitar servicios en una instalación existente

Con la base local funcionando y las credenciales administrativas privadas configuradas:

```powershell
C:\xampp\php\php.exe database/enable-services.php
C:\xampp\php\php.exe database/enable-services.php --apply
C:\xampp\php\php.exe database/enable-services.php --verify
```

El primer comando simula. `--apply` crea un respaldo completo antes de la migración 010, comprueba la conservación de cuentas, pedidos, ventas e inventario y concede únicamente los permisos SQL necesarios. Conserva el historial/checksum y bloquea reintentos de una migración incompleta. Los empleados no reciben acceso automáticamente.

### Usuarios y permisos

El administrador puede crear usuarios y conceder por separado **Pedidos y caja** y **Servicios** a los empleados. El permiso de servicios permite atender, cotizar, preparar y cobrar esos trabajos; no habilita productos, inventario, gestión de cuentas ni anulaciones. La revocación se comprueba en la siguiente solicitud, incluso con una sesión abierta.

El cliente accede a su perfil, pedidos, compras y solicitudes. Las contraseñas se almacenan como hashes. Los cambios de contraseña y la desactivación invalidan sesiones anteriores. La recuperación utiliza enlaces de un solo uso; no revela si una cuenta existe.

### Correo y acceso con Google

El correo utiliza PHPMailer con Gmail SMTP. Configura `MAIL_ENABLED`, `MAIL_USERNAME`, `MAIL_PASSWORD` de aplicación y `MAIL_FROM_NAME`. No uses la contraseña normal de Gmail. Los mensajes incorporan el logo y enlaces a la aplicación; los documentos de servicios no se adjuntan al correo.

OAuth utiliza `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` y `GOOGLE_REDIRECT_URI`. Para habilitar su tabla en una instalación existente utiliza `database/enable-google.php --apply`. Las cuentas existentes confirman su contraseña para vincularse. Consulta [la guía de Gmail y Google](docs/GUIA_GMAIL_GOOGLE.md).

Configura en el Programador de tareas los siguientes comandos, por ejemplo cada minuto, utilizando el directorio del proyecto:

```powershell
C:\xampp\php\php.exe database/expire-orders.php
C:\xampp\php\php.exe database/send-order-mails.php
C:\xampp\php\php.exe database/process-services.php
```

El último procesa hasta 20 avisos de servicios por ejecución, reintenta fallos de correo y limpia archivos vencidos o abandonados. Los cambios se guardan aunque SMTP esté temporalmente fuera de servicio. Cada operación de servicios intenta enviar su aviso después de confirmar los datos; el trabajador procesa los reintentos y la retención.

En esta instalación local está registrada la tarea de Windows `TecnoStar-Services-F8CE0914`, que ejecuta el trabajador cada minuto mientras el usuario tenga sesión abierta, sin ventanas visibles ni credenciales guardadas. Para otra instalación local ejecuta `database/install-services-task.ps1 -Apply`; usa `-Verify` para comprobarla. El nombre depende de la ruta del proyecto y el instalador no reemplaza tareas de otro proyecto.

## Rutas principales

Las rutas se añaden al prefijo configurado en `APP_URL`.

| Ruta | Función |
| --- | --- |
| `/`, `/store`, `/product?id=…` | Inicio, catálogo y detalle de productos |
| `/services` | Catálogo público de servicios |
| `/services/request?id=…` | Solicitar un servicio con archivos |
| `/services/requests`, `/services/detail?id=…` | Solicitudes propias, cotización y seguimiento |
| `/services/file?id=…` | Descarga autenticada de un archivo privado |
| `/login`, `/register`, `/profile` | Cuenta del usuario |
| `/auth/google` | Acceso con Google |
| `/forgot-password`, `/reset-password` | Recuperación de acceso |
| `/checkout`, `/orders`, `/purchases` | Pedido, seguimiento y comprobantes propios |
| `/admin/products`, `/admin/catalog`, `/admin/inventory` | Catálogo e inventario |
| `/admin/orders`, `/admin/cash`, `/admin/sales` | Atención, cobro y ventas |
| `/admin/services`, `/admin/services/catalog` | Bandeja y catálogo de servicios |
| `/admin/users` | Usuarios y permisos |

Las escrituras utilizan POST y CSRF. La autorización se verifica en el servidor; los identificadores recibidos no conceden acceso por sí mismos.

## Estructura

```text
app/Controllers/     Entradas HTTP y autorización
app/Models/          Reglas de negocio y consultas PDO
app/Views/           Vistas PHP
core/                Sesión, seguridad, correo, importes y archivos privados
config/              Variables de entorno, conexión y URL
routes/web.php       Rutas GET y POST
public/              Entrada pública y recursos estáticos
storage/             Registros, respaldos y archivos privados; fuera de Git
database/           Migraciones y herramientas CLI
tests/              Pruebas automatizadas con datos aislados
```

## Verificación

Usa una base nueva por ejecución. Las pruebas rechazan nombres fuera de su prefijo y no deben dirigirse a la base del negocio. Sustituye el puerto por el de una instancia de MariaDB de pruebas:

```powershell
C:\xampp\php\php.exe tests/catalog.php --database=tecno_star_test_catalog_servicios01 --port=13308 --user=root --orders --accounts --services
C:\xampp\php\php.exe tests/configuration.php
node --check public/assets/js/services.js
node tests/accounts-client.cjs
```

Las pruebas de servicios cubren solicitudes y archivos, aislamiento entre cuentas, cotizaciones y vencimiento, permisos, cobro/reembolso, rollback y concurrencia. La cola se comprueba con un transporte simulado, sin enviar correos reales. La entrega real por SMTP y la operación en la base del negocio requieren una conexión y configuración operativas.


## Estado de verificación · 31 de octubre de 2026

Los servicios están activados en la base local, con respaldo completo, migración 010 y permisos SQL limitados. El catálogo público devuelve ocho servicios. El catálogo nuevo usa `tbserviciocatalogo` para conservar la tabla heredada `tbservicios` y sus referencias sin cambios. No se crearon solicitudes ni ventas de prueba en el negocio.

El inicio real con Google se verificó en Chrome con la cuenta del usuario. Se corrigió un desfase de cuatro segundos entre el reloj local y Google: la comprobación admite hasta 60 segundos de diferencia de emisión, mantiene la verificación criptográfica, emisor, audiencia, nonce y correo, y sigue rechazando tokens vencidos. Un desfase mayor produce un mensaje para sincronizar Windows.

El correo de prueba autorizado fue aceptado por Gmail y localizado en Recibidos del destinatario el 31 de octubre. La cola de servicios, permisos, archivos, reintentos y concurrencia cuentan con pruebas aisladas; no se mandaron mensajes a clientes.

La regresión final del módulo de servicios completó 522 comprobaciones en una instancia de MariaDB aislada, con correo real desactivado. Cubrió catálogo, inventario, carrito, pedidos, ventas, cuentas, compras, avisos de retiro y el recorrido completo de servicios hasta cobro y entrega. Las dos pruebas de actualización conservaron los datos anteriores. Se revisaron escritorio y móvil de 360 px.

Se reparó, con autorización, `phpmyadmin.pma__userconfig`: se restablecieron las preferencias de phpMyAdmin y se conservaron sus archivos dañados en el respaldo físico. Todas las tablas del negocio conservaron su huella. MySQL reinició con su configuración normal y precarga activada; la tabla reparada devuelve `OK`.
