# Recorrido del cliente en Tecno Star

## Propósito

Esta guía explica las páginas y acciones disponibles para una persona que visita Tecno Star como cliente: desde conocer la tienda y crear una cuenta hasta guardar productos, reservarlos, consultar compras y solicitar servicios.

La tienda distingue tres momentos:

1. **Elegir productos:** explorar el catálogo, revisar existencias y guardar artículos en el carrito o en Favoritos.
2. **Hacer un pedido:** confirmar los productos y datos de retiro. El pedido reserva existencias durante un plazo definido.
3. **Completar la compra:** retirar y pagar en efectivo en la tienda. El pago genera el comprobante que aparece en **Mis compras**.

Los servicios de papelería siguen un flujo aparte: se solicita el trabajo, se recibe y acepta una cotización, se prepara y se paga en efectivo al retirarlo.

## 1. Landing page o página de inicio

**Ruta:** `/`

Es la entrada pública del sitio. Presenta la propuesta de Tecno Star y ofrece accesos al catálogo y al catálogo de servicios. La página incluye:

- Presentación de la tienda y sus productos tecnológicos y de papelería.
- Categorías destacadas, como accesorios móviles, audio, conectividad y papelería.
- Una explicación breve del proceso: consultar, realizar un pedido y retirar en tienda.
- Información de la tienda y su ubicación en Ilobasco, Cabañas.
- Enlaces para crear una cuenta, iniciar sesión y seguir explorando.

No se necesita una cuenta para visitar la página de inicio.

## 2. Registro de cliente

**Ruta:** `/register`

El formulario crea una cuenta de cliente con nombres, apellidos, teléfono, correo y contraseña confirmada. El servidor comprueba los campos, el formato del correo y teléfono, que el correo no esté registrado y que ambas contraseñas coincidan. Después de crear la cuenta, el cliente puede iniciar sesión.

También existe una opción de acceso con Google. Si el proveedor solicita completar información adicional, la página de finalización de Google permite hacerlo antes de entrar a la tienda.

## 3. Inicio de sesión

**Ruta:** `/login`

Permite entrar con correo y contraseña o, cuando está configurado, con Google. La pantalla incluye la opción para mostrar la contraseña, recuperar el acceso y regresar al registro o a la página de inicio.

Al iniciar sesión, el sistema conserva la cuenta activa para consultar pedidos, compras, servicios y artículos guardados. Si el cliente abrió el inicio de sesión desde un enlace de retiro, puede continuar al pedido correspondiente después de autenticarse.

## 4. Catálogo de productos

**Ruta:** `/store`

El catálogo muestra productos activos y su disponibilidad. El cliente puede:

- Filtrar por categoría.
- Ordenar por más recientes, menor precio, mayor precio o nombre.
- Ver marcas, precios, imágenes y existencias disponibles.
- Identificar productos agotados o con pocas unidades.
- Abrir el detalle de un producto, añadirlo al carrito o guardarlo en Favoritos.

La tienda también muestra productos recientes cuando hay suficientes elementos en el catálogo. Si el catálogo no puede cargarse o no tiene productos, la página informa el problema y ofrece volver a intentar o consultar los servicios.

## 5. Detalle de producto

**Ruta:** `/product?id=<id>`

La ficha amplía la información de un producto seleccionado:

- Nombre, marca, categoría, imagen, descripción y precio por unidad.
- Existencias disponibles y aviso cuando está agotado.
- Selector de cantidad, limitado por las existencias.
- Acción para agregar al carrito y botón para guardarlo en Favoritos.
- Información de retiro en la tienda y enlace para consultar la ubicación.

No se puede agregar al carrito un producto sin existencias. Los importes y las existencias se vuelven a revisar al preparar el pedido.

## 6. Carrito de compras

**Acceso:** botón del carrito en la barra de navegación del catálogo y del detalle del producto.

El carrito se abre como un panel lateral; no es una página separada. Permite revisar artículos, cambiar cantidades, quitar productos y consultar un total estimado. Antes de continuar, muestra avisos si un artículo dejó de estar disponible, si la cantidad supera las existencias o si cambió el precio.

