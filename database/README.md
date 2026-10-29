# Base de datos y migraciones

## Alcance

`migrations/001_initial_schema.sql` es el esquema inicial canónico para una instalación nueva. Incluye las 13 tablas utilizadas por los modelos y las métricas actuales, con claves foráneas, índices únicos, importes `DECIMAL` y restricciones para precios, cantidades y reservas. Usa InnoDB y `utf8mb4_unicode_ci`.

`migrations/002_required_roles.sql` carga los roles `Administrador` y `Cliente`. No crea usuarios ni contraseñas predeterminadas. Pedidos y ventas contienen la estructura mínima para las consultas actuales; sus flujos y detalles se ampliarán con nuevas migraciones en los planes correspondientes.

`migrations/003_inventory_movements.sql` amplía el stock del esquema canónico a `DECIMAL(12,3)` sin cambiar cantidades, añade la tabla 14, `tbstockmovimientos`, y registra los saldos existentes como apertura identificada por el sistema. Conserva el físico, reservado y mínimo; no intenta inventar movimientos anteriores. Aplicarla nuevamente no duplica aperturas.

`migrations/004_shopping_consistency.sql` añade revisión al carrito, precio de referencia a sus detalles y la tabla 15, `tbshoppingoperaciones`, para reconocer solicitudes repetidas. Inicializa la referencia con el precio actual, sin cambiar cantidades ni existencias. La aplicación no confunde esa referencia con el precio de venta.

`migrations/005_pickup_orders.sql` implementa pedidos, detalle, historial, reservas y cola de correo. `migrations/006_sales_accounts.sql` incorpora cobros vinculados al pedido, anulación auditada, permiso de empleados, versiones de cuenta/sesión y recuperación de contraseña. No convierte ventas antiguas en cobros del módulo ni altera sus importes.

### Habilitar ventas y cuentas del Plan 7

`php database/enable-sales-accounts.php` simula; `--apply` actualiza y concede los permisos de la matriz inferior; `--verify` comprueba las columnas y derechos esperados. Requiere la cuenta limitada configurada y credenciales administrativas locales privadas. En esta instalación se aplicó y verificó el 19 de octubre de 2026 con autorización expresa, conservando cuentas, hashes de contraseña, roles asignados, ventas y saldos anteriores.

El esquema canónico usa `schema_migrations`. Para el esquema antiguo, `SalesAccountsUpgrade.php` utiliza `sales_accounts_module_migrations`, checksum y bloqueo; conserva el enum/default anterior de ventas y rechaza ampliaciones parciales. La unicidad de cobro por pedido se aplica a las operaciones del módulo, sin imponerla a ventas antiguas duplicadas. Los comprobantes nuevos guardan el nombre del responsable en el momento del pago. Las anulaciones y auditorías no admiten modificación desde la cuenta de aplicación.

Prueba de instalación/actualización funcional con datos sintéticos: `php tests/sales-accounts-upgrade.php --database=tecno_star_test_sales_up_nuevo --host=127.0.0.1 --port=13308 --user=root`. Usa una base nueva con ese prefijo y un servidor aislado. Comprueba conservación, repetición, permisos limitados, cobro y devolución sobre los esquemas canónico y antiguo.

Probado con MariaDB 10.4.32. Para MySQL se requiere al menos 8.0.16 por la aplicación efectiva de restricciones `CHECK`; la compatibilidad con MySQL no se ha probado en esta tarea. MariaDB anterior a 10.4 tampoco se ha verificado.

## Instalación nueva

Desde PowerShell, en la raíz del proyecto, con el servicio de base de datos activo y `.env` creado a partir de `.env.example` y configurado:

```powershell
& C:\xampp\php\php.exe database/migrate.php --database=tecno_star_nueva --create
```

El nombre de destino es obligatorio. `--create` permite crear la base si no existe; sin esa opción debe existir previamente y estar vacía o tener un historial compatible. El host y puerto provienen del entorno o `.env`. Las credenciales administrativas se leen de `MIGRATION_DB_USER` y `MIGRATION_DB_PASSWORD` en el entorno o `.env.migrations`; si no están definidas, se utilizan las de `.env` durante la instalación inicial. Las opciones explícitas permiten cambiar el destino y el usuario:

