# Plan de mejoras de Tecno Star

Fecha: 16 de octubre de 2026.

## Objetivo

Completar el flujo de compra con retiro en tienda y proporcionar al administrador las herramientas para gestionar catálogo, inventario, pedidos, ventas y usuarios.

El trabajo se divide en ocho planes. Los planes 1 a 7 tienen una base implementada. El [cierre operativo de administración](PLAN_CIERRE_ADMINISTRACION.md) ya incorpora bandejas de Pedidos, tarjetas del cliente, Caja y reinversión. Las pruebas aisladas y la activación SQL de ese flujo están completadas con autorización, copia previa y permisos mínimos. La nueva cola del aviso de retiro también está activada y verificada; quedan los lectores físicos y la recepción real del correo. El [estado actualizado](docs/ESTADO_PROYECTO_2026_10_19.md) recoge los pendientes del Plan 8 y las ampliaciones opcionales.

## Plan 1: Mejorar el contenido de la tienda para el cliente autenticado

### Alcance

Reorganizar el contenido principal que ve el cliente al iniciar sesión, con el catálogo como protagonista y accesos útiles a sus productos guardados. El trabajo se concentra en la distribución de esa pantalla; la estructura del navbar y del footer se conserva.

### Tareas

- [x] Definir la jerarquía y distribución del contenido después de iniciar sesión, con una bienvenida breve y el catálogo visible sin bloques que lo desplacen innecesariamente.
- [x] Organizar las categorías y los controles de búsqueda, filtros y ordenación para facilitar encontrar productos con las funciones disponibles.
- [x] Mejorar la distribución de las tarjetas, mostrando con claridad imagen, nombre, precio, disponibilidad y acciones de compra.
- [x] Incorporar una sección breve de productos destacados o novedades basada en un criterio real y explícito, evitando repetir todo el catálogo o mostrar promociones inexistentes.
- [x] Facilitar retomar el carrito y consultar favoritos desde los accesos del navbar, conectados a los datos de la cuenta y sin duplicarlos en el contenido principal.
- [x] Definir dónde se integrarán los pedidos y su estado cuando se implemente el Plan 6, manteniendo disponibles únicamente las acciones que ya funcionan.
- [x] Diseñar estados vacíos, sin resultados, sin existencias y de error que orienten al cliente sin recargar la pantalla.
- [x] Ajustar espacios, tamaños, alineación y orden de las secciones para escritorio y móvil.
- [x] Verificar la experiencia con una cuenta autenticada, tanto con carrito y favoritos guardados como sin ellos, conservando su persistencia y separación entre cuentas.
- [x] Revisar el uso con teclado, la legibilidad y el recorrido desde encontrar un producto hasta añadirlo al carrito.

### Criterios de aceptación

- Al iniciar sesión, el cliente distingue rápidamente el catálogo, las categorías y las acciones disponibles.
- La distribución facilita encontrar productos y retomar el carrito o los favoritos sin secciones redundantes.
- Los productos destacados o novedades corresponden a datos reales y no simulan ofertas.
- No se presentan como operativas funciones de pedidos que aún no están implementadas; su ubicación futura queda definida para el Plan 6.
- La pantalla funciona en escritorio y móvil, con estados vacíos y mensajes claros.
- Los cambios visuales conservan la autenticación, CSRF y la persistencia del carrito y favoritos.

**Estado del Plan 1: completado y verificado.**

### Distribución implementada

Bienvenida breve y acceso a la cuenta, categorías laterales en escritorio y botones adaptados al móvil, una franja breve de novedades y el catálogo con disponibilidad y ordenación. El buscador, el carrito y los favoritos se mantienen únicamente en el navbar para evitar duplicación. En móvil se accede a ellos desde el menú de navegación. Las novedades se obtienen de la fecha de registro de productos disponibles: hasta tres, como máximo un tercio del catálogo; se omiten con menos de cuatro productos y al filtrar. Las tarjetas muestran imagen o alternativa, nombre, precio, existencias, corazón y botón de añadir.

**Ubicación futura de pedidos (Plan 6):** el historial y los estados se integrarán en «Mi cuenta». Si existe un pedido activo, podrá mostrarse un aviso breve bajo la bienvenida con su estado real y acceso al detalle. Solo aparecerá al implementar las rutas, permisos y estados de pedidos; esta entrega no muestra botones de pedidos inactivos.

**Ajuste posterior solicitado:** se retiraron los resúmenes del carrito y favoritos del contenido y el segundo buscador. Se conservan los accesos y el buscador del navbar; las comprobaciones iniciales de resúmenes descritas abajo corresponden a la primera versión. Se volvió a verificar la búsqueda del navbar y la apertura de los paneles tras este ajuste.

**Disponibilidad solicitada el 19 de octubre de 2026:** el catálogo público solo incluye productos con al menos una unidad completa disponible (`stock_actual - stock_reservado`). Las cantidades de las tarjetas se obtienen de la BDD al cargar la tienda. Se ocultan agotados y reservas que consumen todo el stock; al reponer o liberar unidades vuelven a aparecer. Se retiró «Solo disponibles» por ser redundante, y categorías, conteos y novedades se calculan sobre los productos mostrados. El inventario administrativo y los productos ya guardados conservan sus registros.

- [x] Verificado por HTTP en `tecno_star_test_store_stock01`: cantidad 10 menos 3 reservadas muestra 7; reserva total o menos de una unidad oculta el producto; reposición lo muestra con 25. Pasaron también separación de carrito/favoritos, CSRF, catálogo vacío, fallo SQL controlado y configuración.

- [x] Añadir al icono de cuenta del navbar un menú con «Mi perfil» y «Cerrar sesión». Verificado con cliente sintético: perfil correcto, salida mediante POST con CSRF y regreso al login, apertura con teclado, cierre con Escape y presentación sin desbordamiento en móvil. El menú se comparte entre catálogo y detalle; sin sesión, el icono conserva el acceso al login. También se comprobó que la imagen del producto abre su detalle; el nombre mantiene su enlace.
- [x] Mejorar el footer por solicitud posterior: compartido entre inicio, tienda y detalle, con catálogo, servicios, ayuda, cuenta, ubicación existente y retiro en tienda. Se revisaron escritorio y móvil de 320 px, enlaces y búsqueda; pasaron sintaxis PHP/JavaScript y pruebas de configuración. No se añadieron horarios ni políticas inexistentes. A petición del usuario se restauró después el diseño anterior del navbar: marca centrada y menú plegable en móvil, conservando el menú de perfil, la búsqueda y el cierre de sesión seguro.
- [x] Adaptar el efecto floral aportado por el usuario a «Black Rose Systems»: firma compacta de 14 px, cuatro rosas pequeñas y tallos que aparecen únicamente en hover, con alternativa al enfocar mediante teclado. Se ocultan al salir y no se reproducen automáticamente. Animación limitada al footer y detenida fuera de pantalla; presentación estática durante la interacción si se prefiere movimiento reducido. Revisado en navegador; se verificó que el estado inicial oculta las rosas y el nombre conserva una altura de 22 px.

