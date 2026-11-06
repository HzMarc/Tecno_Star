# Plan de cierre: pedidos, caja, pago y reinversión

Fecha: 19 de octubre de 2026.

**Estado: flujo principal implementado, probado y activado en la instalación local.** El usuario autorizó expresamente la activación de reinversión y sus permisos financieros. Se creó la copia previa y se verificaron la habilitación de Caja, los cálculos y los permisos mínimos. La migración 008 del aviso de retiro también está activada y verificada con copia previa, datos conservados y permisos sin cambios. Quedan pendientes la prueba física del lector/cámara y la recepción del nuevo diseño de correo en el buzón real.

Resultados y recorrido: [docs/CIERRE_ADMINISTRACION.md](docs/CIERRE_ADMINISTRACION.md).

## Lo que falta y la regla recuperada

Existe una base de pedidos, reserva de stock, cobro en efectivo, comprobantes y anulación. Se organizó el recorrido del personal y se integró la **reinversión de las ventas**. Antes de esta implementación, `SaleModel::pay()` no registraba reinversiones y faltaba su migración. Ahora el registro forma parte de la transacción del cobro y existe la migración 007.

La referencia recuperada aparece en el chat **«Diagramas DFD Cliente Tecno Star»**, en el bloque `tbReinversiones`. Coincide con la tabla del [SQL original](C:/Users/marco/Downloads/tecno_star.sql:358): 50 % para reposición, 5 % para crecimiento y un registro por venta. Los `.md` actuales del proyecto no recogían este requisito; se incorpora aquí expresamente.

**Reinversión** es la distribución interna del importe de una venta cobrada. Se mantendrá separada de la anulación y el reembolso de una venta.

## Organización que tendrá el sistema

| Lugar | Para qué sirve | Acciones principales |
| --- | --- | --- |
| Pedidos → Pendientes | Revisar pedidos que todavía no han sido aceptados. | Ver pedido, aceptar o cancelar con motivo. |
| Pedidos → Confirmados | Preparar pedidos aceptados y consultar los listos para retirar. | Iniciar preparación, marcar listo o ir a Caja. |
| Pedidos → Historial | Consultar pedidos entregados y cancelados. | Ver seguimiento, motivo y comprobante cuando exista. |
| Detalle del pedido | Ver productos, contacto, reserva y seguimiento. | La siguiente acción válida y acceso a Caja para cobrar. |
| Caja | Reunir escaneo, búsqueda de pedido y todo el trámite de pago en efectivo. | Cargar pedido, revisar productos, introducir recibido, comprobar cambio, confirmar pago y entregar comprobante. |
| Ventas | Consultar operaciones cobradas y comprobantes. | Buscar venta, ver detalle y anular si corresponde. |
| Ventas → Reinversión | Consultar reposición y crecimiento acumulados. | Filtrar por fecha y abrir la venta de origen. Solo administrador. |
| Cliente → Mis pedidos | Consultar pedidos propios mediante tarjetas y paginación. | Ver detalle, seguimiento y cancelar cuando corresponda. |

**Caja tendrá su propio acceso en la navegación administrativa.** El escaneo estará integrado allí y se elimina la propuesta de un apartado independiente «Escanear pedido». Pedidos se dedicará a la aceptación, preparación y seguimiento. Reinversión permanecerá dentro de Ventas.

Caja debe entregarse con el cobro en efectivo funcional, conectado al pedido, la entrega, la venta, el inventario y la reinversión; no se considerará completada con una pantalla o un lector QR solamente.

## Plan 1 · Definir reglas, pantallas y datos

- [x] Documentar el recorrido del personal y la navegación Pedidos → Caja → Ventas, diferenciando preparación, cobro, entrega y reinversión.
- [x] Revisar en modo lectura el esquema instalado: presencia y estructura de `tbreinversiones`, registros anteriores y relación única con cada venta.
- [x] Contrastar la instalación antigua con una instalación nueva y definir una migración que conserve datos y operaciones existentes.
- [x] Fijar la base de cálculo, los porcentajes históricos y el redondeo conforme a la regla recuperada.
- [x] Preparar una matriz de permisos: administrador, empleado autorizado y cliente.

**Terminado cuando:** estén definidos el mapa de pantallas, las reglas monetarias y la adaptación de datos, antes de modificar el cobro.

## Plan 2 · Reorganizar Pedidos

### Administración: pendientes y confirmados

