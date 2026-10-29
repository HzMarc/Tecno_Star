# Estado del proyecto · 19 de octubre de 2026

**El flujo principal está implementado y activado en local; el proyecto aún no está cerrado para publicación.** Los planes 1–7 tienen implementación y pruebas. El Plan 8 conserva tareas abiertas y hay comprobaciones externas pendientes. El aviso de retiro también está activado: el usuario autorizó expresamente la migración 008 y se verificaron la copia previa, la conservación de datos y los permisos sin cambios.

## Implementado

| Área | Estado |
| --- | --- |
| Tienda, detalle, búsqueda, carrito y favoritos | Implementados y probados. |
| Acceso, cuentas, roles y recuperación | Implementados; Google OAuth activado en localhost el 22 de octubre de 2026. |
| Productos, categorías, marcas e inventario | Administración con modales, toasts y tablas; movimientos con historial. |
| Pedidos y reservas | Bandejas administrativas, tarjetas propias del cliente, estados, cancelación y vencimiento. |
| QR y Gmail | Bibliotecas instaladas; envío de confirmación y lectura probados. Gmail aceptó un envío real anterior. |
| Caja y ventas | Efectivo recibido, cambio, comprobante, entrega, stock, reintentos y anulación. |
| Mis compras del cliente | Tarjetas de cuatro por página, filtros y totales propios; acceso al comprobante y estados pagada/reembolsada. Sin DataTables ni acciones administrativas. |
| Reinversión | Distribución 50 % reposición + 5 % crecimiento, ligada a la venta; activada con permisos mínimos. |
| Nuevos correos | Plantilla con logo incrustado, QR, productos, total y retiro; recuperación con el mismo diseño. Pruebas sin envíos reales. |
| Aviso de «Listo para retirar» | Implementado, probado y activado con la migración 008. Vuelve al detalle del pedido, sin dirigir a Caja. |

La activación guardó el respaldo en `storage/backups/ready-mail-20261020-043951-a97912c7/database.sql`. Conservó los envíos anteriores y no generó ni reenvió correos históricos. `database/enable-ready-mail.php --verify` confirmó la habilitación con la cuenta SQL de la aplicación.

## Qué falta para cerrar

1. **Verificación real del correo:** comprobar en el buzón entrada/spam, logo, QR y enlace de seguimiento. Un envío aceptado por SMTP no demuestra por sí solo la recepción ni el aspecto en Gmail/Outlook. Los correos nuevos se probaron con transporte simulado y vistas de navegador.
2. **Dispositivos y dirección de acceso:** probar lector USB y cámara reales, y configurar/comprobar la dirección HTTPS o de red del negocio. Los enlaces con `localhost` no abren la computadora desde un celular ajeno.
3. **Publicación segura:** dominio/HTTPS, configuración de PHP y sesiones de producción y validación final del recorrido y roles en el servidor de destino.
4. **Respaldos restaurables:** automatizar copias de BDD e imágenes y realizar una restauración de prueba. Las copias puntuales previas a migraciones ya existen; eso no sustituye un respaldo operativo periódico.
5. **Ejecución programada:** configurar en el entorno de operación la liberación de reservas y los reintentos de correo (`expire-orders.php --run` y `send-order-mails.php --run`). Actualmente también se procesan vencimientos durante visitas/operaciones, pero no se instaló una tarea programada.
6. **Cierre del Plan 8:** comprobar rendimiento de imágenes/consultas con mediciones y completar los procedimientos de despliegue, actualización, recuperación y mantenimiento.

Los cinco primeros checks del Plan 8 son una validación final del recorrido, permisos, concurrencia, reintentos y móvil. Existen pruebas aisladas de estas funciones en los planes anteriores; falta cerrar su comprobación en la instalación y dispositivos de destino antes de marcar la publicación como terminada.

## Ampliaciones opcionales

Google OAuth se implementó posteriormente, el 22 de octubre de 2026, y está activado en localhost: cliente web creado en TectoStar, credenciales privadas, callback validado y prueba real completada con sesión abierta en Mi perfil. Sigue en modo Prueba; producción requiere dominio HTTPS y configuración de publicación. Consulta la guía Gmail/Google. Promociones, pagos en línea y reportes comerciales adicionales permanecen como segunda etapa.

## Fuentes revisadas

- [Plan general](../PLAN_DE_MEJORAS.md), especialmente Plan 8 y segunda etapa.
- [Plan de cierre administrativo](../PLAN_CIERRE_ADMINISTRACION.md).
- [Guía Gmail/Google](GUIA_GMAIL_GOOGLE.md).
- [Resultados de cierre](CIERRE_ADMINISTRACION.md).
- [Revisión del cliente](REVISION_CLIENTE_PLANES_1_A_7.md) y [administración](REVISION_ADMINISTRACION.md).

El índice Graphify existente es anterior a estos planes y no encontró información de su estado; las conclusiones se basan en los documentos actuales y en las pruebas de esta implementación.