- [x] Mejorar la estética del detalle del producto: imagen proporcionada en móvil, precio y stock destacados, controles de compra accesibles, descripción separada de la ficha y ubicación para retiro. Añadida alternativa para imágenes fallidas. Verificados PHP y JavaScript sin errores de sintaxis, distribución a 1280 y 360 px sin desbordamiento horizontal y límites del selector de cantidad (1–25 en el producto revisado).

### Verificación del Plan 1

Completada el 16 de octubre de 2026.

- Navegador con dos clientes desechables: uno con carrito y favoritos y otro vacío. Se comprobó el cambio entre cuentas en el mismo navegador, la persistencia al recargar y la actualización del resumen al añadir productos.
- Búsqueda sin acentos sincronizada con la navegación existente, categorías, filtro de disponibles, orden por precio, limpieza y mensaje sin coincidencias. Recorrido completo desde buscar audífonos hasta añadirlos y consultar el carrito.
- Revisión visual y comprobación de ausencia de desbordamiento horizontal en anchos de 320, 390, 768 y 1440 píxeles. Tarjetas con nombre largo, sin foto, imagen rota y productos agotados.
- Teclado: foco visible, entrada al panel, ciclo con Tab/Shift+Tab, Escape y devolución del foco al botón original. El foco se conserva al actualizar cantidades; los paneles aíslan el contenido de fondo.
- `tests/storefront-run.php`: preparación de una base nueva aislada, ocho productos y dos cuentas; catálogo completo y disponible, novedades, persistencia entre sesiones, separación entre clientes, rechazo de agotados y CSRF, catálogo vacío y fallo SQL controlado con respuesta 503 sin detalles internos. El proceso detiene su servidor y retira su cuenta SQL temporal.
- Pasaron `tests/configuration.php`, `tests/foundation.php`, `tests/security-http.php`, la revisión de sintaxis PHP y `node --check public/assets/js/app.js`. La prueba de rotación se ejecutó con permisos de renombrado en su directorio temporal.
- La base de trabajo no recibió registros ni cambios de carrito de prueba. La estructura del navbar y el footer se conservó.

La recuperación robusta de cambios pendientes ante fallos de red corresponde al Plan 5. Esta pantalla distingue los datos pendientes y permite volver a cargar el estado guardado de la cuenta sin afirmar que un cambio fallido se haya guardado.

**Resultado:** una tienda más clara y útil para el cliente autenticado, con el catálogo como contenido principal.

## Plan 2: Preparar y asegurar la base

### Tareas

- [x] Documentar instalación, requisitos, configuración y estructura del proyecto.
- [x] Incorporar esquema de base de datos, migraciones y datos iniciales.
- [x] Separar credenciales y URL base de la configuración del código.
- [x] Configurar un usuario de base de datos con permisos limitados.
- [x] Centralizar la autenticación y autorización, comprobando que el usuario siga activo y conserve sus permisos.
- [x] Agregar protección CSRF a las operaciones que modifican datos.
- [x] Cambiar el cierre de sesión de GET a POST.
- [x] Incorporar límites de intentos de inicio de sesión.
- [x] Definir vencimiento de sesiones y política de persistencia.
- [x] Centralizar el manejo de errores y los registros, sin mostrar información interna al cliente.

**Estado del Plan 2: completado y verificado.**

### Verificación de la primera tarea

Completada el 16 de octubre de 2026. La guía está en [README.md](README.md).

- Se comprobaron los enlaces locales, los directorios y el puerto documentado contra el proyecto.
- Pasaron la revisión de sintaxis de 27 archivos PHP y de `public/assets/js/app.js`.
- Se verificaron las extensiones requeridas en PHP 8.2.12 de XAMPP.
- Apache reportó `Syntax OK` y el módulo `rewrite_module` habilitado.
- Se ejecutaron de forma aislada mediante PHP las rutas de inicio, login y registro (200), administración sin sesión (403) y estado del carrito sin sesión (401).
- El comando documentado para comprobar MySQL se ejecutó y devolvió su mensaje de fallo controlado. No se confirmó la conexión con la base de datos ni una instalación completa mediante navegador; esas comprobaciones quedan pendientes de un servicio y esquema compatibles accesibles.

Estas comprobaciones validan la documentación de la primera tarea; por sí solas no completan los criterios de aceptación del Plan 2.

### Verificación de la segunda tarea

Completada el 16 de octubre de 2026. El esquema y las herramientas están documentados en [database/README.md](database/README.md).

- Se incorporaron 13 tablas compatibles con las consultas actuales, claves foráneas, índices únicos y restricciones de importes, cantidades y reservas.
- Se añadieron migraciones con historial, checksums, bloqueo de ejecución simultánea y detección de aplicación incompleta.
- Los roles Cliente y Administrador se cargan al migrar; los dos productos de demostración son opcionales. No se crean cuentas con contraseñas predeterminadas.
- Pasaron 29 comprobaciones de integración en MariaDB 10.4.32, utilizando una instancia temporal en el puerto 13308 y bases nuevas exclusivas de prueba.
- Se probaron registro y perfil, catálogo y detalle, carrito y favoritos, consultas y vistas administrativas, restricciones, repetición sin duplicados, conservación de existencias, protección de una base sin historial y fallos DDL registrados.
- Los comandos CLI de migración y ejemplos también se ejecutaron dos veces en otra base nueva, sin duplicados.
- No se modificó la base de trabajo ni su configuración. Una instalación antigua requiere una comparación y una migración específica antes de incorporarse al historial nuevo.

El esquema cubre el comportamiento actual. La confirmación de pedidos, los movimientos de inventario y la gestión de ventas se implementarán en sus planes correspondientes.

### Verificación de la tercera tarea

Completada el 16 de octubre de 2026.

- Las credenciales, el nombre de la aplicación y `APP_URL` se cargan desde variables del servidor o el archivo local `.env`. El entorno tiene prioridad y las variables obligatorias se validan.
- Se agregó `.env.example`, y Git ignora `.env` y sus variantes. El archivo local conserva los valores que utilizaba esta instalación; no se cambiaron cuentas ni datos de la base.
- Las vistas, redirecciones y endpoints del carrito utilizan funciones centrales para construir URL. La cookie de sesión y la resolución de rutas utilizan la ruta derivada de `APP_URL`.
- Se eliminaron los prefijos de carpeta fijos de las vistas y controladores y se adaptaron las reglas de Apache.
- Pasaron las pruebas de `tests/configuration.php`: lectura y prioridad de variables, rechazo de valores inválidos, ejecución aislada de rutas y generación de vistas con base en subcarpeta, raíz y URL HTTPS.
- Pasaron la revisión de sintaxis de 36 archivos PHP, la validación de configuración de Apache y los enlaces locales de la documentación.
- En Apache local, puerto 8012, se verificaron la redirección al inicio público, el formulario de login, la redirección del perfil sin sesión, la ruta de la cookie, la respuesta 403 del administrador y la respuesta 401 del estado del carrito.
- Los accesos HTTP a `.env`, `.env.example`, `.env.local` y `.git/config` devolvieron 403.
- Se comprobó la conexión con los valores externos mediante `SELECT 1`, sin modificar datos de la aplicación.