- [x] Retirar el bloque de escaneo de Pedidos y dirigir el trámite de pago al nuevo apartado Caja.
- [x] Separar la bandeja en «Pendientes» (`pendiente`) y «Confirmados» (`confirmado`, `en_preparacion`, `listo`), con conteos reales y filtros de avance dentro de los confirmados.
- [x] Consultar entregados y cancelados desde «Historial», manteniéndolos fuera de las bandejas de trabajo activo.
- [x] Mantener DataTables en las bandejas administrativas de Pedidos, con búsqueda, filtros y conteos procedentes de la base de datos y respetando la bandeja seleccionada.
- [x] Mostrar código, contacto, fecha, vencimiento, total y estado; los datos secundarios se consultarán en el detalle.
- [x] Diferenciar el estado de preparación del estado del cobro: un pedido listo todavía puede estar sin cobrar.
- [x] Sustituir el cambio genérico de estado por la acción siguiente válida, con cancelación claramente separada.
- [x] Organizar el detalle en productos y total, contacto y reserva, seguimiento y acciones. Mantener «Volver a pedidos» y confirmaciones mediante modal/toast.
- [x] Tras aceptar un pedido, moverlo a Confirmados; al cobrar y entregar desde Caja, moverlo a Historial y actualizar conteos al volver a la lista.
- [x] Cambio solicitado: «Listo para retirar» prepara un aviso por correo y vuelve al detalle del pedido. No dirige automáticamente a Caja, incluso si la preparación se inició allí. Plantillas con logo y pruebas terminadas; la activación de la cola real se detalla en el plan general.

### Cliente: tarjetas de pedidos

- [x] Sustituir DataTables en «Mis pedidos» por tarjetas propias, sin cabeceras, selector de filas ni controles de tabla.
- [x] Aplicar también tarjetas a «Mis compras»: cuatro por página, dos columnas en escritorio y una en móvil, filtros por comprobante/pedido, estado y fechas, totales filtrados y acceso al comprobante. Probados privacidad, reembolsos, búsquedas y paginación desde SQL.
- [x] Mostrar **cuatro pedidos por página**, ordenados del más reciente al más antiguo: dos columnas en escritorio y una columna en móvil. Las cuatro tarjetas formarán una cuadrícula de dos por dos cuando el ancho lo permita.
- [x] Paginar desde el servidor y ofrecer Anterior/Siguiente e indicador de página, evitando descargar todo el historial para mostrar cuatro pedidos.
- [x] Mostrar en cada tarjeta código, fecha, resumen breve de productos, total y estado; incluir vencimiento únicamente mientras exista una reserva activa.
- [x] Ofrecer «Ver pedido» como acción principal y la cancelación únicamente en los estados permitidos. Mantener fuera de esta vista las acciones de preparación, escaneo, caja y reinversión.
- [x] Incorporar filtros sencillos «Todos», «En curso» y «Finalizados», junto con estados vacíos claros, sin duplicar controles de la administración.
- [x] Conservar la consulta exclusiva de pedidos propios, incluidos conteos y páginas, y comprobar que el estado refleje la preparación y entrega registradas por el personal.

### Acciones previstas por estado

| Estado del pedido | Acción principal | Acciones secundarias |
| --- | --- | --- |
| Pendiente | Aceptar pedido | Cancelar con motivo. |
| Confirmado | Iniciar preparación | Cancelar con motivo. |
| En preparación | Marcar listo para retirar | Cancelar si corresponde. |
| Listo para retirar | Ir a Caja | Consultar datos o cancelar sin cobrar. |
| Entregado | Ver comprobante | Consultar seguimiento. |
| Cancelado o vencido | Consultar motivo | Volver a pedidos. |

El vencimiento se identificará como motivo de cancelación; no se inventará un nuevo estado en la base. Las acciones deben conservar las validaciones de reserva y permisos del servidor.

**Terminado cuando:** el personal identifique pendientes y confirmados, derive el cobro a Caja y el cliente consulte sus pedidos en tarjetas paginadas y adaptadas al dispositivo.

## Plan 3 · Crear Caja con escaneo integrado

