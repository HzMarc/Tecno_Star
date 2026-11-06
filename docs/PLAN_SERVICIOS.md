# Plan de Servicios de Tecno Star

## Estado de implementación · 31 de octubre de 2026

El módulo está implementado, probado y activado en la base local, con respaldo y permisos SQL limitados. Los ocho servicios están disponibles. El envío real autorizado se verificó en Recibidos de Gmail. El inicio de sesión con Google se comprobó después de corregir un pequeño desfase del reloj. La tabla dañada de preferencias de phpMyAdmin se reparó con autorización, respaldo y verificación de conservación de todos los datos del negocio. MySQL reinició normalmente.

Los planes 1 a 7 corresponden al módulo de tarjetas, solicitudes y atención manual. La preparación de fotografías y stickers se realiza manualmente por el personal.

## Diagnóstico de partida

Los documentos describen a Tecno Star como una tienda de accesorios tecnológicos y servicios de papelería. La interfaz actual solo presenta los servicios como una sección informativa en la página de inicio y como enlaces desde el catálogo y el footer (`/#servicios`). No existe todavía un módulo de servicios con catálogo propio, solicitud del cliente, agenda, cotización, seguimiento, cobro o administración.

El flujo de pedidos, Caja, Ventas, correo y reinversión está diseñado para productos retirados en tienda. Por eso un servicio no debe mezclarse automáticamente con el stock físico ni con el flujo de reserva de productos.

## Objetivo

Crear un apartado de Servicios claro y administrable donde el cliente pueda conocer los servicios disponibles, solicitar uno, recibir una cotización o confirmación y consultar su estado; el personal podrá atenderlo, registrar el importe y mantener su historial.

## Plan 1 — Definir el catálogo de servicios

- [x] Inventariar los servicios reales que ofrece la tienda: copias, impresiones, escaneos, digitación, anillado, plastificado y otros que el negocio confirme.
- [x] Para cada servicio definir nombre, descripción, categoría, imagen opcional, unidad de cobro, precio base o “requiere cotización”, tiempo estimado y estado.
- [x] Separar servicios informativos de servicios que pueden solicitarse desde la web.
- [x] Confirmar horarios, condiciones, archivos aceptados, límites de tamaño y política de entrega.

## Plan 2 — Diseñar la base de datos

- [x] Crear tablas para servicios, categorías, solicitudes, archivos, estados, mensajes y cobros.
- [x] Mantener importes `DECIMAL`, historial de cambios, responsable y fechas en UTC.
- [x] No usar `tbproductos`, reservas ni movimientos de inventario para un servicio salvo que una solicitud consuma un producto y esa regla se defina expresamente.
- [x] Añadir migración repetible, checksum, copia previa y permisos SQL mínimos para la cuenta de la aplicación.

## Plan 3 — Crear la experiencia del cliente

- [x] Añadir una página `/services` accesible desde el navbar, footer y catálogo.
- [x] Mostrar tarjetas filtrables por categoría, con precio o indicador “cotizar”, tiempo estimado y acción principal.
- [x] Crear el formulario de solicitud con servicio, descripción, cantidad, fecha preferida, contacto y archivo cuando corresponda.
- [x] Validar tamaño, extensión y nombre de archivos; guardar fuera de la carpeta pública y bloquear ejecución.
- [x] Mostrar una confirmación clara y una sección “Mis solicitudes” en tarjetas, igual que “Mis compras”.

## Plan 4 — Flujo de atención administrativa

- [x] Añadir Servicios al sidebar administrativo con bandejas: nuevas, en cotización, aceptadas, en proceso, listas, entregadas y canceladas.
- [x] Usar DataTables para la bandeja administrativa y modales para crear, editar, cotizar y cambiar estado.
- [x] Registrar responsable, motivo, notas y cada transición en un historial inmutable.
- [x] Permitir que administrador y empleados con permiso atiendan solicitudes; el cliente solo consulta las propias.

## Plan 5 — Cotización, agenda y comunicación

- [x] Permitir precio fijo o cotización manual con vencimiento.
- [x] Permitir aceptar o rechazar la cotización desde el cliente y registrar la decisión.
- [x] Añadir fecha/turno de entrega únicamente cuando la tienda confirme disponibilidad.
- [x] Crear correos con el logo para solicitud recibida, cotización, cambio de estado y servicio listo.
- [x] Mantener la cola de correos con reintentos, sin enviar adjuntos innecesarios ni QR de pedidos de productos.

## Plan 6 — Cobro y contabilidad

- [x] Definir si el servicio se paga al solicitar, al aceptar la cotización o al entregar.
- [x] Extender Caja para cobrar servicios en efectivo, con recibido, cambio, responsable y comprobante.
- [x] Registrar la operación en Ventas diferenciando `producto` y `servicio`.
- [x] Definir si existe reinversión para servicios y qué costos se registran; no aplicar reglas de inventario sin autorización.
- [x] Permitir anulación solo con motivo, reembolso explícito y auditoría, igual que el flujo actual de ventas.

## Plan 7 — Seguridad, pruebas y puesta en marcha

- [x] Activación en la base real con respaldo completo, conservación de datos y verificación con la cuenta limitada.
- [x] Probar aislamiento entre clientes, CSRF, permisos por rol, XSS, archivos maliciosos, solicitudes duplicadas y concurrencia.
- [x] Probar recorridos completos: cliente → solicitud → cotización → aceptación → preparación → cobro → entrega.
- [x] Verificar entrega SMTP real: mensaje autorizado recibido en Gmail. Cola, visualización móvil, vencimientos y cancelaciones probados de forma aislada.
- [x] Documentar instalación, migración, respaldo, recuperación y operación diaria.

