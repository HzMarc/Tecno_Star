# Revisión del cliente · Planes 1 a 7

Fecha: 19 de octubre de 2026.

Revisión del recorrido del cliente, sus permisos y la presentación en escritorio y móvil. Las escrituras de comprobación se realizaron en bases aisladas con cuentas sintéticas y correo simulado. No se modificaron pedidos del negocio ni se enviaron correos durante esta revisión.

## Comprobaciones completadas

| Plan | Estado | Comprobación y ajustes |
| --- | --- | --- |
| 1 · Tienda | [x] | Catálogo, detalle, filtros, navegación y estados vacíos. Favoritos abre desde el perfil; se eliminaron avisos y descripciones duplicados en el carrito. Menú móvil y cierre de paneles alineados. |
| 2 · Base y seguridad | [x] | Registro, acceso, CSRF, sesiones, separación entre cuentas y SQL limitado. Cliente bloqueado en consultas y escrituras administrativas, incluso con token CSRF válido. |
| 3 · Catálogo | [x] | Catálogo y detalle públicos coherentes con productos, categorías y marcas activas; sin controles de edición del administrador. Regresión administrativa de catálogo aprobada. |
| 4 · Inventario | [x] | Disponibilidad calculada con stock físico menos reservas, productos agotados ocultos y retorno al reponer. Regresión de movimientos y permisos aprobada. |
| 5 · Carrito y favoritos | [x] | Persistencia, cantidades, precios, reintentos y separación entre usuarios. Favoritos compactos y mensajes temporales; productos inactivos no se pueden añadir. |
| 6 · Pedidos | [x] | Confirmación, reserva, estados, cancelación, vencimiento e historial. El cliente cancela sin selector administrativo de estados; pedidos cerrados no muestran instrucciones de retiro pendientes. Generación y lectura de QR y correo MIME probados automáticamente. |
| 7 · Compras y cuenta | [x] | Compras propias con seis columnas y lenguaje del cliente. Comprobante sin anulación ni mensajes internos de stock. Perfil, edición, contraseña y recuperación probados; iniciales acentuadas y teléfono vacío corregidos. Regresión de efectivo, cambio y devolución aprobada. |

Se ocultó el acceso con Google porque OAuth sigue pendiente. Inicio reconoce una sesión activa y ofrece acceder a la cuenta y tienda. El registro valida el teléfono salvadoreño y conserva los campos públicos tras un error, sin conservar contraseñas.

## Presentación y hover

- [x] Revisión visual en escritorio de 1440 px y móvil de 360 y 320 px: formularios, menú, catálogo, favoritos, carrito, checkout, pedidos, compras, comprobante y perfil.
- [x] Formularios de acceso visibles directamente en móvil; tablas con desplazamiento contenido y modales con cierre centrado.
- [x] Fondo azul con texto blanco en botones, filtros y paginación; mismo contraste al enfocar los controles correspondientes con teclado.
- [x] Comprobación de colores calculados en el navegador: filtro «Limpiar», texto `#fff` sobre `#2054dd`; paginación de DataTables, texto `#fff` sobre `#1a56db`.
- [x] Excepciones con fondo claro conservan texto oscuro; acciones de eliminación y salida conservan su tratamiento rojo.
- [x] CSS de componentes separado y recursos con versión de modificación para evitar estilos antiguos en caché.

## Pruebas ejecutadas

Todas las siguientes finalizaron correctamente con PHP de XAMPP y MariaDB temporal en `127.0.0.1:13308`:

```powershell
C:\xampp\php\php.exe tests/catalog.php --database=tecno_star_test_catalog_audit02 --host=127.0.0.1 --port=13308 --user=root --inventory --shopping --orders --accounts --customer
C:\xampp\php\php.exe tests/storefront-run.php --database=tecno_star_test_store_audit01 --host=127.0.0.1 --port=13308 --user=root
C:\xampp\php\php.exe tests/database.php --database=tecno_star_test_db_audit01 --host=127.0.0.1 --port=13308 --user=root
C:\xampp\php\php.exe tests/security-http.php --database=tecno_star_test_db_audit01 --host=127.0.0.1 --port=13308 --user=root
C:\xampp\php\php.exe tests/orders-upgrade.php --database=tecno_star_test_order_audit01 --host=127.0.0.1 --port=13308 --user=root
C:\xampp\php\php.exe tests/sales-accounts-upgrade.php --database=tecno_star_test_sales_audit01 --host=127.0.0.1 --port=13308 --user=root
C:\xampp\php\php.exe tests/configuration.php
node tests/shopping-client.cjs
node tests/accounts-client.cjs
node tests/order-scanner.cjs
```

También pasaron la validación sintáctica de los 117 archivos PHP de aplicación, configuración, base, núcleo y pruebas, y `node --check` de los scripts modificados. Las pruebas de actualización verificaron esquema nuevo y antiguo.

Para repetir, levanta un servidor de pruebas separado y cambia los nombres de base por nombres nuevos. `security-http.php` requiere primero los datos preparados por `tests/database.php`; una base del ensayo de catálogo no es su fixture. `--customer` requiere `--orders --accounts` y agrega `tests/customer-checks.php` a la regresión general.

## Pendientes externos

- Comprobar cámara y lector USB físicos y la dirección HTTPS/red que usará el negocio.
- Comprobar recepción del QR en el buzón real; la prueba automática de esta revisión utilizó transporte simulado.
- OAuth implementado el 22 de octubre de 2026 para localhost; la publicación con dominio HTTPS continúa pendiente. Consulta `GUIA_GMAIL_GOOGLE.md`.
- Completar el Plan 8: despliegue, HTTPS, respaldos, restauración y validación final de producción.

Esta revisión prioriza al cliente. Los ajustes y comprobaciones administrativos se documentan por separado en [Revisión de administración](REVISION_ADMINISTRACION.md). La publicación sigue pendiente.