- [x] Crear Caja como apartado propio, por ejemplo `/admin/cash`, con acceso para el administrador y el empleado con permiso de caja. Incorporar su enlace a la navegación del personal autorizado.
- [x] Integrar en Caja el lector USB, la cámara y la entrada manual de código, con controles claros para iniciar y detener la cámara.
- [x] Permitir llegar desde «Ir a Caja» en un pedido listo, con sus datos ya cargados, o buscarlo directamente desde Caja mediante el código o QR.
- [x] Al cargar el pedido, mostrar contacto, productos, cantidades, precios, total y situación del cobro en la misma pantalla para continuar el pago allí.
- [x] Conservar los QR enviados anteriormente: `/orders/scan` seguirá comprobando permisos; para el personal autorizado abrirá el pedido en Caja y para el cliente conservará el acceso permitido a su propio pedido.
- [x] Mostrar errores y bloqueos dentro de Caja: código inválido, pedido inexistente, reserva vencida, preparación incompleta, cobro anterior o acceso denegado. Un pedido ya cobrado ofrecerá su comprobante y uno cerrado mostrará su motivo.
- [x] Evitar cobros automáticos por escanear; detener la cámara al salir y comprobar foco, teclado, móvil y lecturas repetidas. Añadir «Volver a pedidos» y «Nueva operación».

**Terminado cuando:** Caja permita localizar y revisar el pedido por cualquier medio, dé paso al pago sin saltar entre pantallas y no exista un apartado independiente de escaneo. Su cierre funcional depende del Plan 4.

## Plan 4 · Completar el pago funcional dentro de Caja

- [x] Hacer disponible «Confirmar cobro y entrega» dentro de Caja solo para pedidos listos, con reserva vigente y sin cobro anterior; explicar los casos en los que no se puede cobrar.
- [x] Integrar el formulario de pago en Caja junto al resumen cargado: número de pedido, contacto, productos, total, efectivo recibido y cambio. El detalle de Pedidos ofrecerá «Ir a Caja», sin duplicar allí el formulario de cobro.
- [x] Validar efectivo insuficiente y calcular el cambio en el servidor con importes exactos, conservando la vista previa del navegador.
- [x] Revisar importes en Caja y pedir confirmación final mediante modal antes de registrar cobro y entrega; cancelar la confirmación no registrará operaciones.
- [x] Conservar una misma solicitud en los reintentos y ofrecer comprobación del resultado cuando se pierda la respuesta, sin duplicar cobros.
- [x] Mostrar el resultado del pago y su comprobante dentro del recorrido de Caja, con accesos a la venta, al pedido y a «Nueva operación» para atender al siguiente cliente.
- [x] Integrar la reinversión del Plan 5 dentro de la misma operación transaccional del cobro, entrega, stock e historial.
- [x] Completar la preparación del pedido escaneado dentro de Caja, mostrar «Efectivo recibido» al quedar listo y guardar mediante «Registrar pago» → «Confirmar pago y entrega». Verificado en HTTP y navegador: efectivo, cambio, venta, entrega y reinversión; aparición inmediata en Ventas y recarga del estado al conservar la misma URL con fragmento.

**Terminado cuando:** Caja registre de principio a fin el efectivo recibido, el cambio, la venta, la entrega, el inventario y su historial, y permita recuperar un resultado incierto sin duplicar operaciones. El cobro definitivo no se considerará cerrado hasta integrar la reinversión.

## Plan 5 · Implementar la reinversión de las ventas

### Regla de cálculo

La base es el **total de la venta guardado al cobrar**, obtenido de los precios y cantidades históricos del pedido. No se utiliza el efectivo entregado por el cliente ni el precio de compra del producto; tampoco se incrementa el precio del catálogo para aplicar esta distribución.

| Concepto | Porcentaje inicial | Venta de $100.00 |
| --- | ---: | ---: |
| Reposición | 50 % | $50.00 |
| Crecimiento | 5 % | $5.00 |
| Reinversión total | 55 % | $55.00 |
| Importe restante | 45 % | $45.00 |

El importe restante no representa por sí solo la ganancia neta: esta regla no incorpora todos los costos y gastos del negocio. La distribución es un registro interno, no una transferencia bancaria ni una compra automática de mercancía.

Se conservará el redondeo del esquema original:

```text
reposición = redondear(total_venta × porcentaje_reposición / 100, 2)
reinversión_total = redondear(total_venta × (porcentaje_reposición + porcentaje_crecimiento) / 100, 2)
crecimiento = reinversión_total − reposición
```

Por ejemplo, con una venta de **$24.99**: reposición **$12.50**, crecimiento **$1.24** y reinversión total **$13.74**. El crecimiento se obtiene por diferencia para que los importes sumen exactamente el total reinvertido. Si se reciben $50.00, la base continúa siendo $24.99 y el cambio es $25.01.

### Tareas