Las instrucciones actualizadas están en [README.md](README.md). Los avances de las tareas siguientes se registran a continuación.

### Verificación de las tareas cuarta, quinta y sexta

Completadas el 16 de octubre de 2026. Se completó también el cambio de logout a POST como dependencia para proteger con CSRF todas las operaciones actuales que cambian estado.

- Se creó y activó `tecno_star_app` en la instalación local. Tiene una clave aleatoria guardada únicamente en `.env`, con permisos por tabla para las consultas, el registro, el carrito y los favoritos actuales. No se modificaron registros del negocio.
- La cuenta no puede crear o eliminar tablas, consultar `mysql.user`, modificar roles ni ajustar inventario. Las conexiones administrativas de las herramientas CLI quedaron separadas en `.env.migrations`, ignorado por Git y bloqueado por Apache.
- `core/Auth.php` revalida identidad, estado y rol contra la base. No autoriza usando el rol antiguo de la sesión. El perfil, el panel y las operaciones del carrito utilizan las mismas comprobaciones.
- `core/Csrf.php` genera tokens de sesión; los formularios los envían como `_csrf` y el carrito como `X-CSRF-Token`. El punto de entrada rechaza con 403 las peticiones que modifican datos sin un token válido.
- Las pruebas de integración en MariaDB temporal comprobaron registro, catálogo, carrito, favoritos, consultas administrativas, permisos SQL, roles falsificados en sesión, cambios de rol y desactivación.
- Las pruebas HTTP aisladas comprobaron tokens ausentes, incorrectos, de otra sesión y anteriores al login; las peticiones válidas funcionaron con la cuenta SQL limitada.
- La promoción y la revocación se aplicaron en la siguiente petición sin exigir nuevo login. Una cuenta desactivada perdió su identidad; un fallo SQL durante la validación bloqueó el acceso con 503, incluido el formato JSON de la API.
- GET `/logout` devuelve 404 y conserva la sesión; POST sin token se rechaza y POST válido destruye la sesión. Los tres accesos de salida se convirtieron en formularios.
- Se verificó Apache local en el puerto 8012 con la cuenta limitada: inicio, catálogo, formularios, denegación administrativa, CSRF y bloqueo de `.env` y `.env.migrations`.
- Pasaron la revisión de sintaxis de 41 archivos PHP, la de JavaScript y la validación de configuración de Apache.

Las herramientas y pruebas están documentadas en [database/README.md](database/README.md). Las últimas tareas se verificaron como se detalla a continuación.

### Verificación de las tres tareas finales

Completadas el 16 de octubre de 2026.

- El acceso permite por defecto cinco intentos por combinación de correo e IP y treinta por IP en quince minutos. Los contadores se comparten entre sesiones y procesos mediante bloqueos de archivos; una nueva sesión o una cabecera de proxy no reinicia el límite. Se responde con 429 y `Retry-After`.
- El servidor aplica vencimiento por treinta minutos de inactividad y doce horas de duración máxima. La cookie es de navegador por defecto; la persistencia opcional no elimina los plazos. Se habilitaron modo estricto, uso exclusivo de cookies y renovación periódica del identificador. Las sesiones antiguas sin metadatos requieren un nuevo acceso.
- Se centralizaron respuestas de error, excepciones y advertencias, con HTML o JSON según la solicitud y una referencia `X-Request-ID`. Se descarta la salida parcial cuando falla una solicitud y no se muestran detalles internos.
- Los logs JSON privados guardan referencias, eventos y ubicaciones de código sin contraseñas, correos, IP, cookies, tokens, consultas ni mensajes de excepción. Se verificaron rotación a cinco MiB y un aviso seguro al registro nativo de PHP si falla el almacenamiento.
- Se añadió mantenimiento CLI con simulación predeterminada y limpieza explícita de contadores vencidos y registros fuera de retención. No se creó una automatización ni se modificaron datos del negocio.
- Las pruebas sin MySQL comprobaron ventanas, reintentos, reinicio tras acceso correcto, límites por IP, almacenamiento corrupto y ocho procesos simultáneos: se permitieron exactamente tres intentos con un límite configurado de tres.
- Un IP bloqueado no puede crear contadores adicionales rotando correos. Los formularios con campos de tipo inválido se rechazan con 400 antes de procesarlos.
- Las pruebas de sesiones verificaron límites exactos de inactividad y duración máxima, cookies de navegador y persistentes, sesiones antiguas y renovación del identificador.
- Las pruebas HTTP aisladas verificaron el bloqueo entre sesiones, expiración y renovación con sesiones propias de prueba, referencias correlacionadas con logs y ausencia de secretos en los registros.
- Pasaron las pruebas de integración de registro, catálogo, carrito, favoritos y permisos con MariaDB temporal; en Apache local se comprobaron respuestas, cookie de navegador y bloqueo del almacenamiento privado.
- La prueba de rotación se ejecutó con archivos temporales fuera de la restricción del entorno que impedía renombrarlos. El servidor y las cuentas SQL temporales se cerraron al terminar; los archivos de diagnóstico temporales se conservan para inspección.
- Pasaron la revisión de sintaxis de 47 archivos PHP, la validación de Apache y las comprobaciones de configuración, documentación y exclusión de archivos privados de Git.

La configuración, el mantenimiento y los comandos de pruebas están en [README.md](README.md). Los planes 1 a 5 están completados; el siguiente es el Plan 6: pedidos y retiro en tienda.

### Revalidación completa del 17 de octubre de 2026

- [x] Revisadas las diez tareas del Plan 2 y confirmadas sus implementaciones existentes; no fue necesario modificar de nuevo la seguridad.
- [x] Pasaron `tests/configuration.php` y `tests/foundation.php`: configuración, límites concurrentes de acceso, caducidad y renovación de sesiones, errores seguros, rotación y retención de logs. La prueba de rotación requirió ejecución fuera de la restricción de renombrado de archivos temporales del entorno; pasó en esa ejecución.
- [x] Pasó `tests/database.php`: instalación desde cero, migraciones repetibles, restricciones, transacciones, autenticación, revocación de roles y permisos SQL limitados. Se utilizaron bases nuevas `tecno_star_test_plan2_20261017` y sus variantes, en MariaDB aislado en loopback 13308.
- [x] Pasó `tests/security-http.php`: CSRF en formularios y JSON, renovación de sesión y token, permisos actualizados, cuentas desactivadas, logout exclusivamente por POST, vencimiento de sesiones, límites entre sesiones y errores sin secretos.
- [x] Sintaxis correcta en 54 archivos PHP y ambos scripts JavaScript; Apache devolvió `Syntax OK`. Confirmada la exclusión de `.env` y `.env.migrations` mediante Git.
- [x] Cerrados los servidores temporales HTTP y MariaDB al terminar. Las bases de prueba se conservan para diagnóstico; no se alteraron los datos del negocio.