## Decisiones aplicadas

1. Ocho servicios: copias, impresiones, fotografías, stickers, escaneos, digitación, anillado y plastificado; sin tarifas inventadas. Catálogo, categorías y precios orientativos administrables.
2. PDF, JPG, PNG, WebP, DOCX y XLSX privados: cinco archivos de hasta 10 MiB cada uno y 30 MiB por envío. Word y Excel se revisan manualmente. Archivos de resultado y limpieza por retención.
3. El cliente acepta la cotización y paga en efectivo al retirar; la cotización dura siete días. Cobro y entrega en una transacción, con reintentos idempotentes.
4. Fecha deseada y fecha estimada confirmada por el personal; sin agenda automática ni horarios inventados.
5. Permiso específico de servicios para empleados, revalidado en cada operación. Administración del catálogo y reembolsos reservados al administrador.
6. Comprobante interno de servicio integrado en Ventas y Mis compras. Sin movimientos de inventario ni reinversión de productos.
7. Mensajes entre cliente y personal registrados en el historial; archivos accesibles solo al propietario o al personal autorizado.
8. Botón **Regresar** únicamente en los formularios de solicitud, con destino al catálogo de tarjetas. Cantidad y fecha deseada alineadas y de la misma altura en los ocho servicios.
9. **Limpiar** junto a los filtros del catálogo y de las solicitudes, tanto del cliente como del personal.
10. Formulario sin selector **Entrega del material**: el requisito de originales proviene del catálogo. Para servicios digitales se solicitan los archivos necesarios. La recepción de originales se conserva en la atención del personal cuando corresponde.
11. Administración con navegación entre solicitudes y catálogo/precios, estados con conteos, búsqueda única y tabla compacta. Cada trabajo muestra el siguiente paso y acciones con nombres explícitos. El contenido respeta el espacio del menú lateral.
12. Al cerrar un modal con el ratón se retira el foco del botón que lo abrió. La navegación con teclado conserva el foco y su indicador visible.

## Comprobación y activación

La suite de catálogo con `--inventory --shopping --orders --accounts --customer --closure --purchase-cards --ready-mail --services` verificó la regresión completa en una base aislada. Las pruebas de actualización comprobaron la conservación de cuentas, pedidos y ventas, con y sin historial global. Se revisaron catálogo, formularios, tarifas y mensajes en escritorio y móvil de 360 px.

`database/enable-services.php` simula la activación; `--apply` respalda antes de migrar y conceder permisos limitados; `--verify` comprueba el esquema con la cuenta de la aplicación. `database/process-services.php` procesa correos y limpia archivos. La guía de operación vigente está en el README.

La tabla comercial heredada `tbservicios` se conserva. El módulo usa `tbserviciocatalogo` para evitar conflictos con el esquema anterior.

Los avisos se intentan enviar después de cada operación; los reintentos y la retención se ejecutan cada minuto mediante la tarea local de Windows, con permisos limitados y sin ventanas visibles.

## Cierre final · 31 de octubre de 2026

- Planes 1 a 7 completados para esta versión y documentación de operación actualizada en el README, sin referencias a planes en ese archivo.
- Regresión completa ejecutada después de los últimos ajustes: **522 comprobaciones correctas**, con correo real desactivado y datos sintéticos en una instancia independiente de MariaDB, puerto 13309. Registro privado: `storage/services/cierre-suite.log`.
- Actualización repetible comprobada en dos bases sintéticas: con historial global y con esquema heredado sin ese historial. Se conservaron cuentas, pedidos, ventas y referencias de servicios antiguos.
- Activación local verificada con la cuenta SQL de la aplicación: ocho servicios activos. Tarea de mantenimiento instalada y comprobada.
- Revisión visual previa de los ocho formularios: fecha y cantidad alineadas; **Regresar** solo en el formulario; selector de entrega del material retirado. **Limpiar** restablece búsqueda/categoría en tarjetas y búsqueda/estado en solicitudes.
- No se crearon solicitudes ni ventas sintéticas en la tienda real durante este cierre. No se enviaron correos adicionales de prueba.

El primer intento de preparar la suite en el servidor local se detuvo al crear su cuenta SQL temporal con `The table 'global_priv' is full`; la comprobación rápida de esa tabla devolvió `OK`. La suite se completó en la instancia de pruebas independiente. No se repararon tablas de privilegios ni se reinició el servidor de la tienda para esta validación. Esta incidencia de aprovisionamiento SQL es distinta de las cuentas de clientes de la aplicación.

### Revisión de usabilidad administrativa

Se corrigió la regla que eliminaba el espacio del menú lateral y se verificaron el menú expandido, contraído y la vista móvil. Se retiró la búsqueda duplicada de DataTables. La bandeja tiene accesos por estado con conteos, una búsqueda por cliente/código/servicio, filtros con **Limpiar**, estados identificables y una acción **Abrir** por trabajo. En móvil las filas se presentan como tarjetas, sin desplazar horizontalmente la página. Los errores al consultar la tabla permiten reintentar.

El detalle indica el siguiente paso según el estado y la recepción de originales. Las acciones muestran su propósito; cancelar abre directamente la acción de cancelación. Los modales devuelven el foco visible al usar teclado y retiran el foco residual al cerrarlos con el ratón. Se verificaron cierre con el botón y Escape, edición del catálogo, filtros y ausencia de desbordamiento en móvil. La suite de catálogo y servicios completó **127 comprobaciones** en la instancia aislada; no se modificaron trabajos reales para esta revisión.
