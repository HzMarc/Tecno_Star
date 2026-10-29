# Plan de animaciones para Tecno Star

## Objetivo

Mejorar la percepción de velocidad y claridad de Tecno Star usando librerías existentes, sin crear un sistema de animaciones propio. Las animaciones deben ayudar a entender estados, acciones y cambios de pantalla sin distraer al cliente ni dificultar el trabajo administrativo.

## Librerías propuestas

| Librería | Uso dentro de Tecno Star |
|---|---|
| **Boneyard** | Skeleton loading para productos, pedidos, tablas y paneles mientras se cargan datos. |
| **GSAP** | Transiciones de cards, sidebar, carrito, modales y cambios de estado que necesiten control preciso. |
| **AOS** | Animaciones de secciones al entrar en pantalla, principalmente inicio y servicios. |
| **Animate.css** | Efectos sencillos y puntuales como fade, zoom o slide. |
| **Lottie** | Estados especiales: pedido creado, pago completado, correo enviado y estados vacíos. |
| **Swiper.js** | Carruseles de novedades, productos destacados y servicios. |
| **SweetAlert2** | Confirmaciones y avisos animados para operaciones importantes. |

Boneyard está orientado principalmente a frameworks como React, Vue, Svelte y Angular. Por eso se utilizará solo si se puede integrar de forma compatible con las vistas actuales o en un componente frontend aislado. No se debe introducir React únicamente para usar skeletons.

## Plan 1 — Preparación y dependencias

- [ ] Revisar el uso actual de Bootstrap, Bootstrap Icons y scripts propios.
- [ ] Definir si las librerías se cargarán por Composer no aplica; se instalarán por npm o mediante archivos distribuidos en `public/assets/vendor`.
- [ ] Fijar versiones para evitar cambios inesperados.
- [ ] Cargar cada librería solo en las vistas que la necesiten.
- [ ] Crear una página de prueba para comprobar rendimiento, compatibilidad móvil y conflictos con Bootstrap.

## Plan 2 — Skeleton loading con Boneyard

- [ ] Probar Boneyard en una vista aislada del catálogo.
- [ ] Determinar si funciona correctamente con HTML/PHP generado en servidor.
- [ ] Si no es compatible directamente, usar Boneyard en un componente frontend independiente o descartarlo para esa pantalla.
- [ ] Generar skeletons para cards de productos, pedidos del cliente, ventas, inventario y tablas administrativas.
- [ ] Usar una duración mínima para evitar parpadeos en consultas rápidas.
- [ ] Mostrar un estado vacío diferente cuando la consulta termina sin resultados.

## Plan 3 — Animaciones del cliente

- [ ] Usar GSAP para entrada y salida del carrito lateral, lista de deseos y modales.
- [ ] Animar las cards de productos al cargar y al añadir un artículo.
- [ ] Usar Swiper para novedades, productos destacados y servicios.
- [ ] Usar AOS en la página de inicio y el futuro apartado de servicios.
- [ ] Usar Lottie para pedido creado, pago confirmado, correo enviado y catálogo vacío.
- [ ] Animar cambios de cantidad y actualización del total sin mover bruscamente el contenido.
- [ ] Mantener las animaciones de hover breves y consistentes.

## Plan 4 — Animaciones del área administrativa

- [ ] Animar el sidebar colapsable con GSAP.
- [ ] Animar apertura y cierre de modales de productos, categorías, marcas e inventario.
- [ ] Mostrar skeletons al filtrar DataTables o cargar información de un registro.
- [ ] Animar los cambios de estado de pedidos: pendiente, confirmado, preparación, listo y entregado.
- [ ] Usar SweetAlert2 para confirmaciones de cambios, cobros, anulaciones y devoluciones.
- [ ] Animar los indicadores de Caja y Ventas sin ocultar los importes ni retrasar el uso del sistema.

## Plan 5 — Estados, errores y notificaciones

- [ ] Sustituir mensajes visualmente bruscos por notificaciones animadas con SweetAlert2 o la librería elegida.
- [ ] Añadir una animación de proceso al confirmar un pedido: el botón debe mostrar un spinner y el texto «Confirmando pedido…», bloquear envíos duplicados y conservar la pantalla estable mientras responde el servidor.
- [ ] Mostrar una animación de éxito cuando el pedido se cree correctamente, con el código del pedido, el estado de la reserva y el enlace para consultar el detalle.
- [ ] Mostrar una animación de error si la confirmación falla, explicar el siguiente paso y permitir reintentar sin duplicar el pedido.
- [ ] Aplicar el mismo patrón de carga a login, registro, recuperación de contraseña, filtros, carrito, favoritos, actualización de cantidades, Caja, cobros y cambios de estado administrativos.
- [ ] En operaciones largas, mostrar primero skeletons o un estado de progreso y después reemplazarlos por el contenido real.
- [ ] Mostrar una animación breve cuando se guarda un producto, categoría, marca o ajuste de inventario.
- [ ] Mostrar estados de carga en botones para impedir doble envío.
- [ ] Mostrar una animación de error clara cuando falla una solicitud.
- [ ] Mantener los mensajes accesibles mediante `role="status"` y `aria-live`.

## Plan 6 — Rendimiento y accesibilidad

- [ ] Respetar `prefers-reduced-motion` desactivando o reduciendo los efectos.
- [ ] Evitar animar propiedades costosas como `width`, `height`, `top` o `left` cuando pueda usarse `transform` y `opacity`.
- [ ] No bloquear botones mientras una animación decorativa continúa.
- [ ] Bloquear únicamente el control que inició una operación real, como confirmar pedido o registrar pago, y desbloquearlo si la respuesta falla.
- [ ] No utilizar vídeos o Lottie pesados en cada card.
- [ ] Cargar scripts con `defer` y dividirlos por pantalla.
- [ ] Comprobar móvil, escritorio y navegadores principales.

## Plan 7 — Pruebas y validación

- [ ] Comprobar que una animación no impida abrir, cerrar o enviar formularios.
- [ ] Verificar carrito, favoritos, pedidos, Caja, cobro y modales con conexiones lentas.
- [ ] Comprobar que el estado final siempre coincida con la base de datos aunque la animación se interrumpa.
- [ ] Probar teclado, lector de pantalla y reducción de movimiento.
- [ ] Medir el peso adicional de cada librería y retirar cualquier dependencia que no aporte valor.
- [ ] Documentar qué vista utiliza cada librería y cómo actualizar sus versiones.

## Orden de implementación recomendado

1. SweetAlert2 para confirmaciones y avisos.
2. GSAP para carrito, modales y sidebar.
3. Swiper para carruseles.
4. AOS para inicio y servicios.
5. Lottie para estados especiales.
6. Boneyard para skeleton loading, después de confirmar su compatibilidad con las vistas PHP.
7. Pruebas de rendimiento y accesibilidad.

## Criterio de aceptación

El sistema estará listo cuando las animaciones mejoren la comprensión de cada acción, no bloqueen ningún flujo, funcionen en móvil, respeten la reducción de movimiento y cada operación siga mostrando el resultado real guardado en la base de datos.