### Criterios de aceptación

- El proyecto puede instalarse siguiendo la documentación y las migraciones.
- Un usuario desactivado o sin permisos no puede continuar realizando operaciones protegidas.
- Las solicitudes sin protección CSRF válida se rechazan donde corresponda.
- Los errores se registran y el usuario recibe un mensaje comprensible.
- Las credenciales reales no quedan incorporadas al repositorio.

**Resultado:** proyecto reproducible, con acceso protegido y errores identificables.

## Plan 3: Completar la administración del catálogo

**Dependencia:** Plan 2.

### Tareas

- [x] Crear, editar y desactivar productos.
- [x] Administrar categorías y marcas.
- [x] Subir y reemplazar imágenes, validando tipo, tamaño y nombre de almacenamiento.
- [x] Validar códigos, precios y campos obligatorios desde el servidor.
- [x] Evitar códigos de producto duplicados.
- [x] Incorporar búsqueda, filtros y paginación desde el servidor.
- [x] Conectar los botones y menús administrativos con sus acciones reales.
- [x] Conservar las referencias históricas al desactivar productos, categorías o marcas.

**Estado del Plan 3: completado y verificado el 17 de octubre de 2026.**

### Implementación y comprobaciones

- Productos: alta, edición, activación y desactivación por formularios POST protegidos. Validaciones del servidor para estructura de entrada, código, nombre, referencias, estado y precios decimales. Los códigos se normalizan a mayúsculas y la restricción única también protege frente a duplicados simultáneos.
- Categorías y marcas: crear, renombrar, desactivar y reactivar sin borrado. Productos, categorías y marcas tienen búsqueda y filtros del servidor, con páginas de hasta 12 registros.
- Desactivar una categoría o marca oculta sus productos de la tienda, el detalle y las operaciones de compra. Se conservan filas de productos, inventario, carrito y favoritos; reactivar permite recuperar su visibilidad. No se cambia el estado individual de los productos de forma masiva.
- Imágenes JPG, PNG y WebP verificadas por contenido, tamaño y dimensiones. Límite de 4 MiB, 6000 px por lado y 20 megapíxeles; nombre aleatorio y directorio de cargas con ejecución bloqueada en Apache. La imagen anterior se conserva si falla la operación y la carga nueva fallida se descarta. Los reemplazos anteriores no se borran automáticamente.
- Crear producto e inventario cero es transaccional; editar o desactivar no altera existencias. La cuenta SQL admite INSERT y UPDATE del catálogo, sin DELETE ni permisos de escritura de stock. El usuario autorizó explícitamente la ampliación local y se aplicó y verificó mediante `database/enable-catalog.php`.
- Pasó `tests/catalog.php` en bases nuevas aisladas: formularios, precios y códigos inválidos, duplicados, filtros, paginación, CSRF, visitante y cliente bloqueados, revocación de administrador, referencias conservadas, cargas falsas/SVG/excesivas, respuesta 413 al superar el límite de petición, alta válida, reemplazo, rollback de imagen y reactivación.
- Pasaron las regresiones `tests/configuration.php`, `tests/database.php`, `tests/security-http.php` y `tests/storefront-run.php`, sin utilizar cuentas ni registros del negocio. Revisión en navegador con una cuenta sintética: creación de producto y formularios de productos/categorías sin desbordamiento a 360 px. Verificada además la disponibilidad del detalle en Apache local.
- Documentados requisitos, rutas, validaciones, permisos, respaldo de imágenes y pruebas en `README.md` y `database/README.md`. Los movimientos y ajustes de existencias continúan en el Plan 4.
- Revisión final de sintaxis: 64 archivos PHP y ambos scripts JavaScript correctos; Apache devolvió `Syntax OK`. Cerrados los servidores temporales y eliminadas las cuentas SQL e imágenes generadas por las pruebas; se conservan las bases y registros de diagnóstico.

### Ajuste visual solicitado después del Plan 3

- [x] Separar los estilos del catálogo en `public/assets/css/admin-catalog.css`, manteniendo `admin.css` para la estructura compartida del panel. Recursos versionados por fecha de modificación para evitar estilos antiguos en caché.
- [x] Dar formato a filtros, tablas, acciones y formularios de productos, categorías y marcas. Recuperado el desplazamiento vertical del sidebar cuando supera la altura de pantalla; su comportamiento colapsable se completó en el ajuste siguiente.
- [x] Crear y editar mediante modales accesibles con cierre explícito, Escape, foco contenido y retorno al botón de origen. El listado se actualiza después de guardar sin navegar a otra pantalla.
- [x] Sustituir el recuadro verde superior por toasts temporales y descartables. Mantener errores de validación dentro del modal con los datos introducidos; prevenir envíos simultáneos y avisar si no se puede confirmar una operación.
- [x] Verificar en navegador con datos aislados: alta y edición de producto, código duplicado y corrección sin cerrar el modal, alta y edición de categoría, nombre duplicado, alta de marca, cierre con Escape y retorno de foco, cambio de estado y confirmación toast. Formularios de producto y marca comprobados a 360 px sin desbordamiento.
- [x] Pasaron nuevamente `tests/catalog.php` y `tests/configuration.php`; revisión de sintaxis de 64 archivos PHP y `admin-catalog.js`, Apache `Syntax OK`. Las pruebas conservaron sus bases de diagnóstico, cerraron el servidor HTTP y retiraron sus cuentas SQL e imágenes temporales.

### Sidebar adaptable y cuenta flotante

- [x] Sidebar compartido entre dashboard, catálogo e inventario; eliminado el bloque de usuario del lateral y la implementación duplicada del dashboard.
- [x] En escritorio (desde 992 px), contraer a 84 px con solo logo e iconos y conservar la preferencia al cambiar de pantalla. Nombres accesibles y títulos disponibles también en modo contraído.
- [x] En móvil y tableta, panel lateral superpuesto con fondo, cierre al navegar, Escape, control del foco y bloqueo del contenido mientras permanece abierto.
- [x] Scrollbar fino integrado con los colores del sidebar, en un área desplazable que conserva visible la cabecera del logo.
- [x] Cuenta compacta flotante en la esquina superior derecha, desplegable con identidad, Mi perfil y Cerrar sesión por POST con CSRF.
- [x] CSS y JavaScript propios en `admin-shell.css` y `admin-shell.js`. Comprobado en navegador con una cuenta sintética: colapso a 84 px, persistencia entre dashboard/productos/inventario, scroll a 360 px de altura, panel móvil a 360 px de ancho, navegación y cierre, retorno del foco, compatibilidad con el modal de productos y logout válido desde el desplegable.

### Tablas unificadas y navegación sin duplicados