```powershell
& C:\xampp\php\php.exe database/migrate.php --database=tecno_star_nueva --create --host=127.0.0.1 --port=3308 --user=root
```

Si necesitas otra contraseña, configura `MIGRATION_DB_PASSWORD` en el entorno del proceso o `.env.migrations`; no la incluyas en argumentos ni archivos versionados. Se conserva compatibilidad con `TECNO_STAR_MIGRATION_PASSWORD`, que tiene prioridad cuando está definida. Estas variables no cambian la conexión de la aplicación. Un archivo `.env.migrations` permite representar también una contraseña administrativa vacía en Windows.

Al terminar, actualiza `DB_NAME` y, si corresponde, las demás variables de conexión en `.env` o en el entorno del servidor para usar la nueva base. La herramienta no modifica esa configuración.

Para un catálogo de prueba, de forma opcional:

```powershell
& C:\xampp\php\php.exe database/seed.php --database=tecno_star_nueva --demo
```

Agrega la categoría `Demostración` y dos productos con códigos `DEMO-CABLE-USB` y `DEMO-AUDIFONOS`, con stock inicial. Repetir el comando no duplica los productos ni restablece precios o existencias de los productos ya creados. No representa inventario real; úsalo en instalaciones de demostración. Los ejemplos se cargan dentro de una transacción.

### Primera cuenta administrativa

Registra primero tu cuenta desde `/Tecno_Star/public/register`, conectando la aplicación a la base recién instalada. Se creará con rol `Cliente`. Un responsable con acceso SQL a esa misma base puede promover esa cuenta específica:

```sql
-- Sustituye el correo por el de tu cuenta registrada en la instalacion nueva.
UPDATE tbusuarios
SET id_rol = (SELECT id_rol FROM tbroles WHERE nombre_rol = 'Administrador')
WHERE correo = 'tu-correo@example.com' AND estado = 'activo';
```

Comprueba que se modificó únicamente la cuenta prevista. Cierra la sesión y vuelve a entrar. Una vez instalada la primera cuenta administrativa y el Plan 7, gestiona las demás desde Usuarios.

## Bases existentes

Para el aviso de «Listo para retirar», `008_ready_notifications.sql` añade el tipo de aviso a `tbretiocorreos` y cambia la unicidad a `(id_pedido,tipo)`. `ReadyMailUpgrade` conserva estados, intentos, identificadores y fechas anteriores. El activador `database/enable-ready-mail.php --apply` crea una copia SQL previa, compara los datos existentes y verifica que los permisos no cambien; `--verify` comprueba la cola con la cuenta de la aplicación. En esta instalación ambos comandos finalizaron correctamente tras autorización expresa; se conservaron datos y permisos. No genera correos históricos ni modifica pedidos o saldos.

No importes el SQL inicial ni ejecutes estas migraciones sobre la base de trabajo sin conocer su estructura. La herramienta rechaza una base que contenga tablas sin `schema_migrations`; no la vacía, no la adopta automáticamente ni modifica sus tablas.

Para incorporar una instalación antigua hace falta respaldarla y comprobar su compatibilidad. Los comandos de habilitación por módulo usan adaptadores específicos con historial/checksum y rechazan estructuras parciales; no importes el SQL inicial sobre ella. Los planes 3 a 7 se activaron en la instalación local con las autorizaciones correspondientes, conservando sus registros.

## Cuenta SQL limitada para la aplicación

Con todas las migraciones instaladas, una conexión administrativa local válida y un `.env` escribible:

```powershell
& C:\xampp\php\php.exe database/create-app-user.php --account=tecno_star_app --activate
```

La herramienta admite conexiones a `localhost` o `127.0.0.1` y crea por defecto la cuenta SQL en el host `localhost`. Puedes indicar `--account-host=127.0.0.1` si la configuración de resolución del servicio lo requiere. No modifica datos del negocio, cuentas SQL existentes ni las claves de otros usuarios.

El comando genera una contraseña aleatoria de 32 bytes (64 caracteres hexadecimales), verifica la conexión y la guarda en `.env`, sin mostrarla. La escritura de `.env` se realiza mediante reemplazo de un archivo temporal; ante un fallo antes de la activación se elimina únicamente la cuenta recién creada. Si existe configuración de conexión en variables del proceso, la herramienta rechaza la activación automática para no dejar una configuración local sin efecto.

