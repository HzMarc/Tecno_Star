# Plan de Servicios de Tecno Star

## Diagnóstico según la documentación actual

Los documentos describen a Tecno Star como una tienda de accesorios tecnológicos y servicios de papelería. La interfaz actual solo presenta los servicios como una sección informativa en la página de inicio y como enlaces desde el catálogo y el footer (`/#servicios`). No existe todavía un módulo de servicios con catálogo propio, solicitud del cliente, agenda, cotización, seguimiento, cobro o administración.

El flujo de pedidos, Caja, Ventas, correo y reinversión está diseñado para productos retirados en tienda. Por eso un servicio no debe mezclarse automáticamente con el stock físico ni con el flujo de reserva de productos.

## Objetivo

Crear un apartado de Servicios claro y administrable donde el cliente pueda conocer los servicios disponibles, solicitar uno, recibir una cotización o confirmación y consultar su estado; el personal podrá atenderlo, registrar el importe y mantener su historial.

## Plan 1 — Definir el catálogo de servicios

- [ ] Inventariar los servicios reales que ofrece la tienda: copias, impresiones, escaneos, digitación, anillado, plastificado y otros que el negocio confirme.
- [ ] Para cada servicio definir nombre, descripción, categoría, imagen opcional, unidad de cobro, precio base o “requiere cotización”, tiempo estimado y estado.
- [ ] Separar servicios informativos de servicios que pueden solicitarse desde la web.
- [ ] Confirmar horarios, condiciones, archivos aceptados, límites de tamaño y política de entrega.

## Plan 2 — Diseñar la base de datos

- [ ] Crear tablas para servicios, categorías, solicitudes, archivos, estados, mensajes y cobros.
- [ ] Mantener importes `DECIMAL`, historial de cambios, responsable y fechas en UTC.
- [ ] No usar `tbproductos`, reservas ni movimientos de inventario para un servicio salvo que una solicitud consuma un producto y esa regla se defina expresamente.
- [ ] Añadir migración repetible, checksum, copia previa y permisos SQL mínimos para la cuenta de la aplicación.

## Plan 3 — Crear la experiencia del cliente

- [ ] Añadir una página `/services` accesible desde el navbar, footer y catálogo.
- [ ] Mostrar tarjetas filtrables por categoría, con precio o indicador “cotizar”, tiempo estimado y acción principal.
- [ ] Crear el formulario de solicitud con servicio, descripción, cantidad, fecha preferida, contacto y archivo cuando corresponda.
- [ ] Validar tamaño, extensión y nombre de archivos; guardar fuera de la carpeta pública y bloquear ejecución.
- [ ] Mostrar una confirmación clara y una sección “Mis solicitudes” en tarjetas, igual que “Mis compras”.

## Plan 4 — Flujo de atención administrativa

- [ ] Añadir Servicios al sidebar administrativo con bandejas: nuevas, en cotización, aceptadas, en proceso, listas, entregadas y canceladas.
- [ ] Usar DataTables para la bandeja administrativa y modales para crear, editar, cotizar y cambiar estado.
- [ ] Registrar responsable, motivo, notas y cada transición en un historial inmutable.
- [ ] Permitir que administrador y empleados con permiso atiendan solicitudes; el cliente solo consulta las propias.

## Plan 5 — Cotización, agenda y comunicación

- [ ] Permitir precio fijo o cotización manual con vencimiento.
- [ ] Permitir aceptar o rechazar la cotización desde el cliente y registrar la decisión.
- [ ] Añadir fecha/turno de entrega únicamente cuando la tienda confirme disponibilidad.
- [ ] Crear correos con el logo para solicitud recibida, cotización, cambio de estado y servicio listo.
- [ ] Mantener la cola de correos con reintentos, sin enviar adjuntos innecesarios ni QR de pedidos de productos.

## Plan 6 — Cobro y contabilidad

- [ ] Definir si el servicio se paga al solicitar, al aceptar la cotización o al entregar.
- [ ] Extender Caja para cobrar servicios en efectivo, con recibido, cambio, responsable y comprobante.
- [ ] Registrar la operación en Ventas diferenciando `producto` y `servicio`.
- [ ] Definir si existe reinversión para servicios y qué costos se registran; no aplicar reglas de inventario sin autorización.
- [ ] Permitir anulación solo con motivo, reembolso explícito y auditoría, igual que el flujo actual de ventas.

## Plan 7 — Seguridad, pruebas y puesta en marcha

- [ ] Aplicar autorización específica antes de modificar la base real o conceder permisos.
- [ ] Probar aislamiento entre clientes, CSRF, permisos por rol, XSS, archivos maliciosos, solicitudes duplicadas y concurrencia.
- [ ] Probar recorridos completos: cliente → solicitud → cotización → aceptación → preparación → cobro → entrega.
- [ ] Probar correo real y visualización móvil, además de expiración de cotizaciones y solicitudes canceladas.
- [ ] Documentar instalación, migración, respaldo, recuperación y operación diaria.

## Decisiones pendientes antes de implementar

1. Lista exacta de servicios y sus precios iniciales.
2. Si se subirán archivos y cuáles formatos/tamaños se aceptarán.
3. Momento del pago y si todos los servicios se cobran en efectivo.
4. Si habrá agenda con horarios o solo una fecha estimada.
5. Quién puede cotizar, cambiar estados y cobrar.
6. Si los servicios generan factura o comprobante diferente al de productos.

## Orden recomendado

Implementar primero los Planes 1 y 2, revisar la estructura y autorizar la migración; después completar 3 y 4. Los Planes 5 y 6 dependen de las reglas comerciales. El Plan 7 debe ejecutarse antes de habilitar el módulo en producción.