- [x] Confirmar que métricas y gráficos del dashboard consultan la BDD mediante `AdminController::index`; no contienen cifras de demostración en las vistas.
- [x] Retirar las pestañas duplicadas Productos/Categorías/Marcas y utilizar el sidebar para cambiar de módulo.
- [x] Integrar DataTables 3.1.4 en todas las tablas actuales: productos, categorías, marcas e inventario. Recursos oficiales alojados localmente con licencia, estilos separados en `admin-tables.css` y comportamiento en `admin-tables.js`.
- [x] Búsqueda, ordenación SQL y paginación sobre todos los registros mediante `/admin/tables/data`, con rol Administrador revalidado, consultas parametrizadas, columnas de orden permitidas y límites de página. Resultados escapados contra XSS; interfaz en español con 12, 25, 50 o 100 filas.
- [x] Conservar búsqueda, filtros, orden y página al guardar desde un modal, sin duplicar tablas ni controles. Un fallo de consulta muestra un error con reintento. Los totales de inventario siguen considerando la BDD completa, independientemente de la página.
- [x] Pruebas HTTP aisladas: totales globales y filtrados, segunda página, filtros combinados, orden numérico, inventario paginado, rechazo de visitantes/clientes, parámetros inválidos y nombres escapados. Pasaron `tests/catalog.php`, `tests/database.php` y `tests/configuration.php`.
- [x] Navegador: las cuatro tablas, búsqueda global, orden por precio, paginación, edición desde segunda página conservada y cambio a 25 filas mostrando los 16 registros de prueba. Comprobada presentación móvil a 360 px sin desbordamiento de la página.

### Criterios de aceptación

- El administrador puede mantener el catálogo sin editar directamente la base de datos.
- Los usuarios sin permisos no pueden modificar productos.
- Los archivos inválidos y los datos incompletos se rechazan con mensajes claros.
- Los cambios válidos se reflejan en la tienda.

**Resultado:** catálogo administrable desde el panel.

## Plan 4: Completar el inventario

**Dependencias:** planes 2 y 3.

### Tareas

- [x] Registrar entradas, salidas y ajustes con motivo y responsable.
- [x] Consultar el historial de movimientos por producto.
- [x] Configurar existencias mínimas y alertas de stock bajo.
- [x] Separar stock físico, reservado y disponible.
- [x] Impedir movimientos que generen cantidades negativas o reservas superiores al stock físico.
- [x] Realizar movimientos mediante transacciones y controles de concurrencia.
- [x] Incorporar filtros y paginación al historial.

### Verificación del 17 de octubre de 2026

- [x] Entradas/salidas relativas, ajuste a conteo absoluto y mínimo desde modal, con motivo obligatorio y responsable verificado en BDD. CSS y JS propios, notificaciones toast y actualización de indicadores y tablas sin recargar la página.
- [x] Historial DataTables con búsqueda, filtros de producto, tipo, responsable y fechas, orden y paginación SQL. Saldos físicos antes/después, reservado, mínimo anterior/nuevo, fecha y responsable conservados.
- [x] Transacciones y bloqueo por producto; fallo inducido en la escritura del historial revirtió también el stock. Dos salidas simultáneas aceptaron únicamente la cantidad disponible; dos entradas conservaron ambos incrementos.
- [x] Reintentos y envíos simultáneos con la misma solicitud guardaron una sola operación. Ajustes obsoletos rechazados. Validaciones HTTP de límites, reservas, CSRF, clientes sin permisos, filtros inválidos y XSS. Milésimas exactas sin pérdidas ni redondeo.
- [x] Pasaron `tests/catalog.php --inventory`, `tests/inventory-upgrade.php`, `tests/database.php` y `tests/configuration.php`. Probadas instalación canónica y actualización antigua, repetición sin duplicados, conservación del historial previo y saldos decimales. Bases aisladas `tecno_star_test_catalog_inv03`, `tecno_star_test_inv_upgrade02` y `tecno_star_test_inv_reg02` y sus variantes.
- [x] Navegador: guardar entrada, corregir error sin cerrar el modal, configurar mínimo y activar alerta, filtrar historial desde el producto, cierre con Escape y adaptación a 360 px sin desbordar la página. Sin errores de JavaScript.
- [x] Habilitado en la instalación real tras autorización explícita del usuario. El esquema antiguo se conserva, incluido `tbmovimientosinventario`; el historial nuevo `tbstockmovimientos` comienza en la apertura y no inventa movimientos anteriores. Verificados seis saldos intactos. Cuenta SQL con UPDATE solo de físico/mínimo y SELECT/INSERT de historial, sin cambiar reservas ni alterar/borrar movimientos.
- [x] Revisión final: sintaxis correcta en 76 archivos PHP y los scripts de inventario y tablas; Apache `Syntax OK`. Cerrados los servidores HTTP y MariaDB temporales, retiradas sus cuentas SQL y conservadas las bases de diagnóstico. Prueba visual final con una entrada de 0.125 y total 10.125 en tabla, indicadores y modal.

Las reservas se muestran y protegen; su creación y liberación pertenece al flujo de pedidos del Plan 6. La auditoría corresponde a operaciones realizadas por la aplicación; cambios SQL administrativos externos no se atribuyen a un usuario ficticio.

### Criterios de aceptación

- Cada modificación de existencias tiene un movimiento rastreable.
- El stock disponible corresponde al stock físico menos el reservado.
- Un fallo durante una operación no deja movimientos o existencias parcialmente actualizados.
- Las operaciones simultáneas mantienen la consistencia del inventario.

**Resultado:** existencias confiables y movimientos rastreables.

## Plan 5: Fortalecer carrito y favoritos

**Dependencias:** planes 2, 3 y 4.

### Tareas

- [x] Corregir la sincronización del carrito de visitante para que los reintentos no dupliquen cantidades.
- [x] Manejar cambios de precio, productos desactivados y falta de existencias.
- [x] Mostrar claramente cuándo un cambio no pudo guardarse.
- [x] Definir cómo recuperar o reintentar operaciones fallidas sin duplicarlas.
- [x] Verificar el comportamiento al iniciar sesión y cambiar de cuenta.
- [x] Verificar el comportamiento con varias pestañas y solicitudes consecutivas.
- [x] Mantener los datos de cada cuenta separados en el navegador.

### Verificación del 18 de octubre de 2026

**Estado:** completado y activado en la instalación local con autorización explícita del usuario.