- [x] Crear o adaptar `tbreinversiones` sin duplicar una tabla existente ni sobrescribir su historial, conservando la relación única `id_venta`.
- [x] Guardar monto de venta, porcentajes aplicados, importes de reposición y crecimiento, total reinvertido, fecha y vínculo al comprobante.
- [x] Registrar una sola reinversión por venta al confirmar el cobro. Si falla su registro, revertir también el cobro y la entrega.
- [x] Mantener los porcentajes guardados por venta para que un cambio futuro de configuración no altere operaciones pasadas.
- [x] Crear el apartado administrativo de reinversión: resumen del período, desglose y DataTables con acceso a la venta de origen.
- [x] Mostrar el desglose únicamente al administrador, sin exponerlo en pantallas ni respuestas destinadas al cliente o al empleado de caja.
- [x] Integrar la anulación ya existente: conservar el registro original y su motivo de anulación; excluir las ventas anuladas de los acumulados vigentes de reinversión, sin borrar su historial ni volver a sumar dinero.
- [x] Preparar y probar migración, compatibilidad y permisos SQL mínimos. Los registros antiguos que carezcan de reinversión se identificarán para revisión; no se generarán repartos históricos automáticamente sin comprobar su base.

**Terminado cuando:** cada venta nueva pagada tenga exactamente una reinversión y los totales coincidan entre comprobante administrativo, listado y resumen del período, también después de una anulación.

## Plan 6 · Pruebas y cierre real del módulo

- [x] Probar el recorrido cliente → tarjetas de pedidos → bandeja Pendientes → aceptación → bandeja Confirmados → preparación → listo → Caja con escaneo o selección del pedido → pago → entrega → reinversión.
- [x] Verificar el traslado entre Pendientes, Confirmados e Historial y que los conteos/filtros correspondan a sus estados reales.
- [x] Comprobar tarjetas del cliente con 0, 1, 4, 5 y más pedidos, filtros y paginación desde el servidor; ninguna página, tarjeta ni conteo puede mostrar pedidos ajenos.
- [x] Probar pago insuficiente, reserva vencida, pedido cancelado y pedido ya cobrado, sin operaciones parciales.
- [x] Probar doble clic, reintentos, pérdida de respuesta y dos empleados cobrando simultáneamente: una venta y una reinversión por pedido.
- [x] Probar importes de $0.00 cuando el catálogo lo permita, $0.01, $0.10, $24.99 y $100.00, con sumas exactas y sin discrepancias de centavos.
- [x] Provocar fallos de reinversión e historial y comprobar que no quedan ventas, entregas o movimientos parciales.
- [x] Probar anulación, reembolso y devolución física: historial conservado y acumulados de reinversión sin contar la venta anulada.
- [x] Verificar roles por interfaz y acceso directo: el empleado autorizado cobra, el administrador consulta reinversión y el cliente accede únicamente a sus pedidos y compras.
- [ ] Completar la prueba física del lector USB y la cámara del negocio. Escritorio, móvil, teclado, navegación, estados vacíos y errores ya están comprobados; el lector y la cámara tienen pruebas automáticas con dispositivos simulados.
- [x] Realizar una revisión operativa con los recorridos anteriores, registrar resultados y marcar cada tarea con su check únicamente cuando esté comprobada.

**Criterio final:** el personal gestiona pendientes y confirmados en Pedidos, completa escaneo y pago funcional en Caja y consulta ventas y reinversión donde corresponda; el cliente ve sus pedidos en tarjetas. Los datos y permisos deben conservarse en todo el recorrido.

## Activación verificada en la instalación real

- [x] Activación autorizada expresamente por el usuario: copia SQL previa completa, adopción de `tbreinversiones` y permisos exclusivamente SELECT/INSERT para la cuenta SQL actual. Pasó `database/enable-reinvestment.php --verify` y la aplicación reconoce Caja habilitada. El rechazo automático anterior quedó resuelto con esta aprobación; se conservaron cuentas, claves, ventas, pedidos y stock anteriores, sin conceder UPDATE/DELETE sobre reinversión.

La tabla instalada conserva la fórmula original y no se reconstruyeron ventas históricas. La copia previa terminó sin errores y se verificaron los importes de prueba mediante consultas sin crear operaciones reales. Caja ya permite continuar el cobro de pedidos válidos con los controles del servidor.

## Orden de trabajo

**1. Reglas y datos → 2. Pedidos administrativos y tarjetas del cliente → 3. Caja con escaneo → 4. Pago funcional en Caja → 5. Reinversión integrada → 6. Pruebas y cierre.**

La activación SQL local está completada. Antes de publicar quedan la comprobación de los dispositivos físicos y las demás tareas del Plan 8. El código, los recorridos y las pruebas aisladas ya están implementados.