Las credenciales administrativas utilizadas se conservan en `.env.migrations` si ese archivo no existía. La aplicación web carga únicamente `.env`; las herramientas CLI pueden cargar además `.env.migrations`. Ambos archivos están ignorados por Git y bloqueados por Apache. En un despliegue, las credenciales administrativas pueden suministrarse exclusivamente al entorno del proceso de migración.

| Tablas | Permisos de ejecución |
| --- | --- |
| Roles | SELECT |
| Ventas | SELECT, INSERT; UPDATE solo de estado |
| Anulaciones de ventas y auditoría de cuentas | SELECT, INSERT; sin UPDATE ni DELETE |
| Permisos de personal y recuperación de claves | SELECT, INSERT, UPDATE; sin DELETE |
| Pedidos | SELECT, INSERT, UPDATE; sin DELETE |
| Detalle e historial de pedidos | SELECT, INSERT; sin UPDATE ni DELETE |
| Cola de correos de confirmación | SELECT, INSERT, UPDATE; sin DELETE |
| Categorías, marcas y productos | SELECT, INSERT, UPDATE |
| Inventario | SELECT, INSERT solo de id_producto (stock inicial cero), UPDATE solo de stock_actual, stock_minimo y stock_reservado |
| Movimientos de inventario | SELECT, INSERT; sin UPDATE ni DELETE |
| Usuarios | SELECT, INSERT; UPDATE solo de nombres, apellidos, id_rol, estado, password_hash, session_version y cuenta_revision |
| Clientes | SELECT, INSERT; UPDATE solo de nombres, apellidos y telefono |
| Carritos y listas de deseos | SELECT, INSERT, UPDATE |
| Diario de operaciones del carrito | SELECT, INSERT; sin UPDATE ni DELETE |
| Detalles de carritos | SELECT, INSERT, UPDATE, DELETE |
| Detalles de listas de deseos | SELECT, INSERT, DELETE |

No se concede DDL, privilegios globales, acceso a `mysql.user` ni `GRANT OPTION`. Los historiales de migraciones quedan fuera de los permisos de ejecución. El catálogo no tiene DELETE; inventario no permite insertar valores de stock arbitrarios. `InventoryModel` actualiza físico/mínimo y registra movimientos; `OrderModel` reserva, libera o entrega dentro de transacciones con historial. Quien tenga las credenciales SQL podría escribir directamente las columnas autorizadas, incluidos roles, claves y stock: la auditoría cubre operaciones de aplicación, no cambios administrativos externos. Las credenciales SQL no se entregan a los usuarios de caja.

Para una instalación con la cuenta limitada anterior al Plan 3, ejecuta `php database/enable-catalog.php` para simular y `php database/enable-catalog.php --apply` para habilitar únicamente la escritura del catálogo en la cuenta configurada. Requiere las credenciales administrativas locales de `.env.migrations`; no cambia contraseñas, registros ni existencias. Las cuentas nuevas creadas con `create-app-user.php` ya reciben estos permisos.

En esta instalación local la cuenta `tecno_star_app` ya está activa. El comando rechaza una cuenta existente en lugar de restablecer su contraseña o cambiar sus permisos.

### Habilitar el inventario del Plan 4

`php database/enable-inventory.php` simula la actualización de la instalación local configurada. `--apply` ejecuta las migraciones pendientes y concede a la cuenta limitada actual `UPDATE (stock_actual,stock_minimo)` de inventario y `SELECT,INSERT` del historial. Usa `.env.migrations`; no cambia claves, reservas ni existencias. `--verify` confirma lectura del historial y estos permisos acotados. La aplicación requiere la migración 003 y esos permisos antes de usar los nuevos formularios.

La pantalla `/admin/inventory` permite entradas y salidas relativas, ajuste absoluto a conteo físico y configuración del mínimo. Toda operación exige motivo (3–500 caracteres) y un administrador activo comprobado en BDD; conserva su identificador y nombre en el movimiento. Admite hasta tres decimales, calculados como milésimas enteras para evitar errores de coma flotante. No admite stock negativo ni físico menor que reservado. El máximo es 999999999.999. El mínimo se compara con el disponible, incluyendo la igualdad.

