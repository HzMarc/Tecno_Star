# Tecno Star

Aplicación web de catálogo de accesorios tecnológicos y servicios de papelería, desarrollada con PHP, MySQL y una estructura MVC sencilla.

Actualmente incluye registro e inicio de sesión, perfil editable, catálogo, detalle de productos, carrito, favoritos, administración del catálogo e inventario, pedidos con reservas y QR por correo, cobro en efectivo con entrega, ventas, anulaciones, usuarios y recuperación de contraseña. El trabajo previsto está en [PLAN_DE_MEJORAS.md](PLAN_DE_MEJORAS.md).

## Equipo

Scrum Master: [fredr00](https://github.com/fredr00). Frontend 1: [Lisseth-Montoya](https://github.com/Lisseth-Montoya). Frontend 2: [carlosmejia3-spec](https://github.com/carlosmejia3-spec). Backend 1: [elbinrivas](https://github.com/elbinrivas). Backend 2: [HzMarc](https://github.com/HzMarc).

Consulta la [distribución de responsabilidades y créditos](docs/EQUIPO_Y_ROLES.md).

## Requisitos

- PHP 8.2 o superior. Verificado con PHP 8.2.12 de XAMPP.
- Extensiones PHP `PDO`, `pdo_mysql`, `session`, `filter`, `json`, `hash`, `mbstring`, `fileinfo`, `openssl` y `gd` (QR en PNG).
- Apache 2.4 con `mod_rewrite` y permisos para aplicar los archivos `.htaccess` (`AllowOverride All` en el directorio del proyecto).
- MariaDB 10.4 o superior para las migraciones nuevas (probadas con 10.4.32). MySQL requiere al menos 8.0.16 para aplicar las restricciones `CHECK`, pero no se ha probado con ese motor.
- Navegador moderno con JavaScript, cookies y almacenamiento local habilitados.
- Acceso a Internet para cargar Bootstrap 5.3.3, Bootstrap Icons 1.11.3, Google Fonts y ApexCharts desde los servicios externos usados en las vistas. El mapa también depende de Google Maps.

Ejecuta `composer install` para instalar PHPMailer y Endroid QR Code con las versiones de `composer.lock`. En producción usa `composer install --no-dev`. No hay compilación del frontend. ZXing 0.1.5 y su licencia se sirven localmente para la cámara. Node.js es opcional para las pruebas de JavaScript.

## Instalación local en Windows con XAMPP

### 1. Colocar el proyecto

Instala XAMPP con Apache y PHP. Copia o clona este proyecto en:

```text
C:\xampp\htdocs\Tecno_Star
```

Esta guía utiliza la carpeta `Tecno_Star` y Apache en el puerto HTTP 80. Puedes utilizar otra ubicación ajustando `APP_URL` en la configuración local; si usas otro puerto, inclúyelo en las direcciones de acceso.

Antes de ejecutar la aplicación o las herramientas de base de datos, crea la configuración local:

```powershell
Copy-Item .env.example .env
```

Ejecuta esa copia solo durante la instalación inicial, sin sobrescribir un `.env` ya configurado.

### 2. Comprobar PHP

Abre PowerShell en la raíz del proyecto:

```powershell
Set-Location C:\xampp\htdocs\Tecno_Star
& C:\xampp\php\php.exe -v
& C:\xampp\php\php.exe -m
```

Confirma que aparecen las extensiones indicadas en los requisitos. Estos comandos revisan el PHP de XAMPP; si ejecutas solamente `php`, el PATH podría seleccionar una instalación diferente.

### 3. Preparar la base de datos

Para una instalación nueva, ejecuta las migraciones después de configurar la conexión (paso 4) e iniciar el servicio de base de datos (paso 5):

```powershell
& C:\xampp\php\php.exe database/migrate.php --database=tecno_star_nueva --create
```

Se crea una base nueva con el esquema y los roles necesarios. Ajusta `DB_NAME` a `tecno_star_nueva` al terminar. Para cargar dos productos de prueba, opcionalmente ejecuta:

```powershell
& C:\xampp\php\php.exe database/seed.php --database=tecno_star_nueva --demo
```

Si ya tienes una base con datos, consérvala: el ejecutor rechaza bases con tablas sin historial de migraciones y no convierte automáticamente instalaciones antiguas. Consulta [database/README.md](database/README.md) para los requisitos, opciones, primera cuenta administrativa, pruebas y manejo de fallos.

Las consultas actuales hacen referencia a estas tablas; esta lista no sustituye el esquema con sus columnas, índices y relaciones:

| Área | Tablas referenciadas |
| --- | --- |
| Cuentas | `tbroles`, `tbusuarios`, `tbclientes` |
| Catálogo | `tbproductos`, `tbcategorias`, `tbmarcas`, `tbinventario` |
| Carrito | `tbcarritos`, `tbdetallecarritos`, `tbshoppingoperaciones` |
| Favoritos | `tblistasdeseos`, `tbdetallelistasdeseos` |
| Métricas administrativas | `tbpedidos`, `tbventas` |

Las migraciones crean los roles con nombres exactos `Cliente` y `Administrador`. El registro genera cuentas Cliente. No se crean cuentas con credenciales predeterminadas: para habilitar tu primera cuenta administrativa sigue [las instrucciones de instalación](database/README.md). Las contraseñas se almacenan como hashes compatibles con `password_hash`.

### 4. Configurar la conexión y la URL base

Edita el archivo local `.env` para que coincida con tu servicio y dirección de acceso. No edites el código PHP para cambiar credenciales. El archivo [.env.example](.env.example) contiene únicamente valores de ejemplo:

| Variable | Ejemplo local | Uso |
| --- | --- | --- |
| `DB_HOST` | `127.0.0.1` | Servidor de base de datos |
| `DB_PORT` | `3308` | Puerto del servicio; no se asume el puerto predeterminado 3306 |
| `DB_NAME` | `tecno_star` | Base de datos de la aplicación; ajustar si instalaste otra |
| `DB_USER` | `tecno_star_app` | Cuenta limitada de ejecución, después de crearla |
| `DB_PASSWORD` | Valor privado generado | Clave local de la cuenta limitada; no versionar |
| `APP_NAME` | `Tecno Star` | Nombre de la aplicación |
| `APP_URL` | `/Tecno_Star/public` | Ruta de montaje o URL completa de la entrada pública |

Las variables del proceso o del servidor tienen prioridad sobre `.env`, incluso cuando contienen una cadena vacía. PHP carga el archivo local sin ejecutar ni interpolar sus valores. Las variables de conexión y `APP_URL` son obligatorias; una configuración ausente o inválida produce un error. No se utiliza el encabezado `Host` del visitante para construir enlaces.

Usa comillas para contraseñas con espacios o `#`, por ejemplo `DB_PASSWORD="valor de ejemplo # literal"`. Los valores entre comillas conservan literalmente los caracteres y las barras invertidas; no hay interpolación, escapes ni valores de varias líneas. Los comentarios deben ir en su propia línea o después de un valor sin comillas, separados por un espacio. Las claves duplicadas y las comillas sin cerrar se rechazan sin mostrar el valor.

`.env` y sus variantes están ignorados por Git, y Apache bloquea archivos y directorios ocultos. Solo `.env.example` debe versionarse. `BASE_PATH` sigue calculándose automáticamente en PHP.

Después de instalar las tablas, crea la cuenta de ejecución limitada con una conexión administrativa local:

```powershell
& C:\xampp\php\php.exe database/create-app-user.php --account=tecno_star_app --activate
```

El comando exige una cuenta nueva, genera una clave aleatoria y actualiza únicamente `DB_USER` y `DB_PASSWORD` en `.env`. Verifica la conexión antes de activarla y conserva las credenciales administrativas en `.env.migrations`, destinado a las herramientas CLI. No cambia claves ni permisos de cuentas existentes. La instalación local ya quedó activada con `tecno_star_app`; no repitas el comando con ese nombre.

Los permisos se limitan a las tablas y operaciones que utiliza el código actual. No permiten crear o eliminar tablas, gestionar cuentas SQL ni cambiar roles de clientes. Inventario permite modificar físico, mínimo y reservado; la reserva solo se gestiona desde pedidos en la aplicación. Detalle e historial de pedidos y movimientos solo admiten lectura e inserción, sin modificar ni borrar auditoría. Consulta [database/README.md](database/README.md).

La conexión se crea en [config/Database.php](config/Database.php), usa `utf8mb4`, excepciones PDO y consultas preparadas nativas.

### 5. Habilitar Apache y el servicio de base de datos

Inicia Apache y el servicio MySQL/MariaDB que corresponda al puerto configurado. Pueden pertenecer a instalaciones distintas; evita iniciar dos servicios sobre el mismo puerto.

Comprueba que Apache tiene `mod_rewrite` habilitado y que permite los `.htaccess` del proyecto. En una instalación estándar de XAMPP puedes revisar la sintaxis de configuración y los módulos con:

```powershell
& C:\xampp\apache\bin\httpd.exe -t
& C:\xampp\apache\bin\httpd.exe -M
```

La lista debe incluir `rewrite_module`. Si modificas la configuración de Apache, reinícialo después de validar la sintaxis.

### 6. Abrir la aplicación

Accede a [http://localhost/Tecno_Star/public/](http://localhost/Tecno_Star/public/). La dirección [http://localhost/Tecno_Star/](http://localhost/Tecno_Star/) debe redirigir a la entrada pública.

En la instalación local comprobada, Apache escucha en el puerto **8012**: [http://localhost:8012/Tecno_Star/public/](http://localhost:8012/Tecno_Star/public/). Con la URL base relativa del ejemplo no necesitas añadir el puerto a `APP_URL`; los enlaces conservan el origen del navegador.

Los enlaces, formularios, redirecciones, endpoints del carrito y la ruta de la cookie se derivan de `APP_URL`. Las reglas de Apache utilizan rutas relativas y no contienen el nombre de la carpeta.

Ejemplos de configuración:

| Instalación | `APP_URL` |
| --- | --- |
| XAMPP en la carpeta de esta guía | `/Tecno_Star/public` |
| Otra subcarpeta | `/mi-tienda/public` |
| Dominio con `DocumentRoot` apuntando a `public/` | `https://tienda.example.com` |
| Entrada pública directamente en la raíz del sitio | `/` |

Configura la dirección pública real y el mismo origen desde el que navegan los usuarios. No se admiten consultas, fragmentos, credenciales ni segmentos `..` en la URL base. Al usar un dominio, configura Apache para servir el directorio `public/` y permitir su `.htaccess`; cambiar `APP_URL` no configura el servidor ni el DNS. Los enlaces a imágenes guardados previamente en la base como URL absoluta o ruta de otra instalación deben revisarse por separado al trasladar datos.

## Estructura y funcionamiento

```text
Tecno_Star/
|-- app/
|   |-- Controllers/        Atención de solicitudes y selección de vistas
|   |-- Models/             Consultas y persistencia mediante PDO
|   `-- Views/              Plantillas PHP: inicio, tienda, cuentas y administración
|-- config/
|   |-- config.php          Carga y validación de configuración externa
|   |-- Environment.php     Lectura de .env y variables del entorno
|   |-- urls.php            Construcción central de URL internas
|   `-- Database.php        Conexión PDO reutilizada durante la solicitud
|-- core/
|   |-- Router.php          Registro y resolución de rutas GET y POST
|   |-- Auth.php            Identidad, estado y permisos actuales
|   |-- Csrf.php            Tokens de sesión para cambios de estado
|   |-- SessionManager.php  Inicio, vencimiento y renovación de sesiones
|   |-- LoginLimiter.php    Límites compartidos de intentos de acceso
|   |-- ErrorHandler.php    Respuestas seguras y referencias de error
|   `-- Logger.php          Registros privados y rotación
|-- database/               Esquema SQL, migraciones, ejemplos y herramientas CLI
|-- routes/
|   `-- web.php             Rutas y controladores de la aplicación
|-- public/
|   |-- index.php           Entrada HTTP, sesión y ejecución del router
|   |-- .htaccess           Reescritura de rutas hacia index.php
|   `-- assets/             CSS, JavaScript e imágenes
|-- storage/
|   |-- logs/               Registros JSON privados
|   `-- security/login/     Contadores privados del límite de acceso
|-- tests/                  Pruebas de integración sobre bases nuevas aisladas
|-- graphify-out/           Artefactos de análisis; no intervienen en la aplicación
|-- .htaccess               Redirección inicial y bloqueo de directorios internos
|-- .env.example            Configuración de ejemplo sin secretos reales
|-- .env                    Configuración local ignorada por Git
|-- README.md               Instalación y uso local
`-- PLAN_DE_MEJORAS.md      Tareas y criterios de aceptación
```

Apache envía las rutas de la aplicación a `public/index.php`, que carga la configuración, inicia la sesión y registra las rutas. El router ejecuta el controlador correspondiente. Los controladores utilizan modelos para consultar la base de datos y cargan las vistas para generar HTML; las rutas de datos de producto y carrito devuelven JSON.

El carrito y los favoritos de visitantes se guardan en `localStorage`. Al iniciar sesión se sincronizan con la base de datos. La sesión se identifica con la cookie `TECNO_STAR_SESSION` y utiliza la ruta pública derivada de `APP_URL` (por ejemplo `/Tecno_Star/public/`). Por defecto vence tras 30 minutos de inactividad o 12 horas desde el acceso, aunque haya actividad. La cookie es de navegador y no conserva por defecto el acceso entre reinicios. No se necesita hacer escribible todo el proyecto: PHP necesita acceso a su directorio de sesiones y al directorio privado `storage/` para registros y contadores.

## Rutas principales

Todas las rutas de esta tabla se añaden al prefijo `/Tecno_Star/public`.

Si cambias `APP_URL`, utiliza su ruta de montaje en lugar de ese prefijo.

| Método | Ruta | Función y acceso |
| --- | --- | --- |
| GET | `/` | Presentación pública |
| GET | `/store` | Catálogo público; requiere base de datos |
| GET | `/product?id=ID` | Detalle de producto |
| GET | `/product/data?id=ID` | Datos públicos del producto en JSON |
| GET / POST | `/login` | Formulario e inicio de sesión |
| GET / POST | `/register` | Formulario y creación de cuenta de cliente |
| GET | `/auth/google` | Aviso de configuración pendiente; OAuth no implementado |
| GET | `/profile` | Datos de la cuenta autenticada |
| POST | `/logout` | Cierre de sesión con token CSRF; GET devuelve 404 |
| GET | `/shopping/state` | Carrito y favoritos del usuario autenticado |
| POST | `/shopping/update` | Actualización mediante JSON; requiere sesión |
| GET | `/admin` | Métricas; requiere rol Administrador |
| GET | `/admin/tables/data` | JSON protegido de DataTables para productos, categorías, marcas, inventario y movimientos |
| GET | `/admin/products` | Productos con búsqueda, filtros y paginación SQL |
| GET | `/admin/products/edit?id=ID` | Editar producto; sin ID, crear |
| POST | `/admin/products/save` | Crear o editar, con CSRF e imagen opcional |
| POST | `/admin/products/state` | Activar o desactivar producto, con CSRF |
| GET | `/admin/catalog?kind=categories` o `brands` | Gestión de categorías o marcas |
| POST | `/admin/catalog/save` | Crear, renombrar o cambiar estado, con CSRF |
| GET | `/admin/inventory` | Existencias, alertas y consulta del historial administrativo |
| POST | `/admin/inventory/move` | Entrada, salida, ajuste físico o mínimo; administrador y CSRF obligatorios |

## Autenticación y protección de formularios

`core/Auth.php` comprueba el estado y el rol de la cuenta contra la base de datos en cada solicitud con sesión. La sesión actualiza nombre y permisos; una cuenta desactivada o eliminada pierde su identidad. El perfil requiere autenticación, las consultas administrativas requieren el rol actual `Administrador` y el carrito responde con 401 si no hay una cuenta válida. Si la consulta de validación falla, se devuelve 503 sin autorizar el acceso.

Las solicitudes que modifican datos requieren el token de sesión generado por `core/Csrf.php`. Los formularios envían `_csrf`; las peticiones JSON del carrito envían `X-CSRF-Token`. Un token ausente, incorrecto o perteneciente a otra sesión produce 403 antes de ejecutar la operación. El token rota al iniciar sesión o invalidar una identidad. Si una pestaña conserva un formulario anterior, recárgala antes de volver a enviarlo.

El cierre de sesión utiliza un formulario POST con token; los enlaces GET ya no cierran la sesión.

### Límites de acceso

Los límites se comparten entre procesos y sesiones mediante archivos con bloqueo. Antes de comprobar la contraseña se reserva un intento. Los valores por defecto permiten cinco intentos por combinación de correo e IP y treinta por IP en una ventana fija de quince minutos. Al agotarse un límite se responde con 429 y `Retry-After`; la ventana no se prolonga por nuevos intentos bloqueados.

Un acceso correcto reinicia el contador de la combinación, pero conserva el de la IP para limitar ataques que rotan cuentas. La IP proviene de `REMOTE_ADDR`; no se confía en `X-Forwarded-For`. Los nombres de archivo son hashes y su contenido solo guarda contador y vencimiento. Si no se puede leer o escribir un contador, el acceso se rechaza con 503. Esta implementación comparte estado en un servidor; varias instancias necesitan un almacén compartido con bloqueos compatibles o un servicio de límites común.

### Política de sesiones

El servidor comprueba los plazos en cada petición autenticada; no depende exclusivamente de la caducidad de la cookie ni de la limpieza de PHP. El identificador se regenera al entrar y periódicamente, conservando la identidad válida y el plazo absoluto. Se habilitan el modo estricto, el uso exclusivo de cookies, `HttpOnly` y `SameSite=Lax`; HTTPS activa `Secure`.

`SESSION_PERSISTENT=1` permite conservar la cookie entre reinicios hasta su vencimiento, pero no elimina el límite por inactividad ni el máximo absoluto. Las sesiones anteriores a esta política, que no tienen metadatos de inicio y actividad, requieren iniciar sesión nuevamente. El carrito vinculado a la cuenta permanece en la base de datos.

| Variable opcional | Valor por defecto | Significado |
| --- | --- | --- |
| `LOGIN_PAIR_LIMIT` | `5` | Intentos por correo e IP |
| `LOGIN_IP_LIMIT` | `30` | Intentos totales por IP |
| `LOGIN_WINDOW_SECONDS` | `900` | Ventana fija de límites |
| `SESSION_IDLE_SECONDS` | `1800` | Vencimiento por inactividad |
| `SESSION_ABSOLUTE_SECONDS` | `43200` | Duración máxima autenticada |
| `SESSION_REGENERATE_SECONDS` | `900` | Intervalo para renovar el identificador |
| `SESSION_PERSISTENT` | `0` | `0`: cookie de navegador; `1`: persistente |
| `LOG_RETENTION_DAYS` | `14` | Antigüedad máxima al ejecutar mantenimiento |
| `APP_STORAGE_PATH` | Directorio `storage/` del proyecto | Ruta absoluta privada, fuera de `public/` |

Estas variables se definen en el entorno o `.env`; los valores numéricos inválidos se rechazan y la inactividad no puede superar el plazo absoluto.

### Errores y registros

El punto de entrada registra el manejador antes de cargar la configuración. Las excepciones, advertencias y errores de ejecución se convierten en respuestas genéricas con una referencia y la cabecera `X-Request-ID`. Las rutas de datos y peticiones JSON conservan un formato JSON. Los fallos de base de datos o almacenamiento del límite responden con 503; los demás fallos internos, con 500. Los errores esperados de validación conservan sus códigos 400, 401, 403, 404 o 422; el límite de acceso utiliza 429.

Los registros son líneas JSON en `storage/logs/app-AAAA-MM-DD.log`, con fecha UTC, evento, nivel, referencia, clase/código de excepción, archivo y línea, y llamadas sin argumentos. No se guardan mensajes de excepción, consultas, formularios, correos, IP, cookies, contraseñas ni tokens. También se registran accesos correctos, fallos de credenciales, expiración y logout. Si falla el registro privado, se envía un aviso fijo con la referencia al registro nativo de PHP, sin detalles internos.

Se rota el archivo al alcanzar cinco MiB. La retención se aplica mediante el comando de mantenimiento; no se creó una automatización. Apache bloquea el almacenamiento y los archivos `.env`. En Apache con mod_php, el `.htaccess` público desactiva también los errores en pantalla antes del bootstrap. Si despliegas con otro manejador de PHP, configura `display_errors=Off` y `display_startup_errors=Off` en ese servicio para cubrir errores anteriores a la aplicación.

Para revisar contadores vencidos y registros fuera de retención, sin eliminar nada:

```powershell
& C:\xampp\php\php.exe database/maintenance.php
```

Para aplicar la limpieza:

```powershell
& C:\xampp\php\php.exe database/maintenance.php --prune
```

Ejecuta el mantenimiento periódicamente según la actividad. Solo elimina contadores vencidos y archivos de registro reconocidos fuera del plazo; no modifica usuarios, sesiones ni datos del negocio. Los bloqueos evitan borrar contadores que estén siendo utilizados. Un contador corrupto se conserva para revisión en lugar de ignorar el límite.

## Comprobación de la instalación

### Sintaxis y configuración

Desde la raíz del proyecto, ejecuta:

```powershell
$phpExe = 'C:\xampp\php\php.exe'
$phpFiles = Get-ChildItem app,config,core,routes,public -Recurse -Filter *.php
foreach ($file in $phpFiles) {
    & $phpExe -l $file.FullName
    if ($LASTEXITCODE -ne 0) { throw "Error de sintaxis: $($file.FullName)" }
}
```

Si tienes Node.js instalado, puedes comprobar el JavaScript sin instalar paquetes:

```powershell
node --check public/assets/js/app.js
```

Para comprobar la conexión desde PHP sin modificar datos, ejecuta desde la raíz del proyecto:

```powershell
@'
<?php
require getcwd() . '/config/config.php';
require BASE_PATH . '/config/Database.php';
try {
    $result = Database::connection()->query('SELECT 1')->fetchColumn();
    echo (int) $result === 1 ? "Conexion correcta\n" : "Resultado inesperado\n";
    exit((int) $result === 1 ? 0 : 1);
} catch (PDOException $exception) {
    file_put_contents('php://stderr', "No se pudo conectar. Revisa servicio, puerto, base y credenciales.\n");
    exit(1);
}
'@ | & C:\xampp\php\php.exe
```

`SELECT 1` verifica la conexión, pero no demuestra que el esquema o los datos estén completos. El PHP de consola y el de Apache pueden cargar configuraciones diferentes.

### Comprobaciones en el navegador

También puedes ejecutar las pruebas de configuración sin conectar con MySQL:

```powershell
& C:\xampp\php\php.exe tests/configuration.php
```

Comprueban lectura de `.env`, prioridad del entorno, validaciones y ejecución PHP de rutas con distintas URL base. Estas comprobaciones aisladas no sustituyen las siguientes pruebas HTTP en Apache:

Las pruebas de límites, concurrencia, vencimiento, errores seguros, rotación y mantenimiento no requieren MySQL:

```powershell
& C:\xampp\php\php.exe tests/foundation.php
```

Utilizan únicamente directorios temporales propios y los conservan para diagnóstico. La prueba de rotación requiere permisos para renombrar archivos dentro de esos directorios. Las pruebas HTTP e integración con MariaDB se describen en [database/README.md](database/README.md).

1. Abre `/Tecno_Star/` y comprueba la redirección y la página de inicio.
2. Abre `/Tecno_Star/public/login` y `/Tecno_Star/public/register`; verifica estilos y formularios.
3. Sin iniciar sesión, abre `/Tecno_Star/public/admin`; debe responder con acceso no autorizado (403).
4. Sin iniciar sesión, abre `/Tecno_Star/public/shopping/state`; debe devolver un error JSON con estado 401.
5. Con la base instalada o restaurada, abre el catálogo. Aparecen los productos activos con categoría/marca activa y al menos una unidad disponible. La tarjeta muestra la cantidad obtenida de `stock_actual - stock_reservado`; los agotados o completamente reservados se omiten. Al cargar de nuevo la tienda se consulta la cantidad actual. El inventario administrativo conserva todos los productos.
6. En una base de prueba, registra una cuenta Cliente, inicia sesión, agrega un producto al carrito y recarga para comprobar persistencia.
7. Con una cuenta Administrador existente, comprueba las métricas y los listados. Prueba la creación, edición, desactivación y reactivación del catálogo en una base aislada. Pedidos, ventas y usuarios corresponden a planes posteriores.

El registro y los cambios de carrito escriben datos: utiliza una base de prueba para esas comprobaciones. La revisión de sintaxis por sí sola no valida la conexión, los permisos de Apache ni el flujo completo.

### Prueba aislada del contenido de tienda

Con una instancia de MariaDB de pruebas disponible y credenciales administrativas en `.env.migrations`, ejecuta desde la raíz:

```powershell
& C:\xampp\php\php.exe tests/storefront-run.php --database=tecno_star_test_store_revision01 --host=127.0.0.1 --port=13308 --user=root
```

Usa un nombre nuevo con prefijo `tecno_star_test_store_` en cada ejecución. La prueba rechaza bases existentes, prepara ocho productos y dos clientes sintéticos, inicia un servidor local en el puerto 18014 y verifica catálogo, estados vacíos y de error, persistencia, aislamiento entre cuentas y CSRF. Al finalizar detiene el servidor y elimina únicamente su cuenta SQL temporal; conserva la base y los registros privados temporales para diagnóstico. No cambia `.env` ni los datos de la tienda de trabajo. Las imágenes de la preparación visual apuntan al Apache local del puerto 8012; las comprobaciones HTTP automatizadas no requieren cargarlas.

La revisión visual del Plan 1 cubre escritorio, tableta y móvil. La ubicación reservada para pedidos y los resultados detallados están en [PLAN_DE_MEJORAS.md](PLAN_DE_MEJORAS.md).

### Administración del catálogo (Plan 3)

En `/admin/products`, el administrador puede buscar por nombre o código y combinar filtros de categoría, marca y estado; inicialmente se muestran 12 productos por página. Categorías y marcas también tienen búsqueda, estado y paginación. Crear y editar abre un modal accesible; JavaScript envía los formularios y actualiza el listado sin salir de él. Las confirmaciones aparecen en toasts temporales que se pueden cerrar; los errores de validación permanecen dentro del modal conservando los datos. El modal admite Escape, cierre explícito y devuelve el foco al botón que lo abrió. Solo se aceptan escrituras de administradores activos con token CSRF válido.

Las tablas de productos, categorías, marcas, inventario, movimientos y pedidos utilizan DataTables 3.1.4 con procesamiento del servidor. Permiten elegir 12, 25, 50 o 100 filas, buscar, ordenar columnas y recorrer todas las páginas. `/admin/tables/data` revalida al administrador; `/orders/data` limita resultados al propietario o al personal autorizado. Parametrizan filtros y limitan las columnas de orden a una lista del servidor. Los conteos globales y filtrados respetan el mismo ámbito de acceso. Las celdas escapan los textos y atributos. Los indicadores de inventario son agregados de todos sus registros, independientes de la página mostrada.

DataTables se sirve desde `public/assets/vendor/datatables`, sin jQuery ni consulta al CDN en tiempo de uso. Los estilos propios están en `admin-tables.css` y la integración en `admin-tables.js`. Al guardar desde un modal se conservan búsqueda, filtros, orden, tamaño y página, y se reconstruye la tabla sin duplicar sus controles. Se muestran errores de carga con reintento si la consulta falla. La paginación HTML inicial queda como alternativa cuando no se carga JavaScript; el inventario inicia con 12 filas. Las nuevas tablas administrativas deben incluir `partials/table-assets.php` y los atributos `data-admin-datatable`, `data-table-kind` y `data-table-url`, con su consulta autorizada correspondiente. La navegación entre módulos queda en el sidebar, sin repetir las pestañas Productos/Categorías/Marcas.

Los estilos del módulo están en `public/assets/css/admin-catalog.css`, separados de los estilos comunes del panel (`admin.css`) y de `app.css`. Su comportamiento está en `public/assets/js/admin-catalog.js`. Las vistas versionan los recursos con su fecha de modificación para evitar que la caché mantenga diseños anteriores.

La navegación compartida de dashboard, catálogo e inventario está en `partials/sidebar.php`, con estilos propios en `admin-shell.css` y comportamiento en `admin-shell.js`. Desde 992 px, el botón permite alternar entre sidebar completo y una columna de 84 px con logo e iconos; conserva la preferencia en el navegador entre pantallas. Los enlaces mantienen sus nombres accesibles y títulos. Las secciones aún pendientes aparecen deshabilitadas con «Próximamente».

En pantallas menores de 992 px, el menú permanece oculto y se abre como panel lateral con fondo superpuesto. Bloquea el scroll y el foco del contenido mientras está abierto; admite Escape, cierre explícito y clic en el fondo. Cierra al navegar y devuelve el foco al control de apertura cuando se cancela. El área desplazable usa un scrollbar fino con los colores del sidebar. La identidad del usuario se muestra únicamente al desplegar la cuenta compacta en la esquina superior derecha, junto con Mi perfil y Cerrar sesión por POST con CSRF.

El código de producto admite hasta 50 letras ASCII, números, puntos, guiones y guiones bajos; se guarda en mayúsculas. Es único incluso entre productos inactivos. Los precios aceptan valores no negativos de hasta 9999999999.99 con dos decimales como máximo. El nombre es obligatorio (150 caracteres), la categoría es obligatoria y la marca es opcional. No se puede activar un producto con categoría o marca inactiva.

Crear un producto crea su inventario en cero dentro de la misma transacción. Editarlo o desactivarlo no modifica stock ni elimina referencias de carritos o favoritos. Desactivar una categoría o marca oculta sus productos del catálogo y detalle e impide nuevos agregados. Los productos ya guardados permanecen en carrito y favoritos con un aviso y opción de retirarlos. Reactivarla restaura la visibilidad de los productos que sigan activos. La gestión de existencias se realiza desde Inventario.

### Carrito y favoritos (Plan 5)

El servidor confirma cada cambio antes de mostrarlo como guardado. `shopping-client.js` conserva una solicitud pendiente por cuenta en el navegador y `tbshoppingoperaciones` registra su clave y huella en la misma transacción que el carrito. Si la respuesta se pierde, **Reintentar / actualizar** reenvía exactamente esa solicitud: no vuelve a sumar cantidades ni a importar el carrito visitante. Las claves confirmadas no se eliminan automáticamente ni se permiten UPDATE/DELETE en su tabla desde la cuenta de la aplicación.

El lote de visitante queda vinculado a la primera cuenta que lo reclama. Si cambia la sesión durante un fallo, otra cuenta no importa ese lote. La vista antigua se vacía al detectar el cambio de cuenta; el servidor comprueba también el usuario destino y el CSRF. Se consulta el estado al abrir las listas, volver a la página y recibir cambios de otra pestaña. Web Locks coordina el navegador; las transacciones y revisiones protegen el servidor. Si Web Locks no está disponible, se explica el problema y no se simula un guardado.

El carrito muestra el precio actual y avisa si cambió desde el precio visto. **He revisado los precios actuales** confirma explícitamente esos importes; si vuelven a cambiar durante la revisión, devuelve un conflicto. Una reducción de stock mantiene la cantidad guardada y ofrece ajustarla a lo disponible. Productos desactivados o agotados no se pueden agregar; los desactivados ya guardados pueden retirarse y no se incluyen en el total estimado. Guardar el carrito no reserva stock: la reserva se crea al confirmar el pedido del Plan 6.

Las tarjetas del carrito omiten la descripción extensa, juntan cantidad y subtotal y resumen los avisos en una franja desplegable. La acción para ajustar stock queda en esa misma franja. El detalle completo del aviso sigue disponible al desplegarlo. Los estilos propios están en `public/assets/css/shopping.css`.

La API `/shopping/update` exige JSON con `operation_id` (64 caracteres hexadecimales), `target_user_id` entero y `expected_revision` entera, además de acción y producto/cantidad cuando corresponda. Las acciones son `sync`, `cart_add`, `cart_change` (delta 1 o -1), `cart_set_quantity`, `cart_remove`, `cart_review`, `wishlist_add` y `wishlist_remove`. Las ediciones absolutas antiguas reciben 409 y estado actualizado; los agregados relativos se serializan sin perder incrementos. Cantidades enteras de 1 a 1000000, hasta 100 productos de carrito y 200 favoritos por sincronización, petición máxima de 128 KiB. El precio enviado solo sirve como referencia del aviso, nunca como precio de venta.

Instala la migración 004 antes de usar esta versión. Para la instalación local antigua: `php database/enable-shopping.php` simula; `--apply` actualiza y concede SELECT/INSERT únicamente del diario; `--verify` comprueba esquema y permisos. No cambia cantidades, stock, cuentas ni claves. Pruebas aisladas:

```powershell
& C:\xampp\php\php.exe tests/catalog.php --database=tecno_star_test_catalog_shop06 --host=127.0.0.1 --port=13308 --user=root --shopping --inventory
& C:\xampp\php\php.exe tests/shopping-upgrade.php --database=tecno_star_test_shop_upgrade06 --host=127.0.0.1 --port=13308 --user=root
node tests/shopping-client.cjs
```

Usa bases nuevas en cada ejecución. Incluyen pérdida de respuesta tras commit, reintentos, concurrencia, cuentas separadas, precio/stock, permisos, rollback y conservación del esquema antiguo.

### Inventario (Plan 4)

Desde `/admin/inventory`, pulsa **Registrar** en el producto para abrir un modal de entrada, salida, ajuste al total físico contado o cambio del mínimo. Introduce una cantidad (hasta tres decimales) y un motivo. El responsable procede de la cuenta administrativa revalidada en BDD. Se muestran físico, reservado y disponible por separado; una alerta identifica los productos cuyo disponible es igual o inferior al mínimo. El botón **Historial** filtra los movimientos del producto; también puedes combinar tipo, responsable, fechas y búsqueda en DataTables.

Stock e historial se guardan en una transacción con bloqueo de fila. Se rechazan stock negativo, cantidades inválidas y físico inferior al reservado. Los ajustes de conteo detectan cambios desde que se abrió el modal. La clave de solicitud protege los reintentos contra duplicación: ante respuesta incierta, reintenta en el mismo formulario. Las confirmaciones se muestran por toast; los errores permanecen en el modal. Los estilos y el comportamiento están separados en `admin-inventory.css` y `admin-inventory.js`.

Instalaciones nuevas necesitan la migración 003 antes de crear su cuenta limitada. La instalación antigua local se actualiza con `database/enable-inventory.php --apply`, después de revisar y autorizar sus permisos, y se verifica con `--verify`. El adaptador comprueba la estructura antigua y conserva sus tablas, existencias decimales e historial anterior. El historial nuevo `tbstockmovimientos` comienza con un saldo de apertura; el historial antiguo se conserva en `tbmovimientosinventario`, sin reconstruir ni atribuir a personas operaciones previas. Detalles y pruebas en [database/README.md](database/README.md).

Prueba completa aislada: `php tests/catalog.php --database=tecno_star_test_catalog_inv04 --host=127.0.0.1 --port=13308 --user=root --inventory`. La prueba de actualización es `php tests/inventory-upgrade.php --database=tecno_star_test_inv_upgrade03 --host=127.0.0.1 --port=13308 --user=root`. Usa nombres nuevos; se conservan las bases de diagnóstico y se retiran las cuentas SQL y servidores temporales. Las reservas se crearán y liberarán desde el flujo de pedidos del Plan 6.

Las imágenes se validan por contenido mediante `fileinfo` y `getimagesize`: JPG, PNG o WebP reales, hasta 4 MiB, hasta 6000 px por lado y 20 megapíxeles. El servidor genera nombres aleatorios de 48 caracteres hexadecimales y descarta el nombre del archivo original. `public/uploads/products` debe ser escribible por PHP; incluye una configuración Apache que bloquea ejecución PHP y tipos no admitidos. Sus imágenes están excluidas de Git y deben incluirse en los respaldos. Las imágenes anteriores a un reemplazo se conservan; no hay borrado automático de archivos previamente asociados. Si falla la escritura SQL, se descarta únicamente la nueva carga de esa operación.

En Apache con mod_php se fijan `upload_max_filesize=4M` y `post_max_size=6M` mediante `public/.htaccess`. Con PHP-FPM u otro servidor, configura esos límites en su configuración PHP y replica el bloqueo de ejecución en uploads. Los formularios que excedan el máximo de la petición reciben 413 con un mensaje de tamaño. La ruta de las imágenes nuevas se adapta a `APP_URL` en tienda, detalle, carrito, favoritos y administración.

Las instalaciones con la cuenta SQL anterior requieren `php database/enable-catalog.php --apply`, después de revisar la simulación sin argumentos. `--verify` comprueba los permisos sin modificar datos. Los permisos y la conexión administrativa se describen en [database/README.md](database/README.md).

Prueba integral en una instancia aislada, usando un nombre nuevo en cada ejecución:

```powershell
& C:\xampp\php\php.exe tests/catalog.php --database=tecno_star_test_catalog_revision01 --host=127.0.0.1 --port=13308 --user=root
```

La prueba crea una base nueva, usuarios sintéticos y una cuenta SQL limitada; comprueba CRUD, validaciones, referencias, permisos, CSRF, paginación, cargas y reemplazos. Inicia PHP temporal en loopback 18014 y lo cierra al terminar; elimina su cuenta SQL y sus imágenes de prueba, conservando la base y registros temporales para diagnóstico. No la ejecutes simultáneamente con otras suites que usan 18014. Con `--preview` mantiene la vista sintética abierta hasta pulsar Enter en la consola; las credenciales mostradas pertenecen exclusivamente a esa base de prueba.

## Problemas frecuentes

| Síntoma | Qué revisar |
| --- | --- |
| Las rutas devuelven 404 | Ubicación y nombre del proyecto, `mod_rewrite`, `AllowOverride` y `public/.htaccess` |
| Apache devuelve 500 al cargar | Sintaxis de `.htaccess`, módulos habilitados y registro de errores de Apache |
| No conecta con MySQL | Servicio activo, puerto `DB_PORT`, credenciales y existencia de la base |
| Error de tabla inexistente | Migraciones completas en la base nueva o restauración de un esquema compatible; una base vacía no basta |
| El catálogo aparece vacío | Revisa que productos, categorías y marcas estén activos y que stock_actual - stock_reservado permita al menos una unidad |
| El catálogo se consulta pero no guarda | Permisos de la cuenta limitada; revisa `database/enable-catalog.php --verify` y el log privado |
| El registro no puede completarse | Esquema compatible, rol `Cliente`, restricciones y conexión |
| El administrador recibe 403 | Cuenta autenticada con rol `Administrador` |
| Faltan estilos, iconos o gráficos | Acceso a los CDN, conexión a Internet y consola del navegador |
| El acceso se pierde entre páginas | Cookies habilitadas, `APP_URL` coincidente con la dirección pública y directorio de sesiones escribible |
| La consola usa otro PHP | Ejecutar la ruta explícita de XAMPP; revisar `Get-Command php` |

## Estado de esta documentación

Esta guía describe el código actual. Las migraciones y los permisos de los planes 1 a 7 están activados en la instalación local, con las autorizaciones explícitas correspondientes. Las pruebas de escritura y concurrencia utilizan bases aisladas; no crean pedidos de prueba en la base del negocio. El 19 de octubre de 2026 se verificaron las dependencias Composer, se cargó GD reiniciando Apache y se comprobó QR/SMTP en el servidor web. Gmail aceptó el correo real del pedido 1 confirmado y la cola lo registró como enviado; queda comprobar la bandeja del destinatario. OAuth de Google sigue pendiente de integración.

## Pedidos y QR · Plan 6

Desde el carrito, **Continuar con el pedido** abre `/checkout`. Se revisan los importes actuales y el contacto; confirmar recalcula todo en el servidor y reserva por **24 horas desde la creación**, con un número único. Se guardan código, nombre, cantidad, precio y subtotal históricos. Se vacía el carrito en la misma transacción. La clave de solicitud permite reintentar una respuesta perdida sin duplicar pedidos.

Las cuentas anteriores sin perfil de cliente, incluidas las administrativas, pueden comprar: el perfil asociado se crea al confirmar un pedido válido, con identidad de la cuenta y teléfono de retiro validado. Esto conserva su rol y permisos. Si falla la operación, tampoco se guarda el perfil nuevo; los reintentos no lo duplican.

Estados: `pendiente → confirmado → en_preparacion → listo → entregado`. Se puede cancelar desde cualquier estado previo a la entrega; el cliente solo antes de la preparación. No se permiten saltos ni reabrir estados terminales. Cancelar/vencer libera la reserva; entregar descuenta el físico y libera lo reservado exactamente una vez. Se registra responsable, fecha y motivo de cada transición y movimiento. El vencimiento aparece como cancelado, con el motivo del Sistema. Las fechas de pedido e historial se guardan en UTC y se presentan en hora de El Salvador; los movimientos mantienen el reloj del historial de inventario.

**Mis pedidos** muestra exclusivamente pedidos propios, incluido su conteo DataTables. El administrador usa **Pedidos** en el sidebar. Un empleado activo necesita rol Empleado y permiso de pedidos/caja desde **Usuarios**. Para cuentas sin fila de permiso se conserva la alternativa `ORDER_STAFF_USER_IDS`; un permiso guardado, incluido uno desactivado, prevalece sobre esa lista. El escaneo abre el detalle; el cobro y entrega requieren confirmación explícita en el Plan 7. El permiso no habilita catálogo, inventario ni usuarios.

Al pasar a **Confirmado**, la misma transacción encola un correo con productos, total, código y QR. PHPMailer intenta enviarlo después del commit. La cola usa una reclamación temporal para evitar envíos simultáneos y conserva los fallos para reintentar; no envía confirmaciones pendientes de pedidos cerrados o vencidos. El QR contiene el enlace del sistema con el código; leerlo exige autenticación y permiso. El lector acepta USB y cámara de laptop/celular. ZXing se aloja localmente. La cámara web requiere localhost o HTTPS. Configuración de Gmail, direcciones de red y claves OAuth en [docs/GUIA_GMAIL_GOOGLE.md](docs/GUIA_GMAIL_GOOGLE.md).

Las reservas vencidas se liberan al consultar/operar la tienda, carrito, inventario o pedidos. Para procesarlas también durante períodos sin visitas y reenviar correos pendientes, configura en el Programador de tareas, por ejemplo cada minuto:

```powershell
C:\xampp\php\php.exe database/expire-orders.php --run
C:\xampp\php\php.exe database/send-order-mails.php --run
```

No se creó una tarea programada automáticamente. Cada ejecución procesa hasta 1000 reservas y 50 correos. Un fallo SMTP espera cinco minutos antes del siguiente intento. SMTP no garantiza entrega exactamente una vez ante una desconexión posterior a la aceptación; se conserva un Message-ID estable.

Instalación antigua: `php database/enable-orders.php` simula; `--apply` migra y concede los permisos específicos; `--verify` comprueba esquema y derechos. No adopta pedidos antiguos como reservas nuevas ni envía correos de esos registros. La actualización usa su propio historial/checksum; conserva los estados históricos existentes y rechaza actualizaciones parciales.

Pruebas (usa nombres de base nuevos y un servidor aislado):

```powershell
C:\xampp\php\php.exe tests/catalog.php --database=tecno_star_test_catalog_ord_nuevo --host=127.0.0.1 --port=13308 --user=root --inventory --shopping --orders
C:\xampp\php\php.exe tests/orders-upgrade.php --database=tecno_star_test_orders_nuevo --host=127.0.0.1 --port=13308 --user=root
node tests/order-scanner.cjs
```

Cubren última unidad concurrente, reintentos, entrega/cancelación/vencimiento únicos, reversión por fallo de auditoría/cola, precios históricos, acceso por propietario y permiso, CSRF, QR generado y decodificado, contenido MIME, fallos SMTP simulados y reclamación simultánea de correos. El hardware de cámara/USB y la entrega real de Gmail deben comprobarse en la instalación con sus dispositivos y credenciales.

## Ventas, usuarios y cuentas · Plan 7

### Operación de caja

1. Abre o escanea un pedido y avánzalo a **Listo** antes de vencer su reserva.
2. Un administrador o empleado autorizado pulsa **Cobrar y entregar**, introduce el efectivo recibido y confirma la operación. Se muestran total y cambio; el servidor valida los importes.
3. Cobro, entrega, descuento físico, liberación de reserva e historial se guardan en una transacción. Un fallo revierte todo. La solicitud repetida recupera el mismo cobro; un segundo cobro del pedido se rechaza.
4. Consulta **Ventas** (`/admin/sales`) y el comprobante (`/sales/detail?id=...`). Se conservan los productos/precios del pedido y el nombre del responsable al cobrar. El cliente accede a sus registros desde **Mis compras** (`/purchases`).

El método disponible es **efectivo**. La consulta permite filtrar por fechas de El Salvador, método y estado; métricas y conteos corresponden al mismo filtro completo. Las listas administrativas utilizan DataTables. **Mis compras** del cliente muestra tarjetas con paginación del servidor: cuatro por página, dos columnas en escritorio y una en móvil. Conserva búsqueda por comprobante/pedido, estado, fechas y totales filtrados; cada tarjeta abre el comprobante propio y distingue compras pagadas y reembolsadas. La entrega directa por cambio de estado se bloquea cuando está activo este módulo.

Solo el administrador puede **anular una venta pagada**. Debe indicar el motivo, confirmar el reembolso completo y elegir si la mercancía volvió físicamente. Se registra el reembolso y, únicamente en ese caso, se repone el stock con movimiento `devolucion`. La anulación conserva los datos históricos; el pedido permanece entregado. No hay devolución parcial ni transferencia bancaria automática: el personal confirma el dinero devuelto fuera del sistema.

### Usuarios, perfil y recuperación

**Usuarios** (`/admin/users`) permite crear y editar mediante modal nombres, rol, estado y permiso de pedidos/caja del empleado. El correo existente permanece de consulta. El propio administrador no puede quitarse el acceso; se protege al último administrador activo. Las ediciones concurrentes se rechazan para no sobrescribir datos recientes. Las acciones quedan auditadas y las respuestas correctas utilizan toast.

En **Mi perfil** (`/profile`), la cuenta puede editar nombres/teléfono o cambiar su contraseña, siempre presentando la clave actual. Las nuevas claves requieren 12–72 bytes; el acceso con contraseñas anteriores sigue siendo compatible. El cambio de clave y la desactivación invalidan sesiones anteriores. Los permisos y roles se revalidan en la siguiente solicitud; las sesiones anteriores a esta actualización deben iniciar sesión de nuevo.

**Olvidé mi contraseña** abre `/forgot-password`. La respuesta es genérica exista o no la cuenta y hay límites de intentos. Se envía un enlace con token aleatorio de un solo uso y 20 minutos de vigencia; solo su hash se almacena. El token viaja en el fragmento (`/reset-password#token=...`), se retira de la barra al cargar y se presenta por POST. Una nueva solicitud invalida enlaces previos; cambiar la clave revoca las otras sesiones. Si falla el envío, el token se invalida sin exponer información de la cuenta. Estos mensajes usan el mismo Gmail SMTP que los pedidos, con envío directo y sin cola de recuperación.

### Activación y comprobación

La instalación local ya se activó con autorización el 19 de octubre de 2026. Para otra instalación compatible, con credenciales administrativas locales privadas:

```powershell
C:\xampp\php\php.exe database/enable-sales-accounts.php
C:\xampp\php\php.exe database/enable-sales-accounts.php --apply
C:\xampp\php\php.exe database/enable-sales-accounts.php --verify
C:\xampp\php\php.exe database/check-mail.php --connect
```

El primer comando simula. La migración 006 conserva cuentas, contraseñas, roles asignados, ventas y saldos previos; las ventas antiguas no se adoptan como cobros nuevos. El adaptador soporta el esquema antiguo con historial/checksum propio. Los permisos exactos están en [database/README.md](database/README.md); no se concede DELETE para estas funciones. `check-mail.php --connect` comprueba TLS/autenticación sin mandar mensajes ni mostrar credenciales.

Pruebas aisladas; usa nombres nuevos de base en cada ejecución y un servidor de pruebas:

```powershell
C:\xampp\php\php.exe tests/catalog.php --database=tecno_star_test_catalog_sa_nuevo --host=127.0.0.1 --port=13308 --user=root --inventory --shopping --orders --accounts
C:\xampp\php\php.exe tests/sales-accounts-upgrade.php --database=tecno_star_test_sales_up_nuevo --host=127.0.0.1 --port=13308 --user=root
C:\xampp\php\php.exe tests/security-http.php --database=tecno_star_test_db_sa01 --host=127.0.0.1 --port=13308 --user=root
node tests/accounts-client.cjs
```

La prueba de seguridad requiere una base preparada previamente con `tests/database.php`, que incluye sus productos de ejemplo; no utilices una base del negocio ni la base de `tests/catalog.php`. Se verificaron efectivo/cambio, idempotencia y concurrencia, rechazo y rollback de entrega, devolución con/sin reposición, separación de compras, permisos y sesiones, edición simultánea, tokens vencidos/usados/concurrentes, límites de recuperación y permisos SQL sobre esquema nuevo y antiguo.

### Revisión del cliente · Planes 1 a 7

La [revisión del cliente](docs/REVISION_CLIENTE_PLANES_1_A_7.md) registra los ajustes visuales, permisos y pruebas de escritorio/móvil. Agrega `--customer` al comando de `tests/catalog.php` con `--inventory --shopping --orders --accounts` para verificar pantallas propias, registro y rechazo de acciones administrativas con una cuenta Cliente.

Mis compras muestra tarjetas y un comprobante sin controles de caja. El teléfono de registro acepta formato salvadoreño con espacios/guiones y prefijo `+503` opcional. El acceso con Google está implementado y aparece cuando se configuran `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` y `GOOGLE_REDIRECT_URI` en `.env`. Activa su tabla mediante `php database/enable-google.php --apply` (incluye respaldo) y verifica con `--verify`. Consulta [la guía OAuth](docs/GUIA_GMAIL_GOOGLE.md). Las cuentas existentes confirman su contraseña una vez para vincular; las nuevas completan sus datos de contacto y reciben rol Cliente. Pruebas: `php tests/google-oauth.php` y `php tests/google-http.php`.

La [revisión de administración](docs/REVISION_ADMINISTRACION.md) documenta los enlaces para volver, la jerarquía de acciones, los controles según el rol, el historial plegable y las comprobaciones de escritorio/móvil. El historial de inventario permite filtrar devoluciones de ventas.

El [cierre de administración](PLAN_CIERRE_ADMINISTRACION.md) implementa bandejas de Pedidos, tarjetas del cliente de cuatro por página, Caja con escaneo y cobro e integración transaccional de reinversión (50 % reposición + 5 % crecimiento). [Resultados y guía](docs/CIERRE_ADMINISTRACION.md). El código está probado en datos aislados y la activación SQL real se completó con autorización expresa, copia previa y permisos exclusivamente SELECT/INSERT sobre reinversión. Queda pendiente la prueba de lectores físicos.

En Caja, escanear un pedido confirmado muestra su preparación y permite marcarlo listo desde la misma pantalla. Después se introduce **Efectivo recibido** y se pulsa **Registrar pago → Confirmar pago y entrega**. La confirmación guarda `tbventas.total`, `recibido`, `cambio`, fecha y responsable, junto con la entrega, el inventario y la reinversión. La operación aparece entonces en Ventas. Escanear o avanzar la preparación por sí solos no crean una venta.

Cambio posterior: al marcar **Listo para retirar** se prepara el aviso por correo y se vuelve al detalle del pedido, sin entrar automáticamente a Caja. Para cobrar se utiliza su enlace manual. Los correos de confirmación, retiro y recuperación comparten el diseño con logo incrustado. Las pruebas y la activación de la migración 008 están completadas tras autorización expresa, con copia previa, datos conservados y permisos sin cambios. Consulta [el estado del proyecto y los pendientes](docs/ESTADO_PROYECTO_2026_10_19.md).