El cliente debe revisar los precios actuales cuando el sistema detecta un cambio. En el siguiente paso también se vuelve a validar el catálogo para evitar reservar productos o precios desactualizados.

- **Sin iniciar sesión:** el carrito se guarda temporalmente en el navegador.
- **Con sesión iniciada:** el carrito se guarda asociado a la cuenta.
- Al iniciar sesión, los artículos guardados como visitante se pueden sincronizar con la cuenta.
- Para continuar desde el carrito hasta la confirmación del pedido es necesario iniciar sesión.

## 7. Lista de deseos o Favoritos

**Acceso:** botón de corazón o botón **Favoritos** en la barra de navegación.

Favoritos es otro panel lateral. Sirve para conservar productos que el cliente quiere revisar después; guardar un producto no lo reserva ni garantiza que siga disponible.

Para visitantes, la lista se conserva en el navegador. Al iniciar sesión, los favoritos se sincronizan con la cuenta. Desde la sesión iniciada quedan asociados al cliente y se pueden consultar al volver a la tienda.

## 8. Confirmación y creación del pedido

**Ruta:** `/checkout`

La confirmación se inicia desde el carrito y requiere una cuenta. Antes de reservar, el cliente revisa:

- Productos, cantidades, precios vigentes y total a pagar en tienda.
- Nombre de quien retirará el pedido.
- Teléfono y correo de contacto.
- Una nota opcional para la tienda.
- La información del plazo de retiro y la aceptación de las condiciones.

Al confirmar, se crea el pedido y se reserva el inventario durante **24 horas**. El pago no se realiza en línea: se paga en efectivo al retirar. El cliente debe esperar a que el estado indique **Listo para retirar** antes de acudir.

## 9. Mis pedidos

**Ruta:** `/orders`

Muestra las reservas de productos asociadas a la cuenta. Se pueden separar en **Todos**, **En curso** y **Finalizados**; cada tarjeta muestra código, productos, estado, total y, mientras siga activa, la fecha límite de retiro. La lista incluye paginación.

La página de detalle, `/orders/detail?id=<id>`, muestra los artículos reservados, los datos de retiro, la fecha de vencimiento y el historial de estados. Los estados habituales son:

- **Pendiente:** pedido recibido.
- **Confirmado:** la tienda confirmó la reserva.
- **En preparación:** están preparando los productos.
- **Listo para retirar:** el cliente ya puede acudir a la tienda.
- **Entregado:** se completó el retiro y el cobro.
- **Cancelado:** se liberó la reserva.

El cliente puede cancelar un pedido mientras esté pendiente o confirmado; la aplicación no permite al cliente cancelarlo desde esta acción cuando ya está en preparación. Si vence el plazo de 24 horas, la reserva se cancela y las existencias se liberan. En el detalle también se conserva el seguimiento del pedido.

## 10. Mis compras

**Ruta:** `/purchases`

Esta sección guarda los comprobantes de operaciones ya cobradas. Por eso es distinta de **Mis pedidos**: un pedido puede seguir reservado o en preparación, mientras que una compra aparece después de completar el pago en tienda.

El cliente puede buscar por código de comprobante o pedido, filtrar por estado y por rango de fechas, y abrir cada comprobante. La lista muestra fecha, código de venta, origen (pedido o servicio), método de pago, total y estado. Los estados visibles son **Pagada** y **Reembolsada**.

El detalle `/sales/detail?id=<id>` muestra el comprobante, los productos o el servicio, el total, el efectivo recibido y el cambio. Cuando corresponde, también enlaza al pedido relacionado.

## 11. Catálogo y solicitudes de servicios

- **Catálogo público:** `/services`
- **Solicitar un servicio:** `/services/request?id=<id>`
- **Mis solicitudes:** `/services/requests`
- **Detalle:** `/services/detail?id=<id>`

El catálogo presenta servicios de papelería con descripción, categoría, unidad de cobro, precio orientativo cuando existe y tiempo estimado. El cliente puede buscar por nombre o descripción y filtrar por categoría. Los precios mostrados son orientativos; la tienda confirma el importe y la fecha en una cotización.