- [x] Diario transaccional de solicitudes con clave y huella: reintentar sincronización, agregar o enviar simultáneamente la misma copia no duplica cantidades ni revisiones. Un fallo inducido al guardar el diario revierte carrito y revisión.
- [x] Revisiones por carrito para ediciones absolutas; operaciones relativas serializadas sin perder incrementos. Pruebas con procesos SQL independientes y dos pestañas del navegador.
- [x] Identidad destino y CSRF obligatorios. Lote de visitante reclamado por una sola cuenta, pendientes y caché separados por usuario; al cambiar de cuenta se vacía la vista antigua. Probado inicio de sesión, recarga sin duplicación, otra cuenta vacía y recuperación del lote original tras respuesta perdida.
- [x] Precio actual con aviso y revisión explícita; stock insuficiente con ajuste voluntario; productos desactivados conservados con aviso, retiro permitido y exclusión del total estimado. Verificados en HTTP y navegador, incluidos favoritos.
- [x] Cambios confirmados por servidor antes de anunciarlos como guardados. Un fallo incierto deja la misma solicitud pendiente con reintento. Pruebas Node simulan respuesta perdida después del commit y cambio de cuenta durante un lote pendiente.
- [x] Pasaron pruebas de actualización del Plan 5 en esquemas canónico y antiguo, repetidas sin cambiar cantidades ni stock. También `tests/database.php`, `tests/catalog.php --shopping --inventory`, `tests/security-http.php`, `tests/storefront-run.php`, `tests/configuration.php` y `tests/shopping-client.cjs`.
- [x] Revisión visual de escritorio y móvil a 360 px sin desbordamiento, ajuste de stock y confirmación del nuevo precio. Ajuste posterior solicitado: tarjeta compacta sin descripción larga, cantidad/subtotal juntos y avisos desplegables con ajuste en la misma franja; sintaxis y pruebas del cliente correctas. La revisión visual de ese último ajuste quedó bloqueada por la política del navegador.
- [x] Migración y permisos aplicados a la instalación local tras aprobación específica del usuario. Verificados campos de revisión/precio y SELECT/INSERT del diario, sin UPDATE/DELETE. Cantidades conservadas; sin modificar existencias, reservas, cuentas ni contraseñas. Completadas las pruebas de seguridad HTTP, tienda y actualización canónica/antigua; sintaxis correcta en 82 archivos PHP y scripts del carrito, Apache `Syntax OK`.

### Criterios de aceptación

- Repetir una sincronización no vuelve a sumar los mismos productos.
- El carrito de una cuenta no aparece en otra.
- Las diferencias de stock o precio se comunican antes de confirmar el pedido.
- Un fallo de persistencia no se presenta como una operación guardada correctamente.

**Resultado:** carrito consistente entre navegador y cuenta del cliente.

## Plan 6: Implementar pedidos y retiro en tienda

**Dependencias:** planes 2 a 5.

### Tareas

- [x] Crear la pantalla de confirmación del pedido con datos de contacto.
- [x] Recalcular precios, cantidades y total desde el servidor.
- [x] Validar y reservar existencias dentro de una transacción.
- [x] Generar un número de pedido único.
- [x] Guardar el detalle y los precios aplicados al confirmar.
- [x] Evitar pedidos duplicados por doble clic o reintentos de la solicitud.
- [x] Gestionar estados: pendiente, confirmado, en preparación, listo, entregado y cancelado.
- [x] Definir y validar las transiciones permitidas entre estados.
- [x] Definir la vigencia de las reservas y liberar existencias al cancelar o vencer el pedido.
- [x] Registrar quién realiza cada cambio de estado y cuándo.
- [x] Permitir al cliente consultar únicamente sus propios pedidos.
- [x] Mostrar instrucciones para retirar el pedido en tienda.

### Verificación del 18 de octubre de 2026

**Estado:** implementado, probado y activado en la instalación local tras autorización específica de migración y permisos. La activación conserva pedidos y saldos anteriores.

- [x] Confirmación desde el carrito, precios y cantidades recalculados, contacto validado y aceptación explícita. Precio cambiado exige revisar otra cotización. Detalle conserva nombre/código/precio históricos y vacía el carrito junto con la reserva.
- [x] Reservas por 24 horas desde la creación. Cancelación y vencimiento liberan una sola vez; entrega descuenta físico y reservado una sola vez. Probados con procesos SQL simultáneos: última unidad, cancelación, entrega y vencimiento. Bloqueos ordenados y reintento acotado de interbloqueos.
- [x] Estados secuenciales, motivo obligatorio y responsable real. Cliente cancela solo antes de preparación. Pedidos terminales no se reabren; saltos inválidos se rechazan. Fallos inducidos revierten pedido, detalle, movimiento, carrito e historial.
- [x] Mis pedidos y administración con DataTables, filtros y paginación SQL. Propietario, alcance administrativo/personal, CSRF e identidad destino verificados en servidor. Otro cliente no puede abrir un pedido ni consultar su conteo.
- [x] Navegador: confirmación crea pedido y toast; lectura por campo de código abre el detalle; modal guarda el cambio y muestra toast. CSS propio de pedidos, seguimiento, contacto e instrucciones de retiro. Revisado escritorio y 360 px; corregida superposición del contenido con el sidebar y comprobado que no hay desbordamiento horizontal.
- [x] Migración 005 para instalaciones nuevas y adaptador de esquema antiguo, con checksum y bloqueo. Actualización y repetición probadas conservando pedidos, estados y saldos sin generar reservas o correos de registros antiguos.
- [x] Confirmación y cancelación funcional comprobadas también sobre el esquema antiguo con cantidades `DECIMAL(12,3)`, conservando sus saldos originales.
- [x] Regresión conjunta de catálogo, inventario, carrito y pedidos; pruebas de BDD, seguridad HTTP, configuración y clientes JavaScript. Verificación de esquema/permisos local y Apache `Syntax OK`. Bases de prueba separadas, sin pedidos sintéticos en la tienda real.
- [x] Revisión final: 95 archivos PHP con sintaxis correcta; scripts de pedidos, cámara y tablas válidos. Apache responde en 8012 y bloquea `vendor` y `composer.json` con 403. Vistas y servidores temporales cerrados; bases de diagnóstico conservadas. Regresión conjunta en `tecno_star_test_catalog_ord06`, ampliación de correo vencido en `tecno_star_test_catalog_ord07`, actualización canónica/antigua funcional en `tecno_star_test_order_up03` y seguridad HTTP en `tecno_star_test_db_ord02`.

### Ampliación solicitada: correo con QR y lectura por personal

**Corrección del 19 de octubre de 2026:** se detectó que el esquema local conserva `ck_pedidos_fechas`, que exige `fecha_confirmacion` al guardar un vencimiento. La primera prueba de compatibilidad no contenía esa restricción. Se corrigió la creación para registrar la confirmación del cliente en esa columna cuando existe, conservando el estado pendiente de aceptación de la tienda y el plazo de 24 horas. No se modificaron restricciones ni pedidos históricos. `tests/orders-upgrade.php` ahora reproduce la condición exacta y comprueba creación, recuperación de la misma solicitud, aparición en DataTables y liberación al cancelar en ambos esquemas. El aviso de confirmación pendiente indica una respuesta sin comprobar, no un pedido ya guardado; el usuario puede recuperar el intento con «Comprobar confirmación». Los errores de contacto identifican el campo inválido.