En bases antiguas sin `schema_migrations`, `InventoryUpgrade` utiliza una actualización específica: valida InnoDB, identificadores unsigned, stock DECIMAL(12,3), saldos válidos e índice único por producto. No ejecuta 001/002, no adopta el esquema como canónico ni convierte o modifica las tablas antiguas. Crea únicamente `tbstockmovimientos` y su control `inventory_module_migrations`, con checksum, bloqueo y detección de actualización incompleta. Conserva `tbmovimientosinventario` y sus registros sin mezclarlos con saldos reconstruidos. La nueva pantalla empieza su historial en la apertura. El instalador compara los saldos antes/después y el conteo del historial antiguo antes de conceder los permisos. La instalación local quedó habilitada con autorización explícita del usuario el 17 de octubre de 2026.

La fila se bloquea con `FOR UPDATE` y stock/historial se confirman juntos. Un ajuste físico incluye el saldo visto al abrir el formulario y se rechaza si cambió. Una clave aleatoria de solicitud evita duplicar un movimiento al reintentar o enviar simultáneamente; cambiar los datos de una solicitud ya guardada se rechaza. Ante respuesta incierta, reintenta desde el mismo formulario. Las reservas se crean/liberan desde el flujo de pedidos del Plan 6; el formulario de inventario no permite editarlas directamente.

El historial utiliza DataTables con filtros por producto, tipo, responsable, fechas y texto, orden SQL y páginas de hasta 100 filas. El saldo inicial identifica al sistema, sin atribuirlo a una persona. Los estilos y scripts son `admin-inventory.css` y `admin-inventory.js`; altas/cambios en modal y confirmaciones por toast.

Pruebas aisladas (nombres nuevos en cada ejecución):

```powershell
& C:\xampp\php\php.exe tests/catalog.php --database=tecno_star_test_catalog_inv01 --host=127.0.0.1 --port=13308 --user=root --inventory
& C:\xampp\php\php.exe tests/inventory-upgrade.php --database=tecno_star_test_inv_upgrade01 --host=127.0.0.1 --port=13308 --user=root
```

El primer comando incorpora pruebas HTTP del inventario, transacciones fallidas, concurrencia entre procesos, reintentos idénticos, ajuste obsoleto, límites, reservas, permisos y filtros/XSS. El segundo migra una instalación sintética del Plan 3 y verifica saldos y apertura repetible. Nunca reutilizan una base existente ni modifican registros de la tienda.

## Historial, reintentos y fallos

### Habilitar el carrito del Plan 5

`php database/enable-shopping.php` simula la actualización de la instalación configurada. `--apply` agrega los campos y el diario del Plan 5, comprueba que las cantidades se conserven y concede SELECT/INSERT solo de `tbshoppingoperaciones` a la cuenta limitada existente. No modifica contraseñas, usuarios, stock ni reservas. `--verify` comprueba los campos y que el diario no tenga permisos UPDATE/DELETE.

La instalación local quedó habilitada el 18 de octubre de 2026 tras autorización explícita del usuario. La verificación incluye leer el estado de carrito y favoritos mediante el modelo y la cuenta SQL limitada, sin modificar sus datos.

En instalaciones canónicas se aplica la migración 004 con `MigrationRunner`. En la instalación antigua, `ShoppingUpgrade` verifica InnoDB, cantidades enteras compatibles e índice único de carrito por usuario o usuario/estado. Mantiene las tablas anteriores y registra únicamente esta ampliación en `shopping_module_migrations`, con checksum, bloqueo y detección de aplicación incompleta. No adopta el esquema antiguo como si tuviera las migraciones 001–003.

Las solicitudes confirmadas se conservan en el diario. Su clave es única y su huella incluye la cuenta y el contenido; reutilizarla con otros datos se rechaza. El bloqueo del carrito serializa modificaciones de la misma cuenta; las revisiones evitan sobrescribir cambios de otra pestaña. Carrito, favoritos, revisión y diario se confirman o revierten juntos. La API requiere además identidad destino y CSRF actuales. El estado se devuelve con `Cache-Control: no-store`.

Para comprobar la actualización sin tocar datos reales: `php tests/shopping-upgrade.php --database=tecno_star_test_shop_upgrade06 --host=127.0.0.1 --port=13308 --user=root`. Crea bases nuevas canónica y antigua, las actualiza dos veces y verifica cantidades, stock y precios de referencia. El resto de pruebas del Plan 5 se documenta en el README principal.

