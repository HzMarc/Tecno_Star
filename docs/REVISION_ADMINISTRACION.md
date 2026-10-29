# Revisión de administración

Fecha: 19 de octubre de 2026.

**Seguimiento pendiente:** esta revisión registró ajustes visuales y pruebas de la base existente. Pedidos con pendientes y confirmados, tarjetas del cliente y Caja con escaneo, pago y reinversión se organizan en [PLAN_CIERRE_ADMINISTRACION.md](../PLAN_CIERRE_ADMINISTRACION.md); todavía no están completados con ese alcance.

Revisión de navegación, jerarquía visual, redundancias y acciones administrativas. Las escrituras de comprobación utilizaron bases aisladas con cuentas sintéticas, sin operar pedidos, cuentas ni saldos del negocio.

## Cambios completados

| Área | Estado | Resultado |
| --- | --- | --- |
| Navegación | [x] | Volver al inicio en las listas, a Productos desde el editor, a Pedidos desde su detalle y a Ventas desde el comprobante. Acceso a la tienda en el menú de cuenta. |
| Catálogo | [x] | Un título principal, descripción breve y acción de crear en el encabezado. Retirados los segundos títulos y los números técnicos «Registro #» de categorías y marcas. Se mantienen modales y toasts. |
| Inventario | [x] | Sin encabezado ni contador duplicados. Botón Limpiar. Historial plegable, abierto y filtrado desde la fila del producto, con enlace para volver a existencias. |
| Devoluciones | [x] | Filtro añadido tanto en la vista como en la validación del servidor. Antes no se podía seleccionar este tipo de movimiento. |
| Pedidos | [x] | Buscador QR compacto y textos dirigidos al personal. Pedidos cerrados sin reintento de correo QR. Cobrar y entregar tiene prioridad visual frente a cambiar estado. |
| Ventas | [x] | Dos importes relevantes, una aclaración de filtros y sin la tarjeta constante de método de pago. Acceso a buscar el pedido para cobrar. |
| Usuarios | [x] | Sin enlace irrelevante a Pedidos. Crear usuario en el encabezado. Permiso de caja visible y habilitado solo al elegir Empleado; la lista distingue acceso completo, permiso del empleado y permiso no aplicable al cliente. |
| Perfil | [x] | Regreso al panel y accesos a Pedidos, Ventas y Productos para el administrador. El cliente conserva sus accesos personales. |
| Resumen | [x] | Tarjetas enlazadas a sus registros; pendientes y ventas pagadas con estado seleccionado. Estados legibles y stock sin redondear decimales. Gráficas ajustadas al móvil. |
| Controles | [x] | Limpiar elimina también el estado seleccionado al llegar desde el resumen. Contraste azul/blanco en hover y foco. Botones de modales legibles y apilados en móvil. |

Los estilos compartidos están separados en `public/assets/css/admin-workspace.css`. Se conserva el sidebar con iconos en escritorio y menú superpuesto en móvil, los formularios en modales, los avisos toast y las tablas DataTables.

## Comprobaciones

- [x] Revisión visual en escritorio y a 360 y 320 px: navegación, catálogo, usuarios, pedidos, comprobante, resumen y modales.
- [x] Crear una categoría sintética y editar una cuenta sintética, con persistencia y toast.
- [x] Cambiar el rol del formulario: permiso de pedidos/caja visible para Empleado y oculto/deshabilitado para Cliente y Administrador.
- [x] Historial abierto desde un producto, limpieza de filtros y consulta exclusiva de devoluciones.
- [x] Pedido sintético abierto por código y modal de efectivo revisado, sin registrar un cobro manual.
- [x] Pedido cerrado sin reenvío; regreso desde el comprobante y desde el perfil administrativo.
- [x] Filtros iniciales desde el resumen y limpieza de estado; menú móvil cerrado con Escape.
- [x] Panel a 320 px: contenido de 305 px, igual al ancho disponible descontando la barra de desplazamiento.
- [x] Regresión funcional aprobada de catálogo, inventario, carrito, pedidos, ventas, cuentas y permisos del cliente, incluida la nueva prueba de filtro de devoluciones.
- [x] Validación sintáctica de los 116 archivos PHP; configuración y pruebas JavaScript aprobadas.

Comandos de verificación final en MariaDB temporal:

```powershell
C:\xampp\php\php.exe tests/catalog.php --database=tecno_star_test_catalog_admin03 --host=127.0.0.1 --port=13308 --user=root --inventory --shopping --orders --accounts --customer
C:\xampp\php\php.exe tests/configuration.php
node tests/accounts-client.cjs
node tests/order-scanner.cjs
node tests/shopping-client.cjs
```

Para repetir, utiliza un servidor aislado y un nombre nuevo de base. La vista interactiva utilizó `tecno_star_test_catalog_admin02` con `--preview`; las operaciones manuales quedaron únicamente en esa base sintética. Los servidores y la pestaña temporales se cerraron al terminar.

Esta revisión no marca como completado el Plan 8 ni sustituye las comprobaciones de publicación, respaldos, dispositivos físicos o recepción real del correo.

El cierre operativo posterior ya implementa Caja, bandejas y reinversión: [resultados de implementación](CIERRE_ADMINISTRACION.md). La activación SQL real se completó con autorización expresa, copia previa y permisos mínimos verificados; sigue pendiente la prueba de los dispositivos físicos.