**Compatibilidad de cuentas anteriores:** una cuenta activa sin fila en `tbclientes` ahora obtiene su perfil al confirmar un pedido válido, dentro de la misma transacción. La identidad procede de la cuenta autenticada y el teléfono de los datos de retiro validados; no se aceptan IDs de cliente del formulario ni se cambian rol, contraseña o permisos. Se bloquea la cuenta para serializar intentos y se conserva un solo perfil/pedido al reintentar. La prueba con cuenta SQL limitada incluye administrador anterior sin perfil y fallo inducido del historial: se revierten perfil, reserva y pedido, conservando el carrito.

- [x] Composer con PHPMailer y Endroid QR Code; GD habilitado en XAMPP. Dependencias fijadas en `composer.lock`, requisitos de plataforma correctos y auditoría Composer sin avisos de vulnerabilidad.
- [x] Encolar un correo al pasar a **Confirmado**, en la misma transacción del estado. QR con enlace y código único, productos, precios, total y plazo. Imagen PNG generada y decodificada; contenido MIME y destinatario verificados sin enviar correos reales.
- [x] Implementar aviso separado al pasar a **Listo para retirar**, con QR, productos, total y vencimiento; al terminar vuelve al detalle del pedido, sin redirigir a Caja. Repetición y concurrencia probadas sin duplicar el evento; fallo SMTP conserva un reintento pendiente y fallo de cola revierte el cambio de estado.
- [x] Rediseñar confirmación, aviso de retiro y recuperación con logo incrustado, estructura de correo adaptable, acción principal y alternativa de texto. Verificados MIME, escritorio y ancho móvil de 320 px con datos sintéticos.
- [x] Activar la migración 008 del aviso de retiro en la base real, tras autorización expresa, con copia previa y conservación verificada de envíos y datos de negocio. La cuenta de la aplicación verificó la habilitación; no se modificaron permisos ni se generaron envíos históricos.
- [x] Envío posterior al commit, estado pendiente ante fallo y reintentos con reclamación exclusiva. Pruebas de fallo SMTP, fallo de cola con rollback y dos procesos reclamando un solo envío; no se reenvían correos marcados enviados ni pendientes de pedidos cerrados/vencidos. SMTP puede duplicar excepcionalmente tras respuesta perdida del proveedor; Message-ID estable.
- [x] Campo de lector USB y lectura de cámara con ZXing local, selección de cámara, aviso de permisos/contexto seguro y apagado al salir. Se neutraliza la navegación a enlaces externos leídos; solo se extrae el código y abre una ruta propia. Pruebas JavaScript con cámara simulada.
- [x] Administrador y empleado activo con permiso explícito mediante lista de IDs privada. Sin permiso automático por tener rol Empleado y sin acceso adicional al inventario u otros módulos; revalidación en cada solicitud.
- [x] Guía `docs/GUIA_GMAIL_GOOGLE.md` con contraseña de aplicación Gmail, configuración local/LAN, permisos de empleados, lectura QR y obtención de Client ID/Secret del proyecto existente `tecnostar`.
- [x] Configuración privada de Gmail presente, `MAIL_ENABLED=1` y conexión STARTTLS/autenticación comprobadas el 19 de octubre con `database/check-mail.php --connect`, sin publicar claves ni enviar correos.
- [x] Envío real del correo con QR del pedido 1 confirmado: aceptado por Gmail y cola en `enviado`, el 19 de octubre. Composer comprobó las dependencias; se corrigió GD sin cargar en Apache reiniciándolo y se verificó generación QR y SMTP desde el servidor web. No se reenviaron correos ya enviados.
- [ ] Comprobar recepción en la bandeja del destinatario, incluida la imagen QR. La aceptación SMTP no confirma ubicación en entrada o spam.
- [ ] Comprobar lectura con el hardware USB/cámara del negocio y la dirección de red elegida. La interfaz y el decodificador están probados; no se afirma haber probado dispositivos físicos.

El login OAuth de Google está implementado y activado en local desde el 22 de octubre de 2026: cliente web creado, credenciales privadas, callback con state/nonce/PKCE, vinculación con contraseña y registro exclusivo de Clientes. Consulta `docs/GUIA_GMAIL_GOOGLE.md`. El cobro tras escanear ya está disponible mediante el Plan 7 para pedidos listos. La liberación de reservas se ejecuta al acceder/operar los módulos o mediante `database/expire-orders.php --run`; los correos pendientes mediante `database/send-order-mails.php --run`. No se creó una tarea programada automáticamente. El permiso de empleado se administra ahora desde Usuarios; la lista privada de IDs se conserva como alternativa para cuentas sin permiso registrado.

### Criterios de aceptación

- El cliente obtiene un número de confirmación y puede consultar su pedido.
- Dos pedidos simultáneos no pueden reservar las mismas últimas unidades.
- Cancelar o vencer un pedido libera su reserva una sola vez.
- La entrega descuenta el stock físico y libera la reserva sin duplicar movimientos.
- Los cambios posteriores del catálogo no alteran los precios históricos del pedido.

**Resultado:** flujo completo desde el carrito hasta la entrega en tienda.

## Plan 7: Completar ventas, usuarios y cuentas

**Cierre operativo del 19 de octubre de 2026:** se implementaron y probaron Caja, las bandejas de Pedidos, las tarjetas del cliente y la reinversión de ventas (50 % para reposición y 5 % para crecimiento). La activación local quedó autorizada y verificada con copia previa y permisos mínimos. El estado y la prueba física pendiente se recogen en [PLAN_CIERRE_ADMINISTRACION.md](PLAN_CIERRE_ADMINISTRACION.md).

**Dependencias:** planes 2 a 6.

### Tareas

- [x] Registrar el cobro y vincularlo con el pedido.
- [x] Registrar método de pago, importe, fecha y responsable.
- [x] Evitar registrar dos veces el mismo cobro.
- [x] Consultar ventas por fecha y método de pago.
- [x] Definir el procedimiento de anulación y su efecto sobre cobros e inventario.
- [x] Administrar usuarios, estados y roles con permisos diferenciados.
- [x] Permitir editar el perfil y cambiar la contraseña.
- [x] Implementar recuperación de contraseña por correo con tokens de un solo uso y vencimiento.
- [x] Añadir historial de compras del cliente.
- [x] Conectar los menús de ventas y usuarios con sus pantallas funcionales.

### Verificación del 19 de octubre de 2026

**Estado: completado, probado y activado**, tras autorización explícita de la migración y sus permisos. La activación conservó cuentas, contraseñas, roles asignados, ventas y saldos existentes; no creó operaciones de negocio de prueba.