Para solicitar un servicio se necesita iniciar sesión. El formulario recoge cantidad, fecha deseada, instrucciones, teléfono y, según el trabajo, opciones como tamaño de papel, impresión a color o número de caras. Se pueden adjuntar hasta **5 archivos**, de **10 MiB cada uno** y **30 MiB en total**, en PDF, JPG, PNG, WebP, Word o Excel. Algunos trabajos requieren llevar originales físicos a la tienda.

En **Mis solicitudes** el cliente puede consultar código, servicio, cantidad, estado, fecha e importe aceptado. El flujo de seguimiento es:

1. **Nueva:** la solicitud fue recibida.
2. **En cotización:** la tienda revisa los detalles.
3. **Cotizada:** el cliente puede revisar y aceptar o rechazar el precio. La cotización vence después de siete días.
4. **Aceptada:** el cliente aprobó el precio; la tienda puede iniciar el trabajo.
5. **En proceso:** el trabajo se está preparando.
6. **Lista para retirar:** el cliente puede coordinar el retiro.
7. **Entregada** o **Cancelada:** el servicio se cerró.

El detalle conserva la cotización, los archivos relacionados, los mensajes y el historial. El cliente puede enviar mensajes y cancelar la solicitud antes de que el trabajo entre en preparación. Los adjuntos son privados y quedan disponibles hasta 30 días después del cierre; el cliente puede descargar los archivos autorizados desde su solicitud. El pago se realiza en efectivo al retirar. Rechazar una cotización cancela esa solicitud.

## 12. Perfil y accesos de cuenta

**Ruta:** `/profile`

El perfil presenta el nombre, correo y teléfono registrados. Cuando las opciones de cuenta están habilitadas, permite editar nombres, apellidos y teléfono; para guardar cambios solicita la contraseña actual. El correo aparece como dato de la cuenta, pero no se modifica desde ese formulario. También permite cambiar la contraseña mediante la contraseña actual, una nueva contraseña y su confirmación; al completar el cambio, se debe iniciar sesión de nuevo. La nueva contraseña debe tener al menos 12 caracteres.

La página ofrece accesos directos a **Mis pedidos**, **Mis compras**, el catálogo y Favoritos, además de cerrar sesión.

El menú de cuenta en la tienda incluye asimismo **Mis solicitudes** para que el cliente vuelva a sus servicios en curso.

## Diferencia entre pedidos, compras y servicios

| Sección | Qué representa | Cuándo se cierra |
| --- | --- | --- |
| **Mis pedidos** | Productos reservados para retiro. | Al cancelar o al completar retiro y cobro. |
| **Mis compras** | Comprobantes de pagos ya completados, de productos o servicios. | Se conserva como registro de pagada o reembolsada. |
| **Mis solicitudes** | Trabajos de papelería que requieren instrucciones y, normalmente, cotización. | Al entregar el trabajo o cancelar la solicitud. |

## Rutas y archivos principales

Las rutas se registran en `routes/web.php`. La lógica de cada flujo está en sus controladores y modelos; las plantillas están en `app/Views/` y la interacción del navegador en `public/assets/js/`.

- Inicio y tienda: `IndexController.php`, `StoreController.php`; vistas `app/Views/index.php` y `app/Views/store/`.
- Autenticación y perfil: `AuthController.php`, `AccountController.php`; vistas `app/Views/auth/`.
- Carrito y Favoritos: `ShoppingController.php`, `ShoppingModel.php`; componentes de tienda y `public/assets/js/shopping-client.js`.
- Pedidos y compras: `OrderController.php`, `OrderModel.php`, `SaleController.php`, `SaleModel.php`; vistas `app/Views/orders/` y `app/Views/commerce/purchases.php`.
- Servicios: `ServiceController.php`, `ServiceModel.php`; vista `app/Views/services/page.php`.
- Catálogo y producto: `ProductController.php`, `ProductModel.php`; vistas `app/Views/store/index.php` y `app/Views/store/product.php`.

Las páginas y operaciones que contienen datos personales exigen una sesión y limitan las consultas a la cuenta autenticada. Los archivos adjuntos de servicios se almacenan fuera de la carpeta pública.