### Comportamiento del ejecutor general

- `schema_migrations` registra versión, checksum SHA-256, estado y fecha de aplicación.
- Un bloqueo de MariaDB/MySQL evita que dos ejecutores migren simultáneamente la misma base.
- Repetir el comando omite las migraciones aplicadas.
- Si cambia o desaparece una migración registrada, el proceso se detiene. Las migraciones publicadas deben conservarse; agrega un archivo nuevo con un número superior.
- Una migración empieza como `applying` y termina como `applied` únicamente cuando todas sus sentencias se ejecutan.
- MySQL/MariaDB confirma DDL de forma implícita: no existe rollback automático del esquema. Ante un fallo, puede haber tablas parciales y la herramienta bloquea el siguiente intento. Inspecciona el error y el esquema; restaura un respaldo o, si era una base nueva desechable, utiliza otra base vacía. No elimines el historial para forzar la repetición.
- No se ofrecen comandos de borrado, reinicio ni migración hacia atrás.

Los archivos SQL admiten sentencias simples separadas por punto y coma y comentarios de línea `--`. No uses procedimientos, `DELIMITER` ni puntos y coma dentro de literales con este ejecutor.

## Pruebas de integración

Con una instancia MariaDB de prueba disponible:

```powershell
& C:\xampp\php\php.exe tests/database.php --database=tecno_star_test_local01 --host=127.0.0.1 --port=13308 --user=root
```

Adapta el puerto al servicio aislado que estés utilizando. El script no inicia un servidor. Usa las mismas opciones de conexión y variable de contraseña que las migraciones.

El nombre debe comenzar por `tecno_star_test_`, seguido de hasta 25 letras, números o guiones bajos. Deben estar disponibles ese nombre y sus variantes `_legacy` y `_failed`: el script rechaza bases ya existentes, crea tres bases nuevas y las conserva para inspección. Para repetirlo, utiliza otro sufijo.

Las pruebas comprueban instalación y repetición de migraciones, roles, registro con hash de contraseña, perfil, restricciones de integridad, catálogo, repetición de ejemplos, carrito, favoritos, consultas administrativas, protección de bases antiguas, checksums y recuperación bloqueada después de un fallo DDL. El caso de fallo genera intencionalmente una migración parcial en la base terminada en `_failed`.

También comprueban la revalidación de roles, la invalidación de cuentas desactivadas y los permisos de una cuenta SQL temporal. La cuenta de prueba se elimina al finalizar, incluso ante errores.

Para comprobar formularios, peticiones JSON, sesiones y revocación de permisos mediante HTTP, utiliza la base de prueba ya migrada y con los ejemplos cargados:

```powershell
& C:\xampp\php\php.exe tests/security-http.php --database=tecno_star_test_local01 --host=127.0.0.1 --port=13308 --user=root
```

Esta prueba crea una cuenta SQL temporal, inicia PHP en `127.0.0.1:18014`, registra un cliente de prueba, cambia su rol y estado y comprueba CSRF y logout. Usa exclusivamente una base de prueba con el prefijo indicado, conserva sus registros para inspección y elimina el usuario SQL temporal y el servidor HTTP al finalizar. Comprueba además que, si se revoca el permiso de lectura para validar cuentas, el acceso falla con 503. No utiliza cuentas ni datos de la instalación de trabajo.

También comprueba el límite de acceso compartido entre sesiones, el rechazo de una cabecera de proxy falsificada, la expiración por inactividad y duración máxima, la renovación de identificador y la correlación de referencias de error con registros privados sin credenciales. Utiliza directorios temporales propios para almacenamiento y sesiones; modifica únicamente sus sesiones de prueba para simular el paso del tiempo, sin esperar plazos reales. Esos archivos temporales se conservan para diagnóstico.

Para mantenimiento del almacenamiento local, `php database/maintenance.php` muestra una simulación y `php database/maintenance.php --prune` elimina exclusivamente contadores vencidos y logs fuera de retención. No requiere conexión SQL. Consulta la política y las pruebas sin MySQL en [README.md](../README.md).

El directorio `database/` y el directorio `tests/` están bloqueados por el `.htaccess` de la raíz. Los comandos PHP también requieren ejecución CLI. No se agregaron rutas web de instalación.