- [x] Efectivo como único método acordado. Desde un pedido listo, cobrar y entregar se confirman juntos: importe recibido, cambio, fecha y nombre histórico del responsable. El servidor calcula importes exactos y rechaza efectivo insuficiente, pedidos vencidos y entregas sin cobro. Reintentos y solicitudes concurrentes no duplican ventas ni movimientos.
- [x] Anulación exclusiva del administrador, con motivo, confirmación del reembolso completo y selección explícita de devolución física. Solo se repone inventario cuando se confirma esa devolución; quedan el cobro y la auditoría histórica. No incluye anulaciones parciales ni procesamiento bancario.
- [x] Ventas y compras propias con DataTables, filtros de fechas, método y estado, totales correspondientes al filtro y aislamiento de registros/conteos por cliente.
- [x] Usuarios: altas y edición mediante modal, roles, estado y permiso de pedidos/caja del empleado. Protección del propio administrador y del último administrador activo, control de ediciones simultáneas y auditoría. Permisos revalidados en sesiones existentes; desactivación y cambio de contraseña invalidan sesiones anteriores.
- [x] Perfil y cambio de contraseña requieren la clave actual; los clientes solo modifican sus datos. Nuevas claves de 12–72 bytes. Recuperación con respuesta genérica y límites de intentos, token aleatorio almacenado como hash, vigencia de 20 minutos, un solo uso y rechazo ante consumo concurrente. El enlace usa fragmento para no incluir el token en la petición inicial ni en sus registros. Un fallo de envío invalida el token sin revelar si la cuenta existe.
- [x] Migración 006 y adaptador del esquema antiguo probados, conservando datos, enum/default de ventas anteriores y cantidades decimales. Cuenta SQL limitada: permisos de actualización por columnas y auditorías sin UPDATE/DELETE. Verificación de activación local correcta.
- [x] Regresión conjunta de catálogo, inventario, carrito, pedidos y cuentas en `tecno_star_test_catalog_sa04`; migración nueva en `tecno_star_test_db_sa01` y actualización canónica/antigua funcional en `tecno_star_test_sales_up02`. Pruebas de concurrencia, aislamiento, rollback de cobro/entrega, recuperación y permisos aprobadas.
- [x] Seguridad HTTP sobre base aislada: CSRF, rotación y vencimiento de sesión, revocación, roles y errores sin datos privados. Pruebas de clientes JavaScript aprobadas. Navegador con datos sintéticos: ventas, modal de usuario y cobro de 24.99 recibido con 50.00, cambio 25.01 y comprobante resultante. CSS separado en `commerce.css` y `accounts.css`; mensajes mediante toast.

La entrega real de Gmail y el escaneo con los dispositivos físicos siguen pendientes en el Plan 6; no bloquean las pruebas aisladas del flujo de caja. La dirección pública configurada aún usa localhost: para abrir enlaces desde otro dispositivo hace falta configurar y comprobar la dirección de red.

### Criterios de aceptación

- Cada venta puede relacionarse con su pedido y cobro.
- Un reintento no genera cobros o ventas duplicados.
- Los reportes corresponden a las operaciones registradas.
- Los cambios de permisos se aplican a las sesiones existentes.
- Cada cliente puede consultar y modificar solamente sus propios datos.
- Los enlaces de recuperación vencidos o utilizados se rechazan.

**Resultado:** administración de la operación diaria y autoservicio básico del cliente.

## Revisión del cliente después de los planes 1 a 7

- [x] Revisar el recorrido del cliente, textos, iconos, formularios, estados vacíos y navegación móvil.
- [x] Eliminar acciones y mensajes administrativos de las pantallas del cliente y comprobar el bloqueo de acceso directo.
- [x] Verificar carrito, favoritos, pedidos, compras y cuenta mediante pruebas aisladas de los planes 1 a 7.
- [x] Corregir el contraste de los controles: texto blanco cuando el hover cambia el fondo a azul, incluida la paginación.

Realizada el 19 de octubre de 2026. Resultados, cambios y límites en [Revisión del cliente · Planes 1 a 7](docs/REVISION_CLIENTE_PLANES_1_A_7.md). Las tareas de publicación y comprobaciones externas siguen pendientes.

## Revisión de administración después de los planes 1 a 7

- [x] Revisar jerarquía visual, navegación y enlaces para volver en los módulos administrativos.
- [x] Eliminar encabezados, textos y controles redundantes o ajenos a cada pantalla y rol.
- [x] Simplificar catálogo, inventario, pedidos, ventas, usuarios y perfil del administrador, conservando modales, toasts y DataTables.
- [x] Verificar escritorio y móvil, filtros, permisos y regresión funcional de los planes implementados.

Realizada el 19 de octubre de 2026. Cambios y comprobaciones en [Revisión de administración](docs/REVISION_ADMINISTRACION.md). El Plan 8 continúa pendiente.

## Plan 8: Verificar y preparar la publicación

**Dependencias:** planes 1 a 7. La verificación de cada función también se realiza durante su implementación.

### Tareas

- [ ] Probar el recorrido completo: registro, acceso, carrito, pedido, preparación, cobro y entrega.
- [ ] Probar permisos, cancelaciones, vencimiento de reservas y recuperación de contraseña.
- [ ] Comprobar pedidos simultáneos sobre las últimas unidades disponibles.
- [ ] Verificar que los reintentos no dupliquen pedidos, reservas, cobros o movimientos.
- [ ] Revisar navegación móvil, uso con teclado, foco y mensajes de error.
- [ ] Configurar HTTPS y las opciones de producción de PHP y sesiones.
- [ ] Configurar respaldos de la base de datos y de las imágenes.
- [ ] Realizar una prueba de restauración de los respaldos.
- [ ] Optimizar imágenes y consultas según las mediciones obtenidas.
- [ ] Documentar despliegue, actualización, recuperación y mantenimiento.

### Criterios de aceptación

- El flujo completo funciona con cuentas de cliente y administrador.
- Las comprobaciones críticas de permisos, inventario y duplicación pasan.
- La aplicación funciona en los dispositivos y navegadores definidos para el proyecto.
- Existe un respaldo restaurable y un procedimiento de recuperación comprobado.
- La configuración de producción no muestra errores internos ni expone archivos privados.

**Resultado:** versión lista para operar, con respaldo y procedimientos claros.

## Segunda etapa: funciones opcionales

Evaluar estas funciones después de completar el flujo principal:

- [x] Acceso con Google mediante OAuth, implementado para localhost el 22 de octubre de 2026. Producción requiere dominio HTTPS y su cliente OAuth.
- Promociones y ofertas reales, con vigencia y reglas de aplicación.
- Pagos en línea, si el negocio los necesita.
- Notificaciones de cambios de estado por correo u otros canales autorizados.
- Reportes comerciales más detallados.

## Orden de ejecución

1. Mejorar el contenido de la tienda para el cliente autenticado.
2. Preparar y asegurar la base (completado).
3. Completar la administración del catálogo.
4. Completar el inventario.
5. Fortalecer carrito y favoritos.
6. Implementar pedidos y retiro en tienda.
7. Completar ventas, usuarios y cuentas.
8. Verificar y preparar la publicación.

El [cierre operativo de administración](PLAN_CIERRE_ADMINISTRACION.md) está implementado, probado y activado localmente: bandejas de Pedidos, tarjetas del cliente y Caja con escaneo, pago y reinversión integrada. Continúan pendientes la lectura con los dispositivos físicos y la recepción real del correo; después corresponde completar el Plan 8 de publicación.
