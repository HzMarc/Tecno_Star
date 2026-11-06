(() => {
    'use strict';
    if (document.querySelector('[data-context-help-panel]')) return;
    const assets = document.querySelector('[data-context-help-assets]');
    const base = new URL(assets?.dataset.helpBase || '/', location.href).pathname.replace(/\/$/, '');
    const staff = ['Administrador','Empleado'].includes(assets?.dataset.helpRole);
    const guide = (title, steps, tip = '') => ({title, steps, tip});
    const guides = {
        home: guide('Inicio', ['Elige Catálogo para ver productos o Servicios para solicitar un trabajo.', 'Abre un producto para conocer su precio y disponibilidad.', 'Desde tu cuenta puedes consultar pedidos, compras y solicitudes.']),
        catalog: guide('Catálogo de productos', ['Busca por nombre y elige una categoría para reducir los resultados.', 'Usa Ordenar para comparar precios o ver los productos más recientes.', 'Abre el detalle del producto antes de elegir.', 'Añade al carrito para comprar o guarda en Favoritos para después.', 'Limpiar filtros vuelve a mostrar todos los productos.']),
        product: guide('Detalle del producto', ['Revisa el nombre, precio, descripción e imagen.', 'Elige una cantidad dentro de las unidades disponibles.', 'Agrega al carrito o guarda el producto en Favoritos.', 'Abre el carrito para revisar el total y continuar con tu pedido.']),
        cart: guide('Carrito', ['Revisa los productos y cambia sus cantidades.', 'Quita los que ya no necesitas y comprueba el total estimado.', 'Revisa cualquier aviso sobre precio o disponibilidad.', 'Continúa con el pedido para indicar quién retirará la compra.', 'El pago se realiza en la tienda; agregar al carrito todavía no reserva existencias.']),
        favorites: guide('Favoritos', ['Guarda aquí los productos que quieres revisar después.', 'Abre un producto para ver sus detalles o pásalo al carrito.', 'Quita un favorito cuando ya no lo necesites.', 'Con tu cuenta abierta, lo guardado queda vinculado a ella.']),
        checkout: guide('Confirmar un pedido', ['Revisa cantidades, precios y total.', 'Completa nombre, teléfono y correo de quien retirará.', 'Lee el plazo de retiro y marca la aceptación.', 'Confirma y conserva el código del pedido.', 'Consulta Mis pedidos y espera el estado Listo para retirar antes de acudir.'], 'La reserva dura 24 horas. Al vencer, el pedido se cancela y se liberan sus unidades.'),
        orders: guide('Mis pedidos', ['Filtra los pedidos activos o cerrados.', 'Abre el pedido para ver productos, total, código y plazo.', 'Consulta el estado para conocer su avance.', 'Puedes cancelar antes de que comience la preparación.', 'Cuando esté listo, presenta el código y el nombre de retiro en la tienda.']),
        orderDetail: guide('Detalle de tu pedido', ['Comprueba productos, cantidades, contacto y fecha límite.', 'Usa Consultar estado para ver el avance.', 'Conserva el número o código de retiro.', 'La cancelación está disponible antes de la preparación.', 'Después de pagar en tienda, revisa el comprobante en Mis compras.']),
        purchases: guide('Mis compras', ['Busca por comprobante o pedido y filtra por fecha y estado.', 'Abre Ver comprobante para revisar productos e importes.', 'Pagada indica un cobro completado; Reembolsada indica una devolución registrada.', 'Usa Limpiar para quitar los filtros.']),
        receipt: guide('Comprobante de compra', ['Revisa el pedido asociado, productos y total.', 'Comprueba el efectivo recibido y el cambio.', 'Si hay un reembolso, revisa el importe y el motivo.', 'Vuelve a Mis compras para consultar otra operación.']),
        profile: guide('Mi perfil', ['Revisa los datos de tu cuenta.', 'Abre Editar para cambiar los datos permitidos y guarda.', 'Confirma con tu contraseña actual cuando el formulario lo solicite.', 'Desde los accesos de tu cuenta puedes abrir tus pedidos, compras y servicios.']),
        password: guide('Contraseña de tu cuenta', ['Introduce tu contraseña actual si estás cambiándola desde el perfil.', 'Elige una contraseña nueva de al menos 12 caracteres y confírmala.', 'Guarda y vuelve a acceder cuando el sistema lo indique.', 'Si no recuerdas la contraseña actual, usa Recuperar acceso.']),
        login: guide('Iniciar sesión', ['Escribe el correo de tu cuenta y la contraseña.', 'Usa el botón del ojo si necesitas revisar lo escrito.', 'Pulsa Iniciar sesión.', 'Si olvidaste la contraseña, abre Recuperar acceso; si no tienes cuenta, abre Registro.']),
        register: guide('Crear tu cuenta', ['Completa nombres, apellidos, teléfono y correo.', 'Usa un correo al que tengas acceso y que no esté registrado.', 'Escribe una contraseña de al menos 12 caracteres y repítela.', 'Crea la cuenta y sigue la confirmación que muestra el sistema.']),
        recovery: guide('Recuperar acceso', ['Escribe el correo de tu cuenta y solicita el enlace.', 'Revisa tu buzón y la carpeta de spam.', 'Abre el enlace recibido y escribe una nueva contraseña.', 'El enlace es de un solo uso y vence a los 20 minutos.']),
        google: guide('Continuar con Google', ['Revisa el correo verificado por Google.', 'Completa los datos que falten en el formulario.', 'Si ese correo ya tiene cuenta, confirma la contraseña para vincularla.', 'Guarda para continuar con la misma cuenta de Tecno Star.']),
        dashboard: guide('Resumen de administración', ['Revisa los indicadores de pedidos, ventas y existencias.', 'Abre las tarjetas para ir a sus registros.', 'Usa el menú lateral para entrar a cada área.', 'En las listas puedes buscar, filtrar y volver al resumen.']),
        products: guide('Administrar productos', ['Busca por nombre o código y filtra por categoría, marca o estado.', 'Abre Nuevo producto para registrar uno o Editar para modificarlo.', 'Para activar o desactivar, revisa la confirmación correspondiente.', 'El producto necesita existencias disponibles y referencias activas para aparecer en la tienda.']),
        productForm: guide('Agregar o editar un producto', ['Completa código único, nombre y categoría.', 'Selecciona una marca o deja Sin marca.', 'Si falta una categoría o marca, pulsa + junto a su lista: se abrirá otra ventana sin borrar el producto.', 'Agrega descripción, precios, estado e imagen.', 'Guarda el producto. Las existencias se gestionan desde Inventario.']),
        prices: guide('Precios y visibilidad', ['Escribe precio de compra y precio de venta sin valores negativos.', 'Elige Activo si se ofrecerá al público o Inactivo si quieres ocultarlo.', 'Revisa también que la categoría y marca estén activas.', 'Guarda los cambios y comprueba el catálogo público.']),
        image: guide('Imagen del producto', ['Puedes subir JPG, PNG o WebP, o indicar una URL http o https.', 'Elige una sola opción: archivo, URL o quitar imagen.', 'El archivo admite hasta 4 MiB, 6000 píxeles por lado y 20 megapíxeles.', 'Si el formulario señala un error, vuelve a elegir el archivo antes de guardar.']),
        categories: guide('Categorías', ['Busca la categoría antes de crear otra.', 'Escribe un nombre claro y elige su estado.', 'Guarda para que esté disponible en los productos.', 'Desactivarla oculta al público los productos relacionados, sin borrar sus registros.']),
        brands: guide('Marcas', ['Busca la marca antes de crear otra.', 'Escribe su nombre y elige si estará activa.', 'Guarda para poder seleccionarla en un producto.', 'Desactivar una marca oculta los productos relacionados en la tienda.']),
        newCategory: guide('Crear categoría sin salir del producto', ['Escribe el nombre de la categoría que falta.', 'Pulsa Crear y seleccionar.', 'La categoría queda seleccionada en el producto.', 'Cancelar vuelve al producto sin cambiarlo ni perder lo escrito.']),
        newBrand: guide('Crear marca sin salir del producto', ['Escribe el nombre de la nueva marca.', 'Pulsa Crear y seleccionar.', 'La marca queda seleccionada en el producto.', 'Cancelar cierra solo esta ventana; el producto permanece abierto.']),
        inventory: guide('Inventario', ['Busca un producto y revisa físico, reservado y disponible.', 'Abre Registrar movimiento para entradas, salidas, ajustes o mínimo.', 'Indica cantidad y motivo antes de guardar.', 'Revisa el Historial de movimientos y sus filtros.', 'Disponible es el físico menos lo reservado para pedidos.']),
        movement: guide('Registrar un movimiento', ['Selecciona la operación: entrada, salida, ajuste físico o mínimo.', 'Indica las unidades; en Ajuste escribe el conteo físico final.', 'Añade un motivo para dejar constancia del cambio.', 'Guarda y revisa el saldo y el historial.'], 'Una salida no puede superar lo disponible; un conteo físico no puede quedar por debajo de lo reservado.'),
        adminOrders: guide('Atender pedidos', ['Revisa Pendientes y abre un pedido.', 'Comprueba productos y contacto antes de confirmarlo.', 'Avanza a En preparación y luego a Listo para retirar.', 'Cuando llegue el cliente, abre Caja para registrar pago y entrega.', 'Consulta Historial para revisar pedidos entregados o cancelados.']),
        adminOrderDetail: guide('Gestionar un pedido', ['Revisa productos, total, contacto y plazo.', 'Usa la acción disponible para confirmar, preparar o marcar listo.', 'Consulta el historial antes de cambiar el estado.', 'Si el correo quedó pendiente, usa el reintento que ofrece la pantalla.', 'Para entregar un pedido listo, registra primero el cobro en Caja.']),
        cash: guide('Caja', ['Busca el código del pedido o utiliza el lector disponible.', 'Comprueba el contacto, productos y total antes de cobrar.', 'El pedido debe estar listo y tener una reserva vigente.', 'Introduce el efectivo recibido y revisa el cambio.', 'Confirma el pago y la entrega; revisa el comprobante.', 'Si la respuesta queda pendiente, usa Comprobar resultado antes de reintentar.']),
        sales: guide('Ventas', ['Busca por comprobante o pedido y filtra por fechas y estado.', 'Abre una venta para comprobar total, efectivo y cambio.', 'El administrador puede registrar anulación y reembolso desde el detalle.', 'Indica devolución física solo si los productos regresaron realmente.', 'Usa Limpiar para volver a ver todas las ventas.']),
        reinvestment: guide('Reinversión', ['Filtra por fecha, estado o código de venta.', 'Revisa la distribución para reposición y crecimiento.', 'Los importes se calculan a partir de las ventas registradas.', 'Una venta anulada conserva su registro y deja de sumar a los acumulados vigentes.']),
        users: guide('Usuarios y permisos', ['Busca una cuenta o pulsa Nuevo usuario.', 'Completa los datos, rol y estado.', 'Para Empleado, asigna únicamente los permisos de pedidos, caja o servicios que necesite.', 'Guarda y revisa la cuenta en la lista.', 'No puedes desactivar tu propia cuenta administradora ni dejar el sistema sin un administrador activo.']),
        services: guide('Papelería y servicios', ['Busca por nombre o filtra por categoría.', 'Revisa descripción, precio orientativo y si se requieren originales.', 'Abre Solicitar servicio para explicar el trabajo.', 'El precio y la fecha definitivos se confirman en una cotización.']),
        serviceRequest: guide('Solicitar un servicio', ['Completa cantidad, descripción, teléfono y las opciones que correspondan.', 'Adjunta los documentos o indica que entregarás originales, según el servicio.', 'Envía la solicitud y consérvala en Mis solicitudes.', 'Espera la cotización y revísala antes de aceptarla.']),
        myServices: guide('Mis solicitudes', ['Busca el servicio por código o nombre y filtra por estado.', 'Abre su detalle para revisar cotización, mensajes y archivos.', 'Acepta la cotización para autorizar la preparación.', 'Consulta el avance hasta que esté listo para retirar.']),
        serviceDetail: guide('Detalle de tu servicio', ['Revisa la descripción, los archivos y las instrucciones.', 'Cuando recibas la cotización, comprueba importe y fecha antes de aceptar o rechazar.', 'Envía mensajes si necesitas aclarar el trabajo.', 'Descarga el resultado cuando esté disponible.', 'Acude a la tienda cuando el trabajo esté listo; allí se registra el pago y la entrega.']),
        serviceInbox: guide('Solicitudes de servicios', ['Filtra por estado o busca cliente, código o servicio.', 'Abre una solicitud y revisa lo pedido y sus archivos.', 'Registra la recepción de originales si se necesitan.', 'Envía la cotización y espera la aceptación del cliente.', 'Continúa la preparación y la entrega desde el detalle.']),
        serviceManage: guide('Catálogo y precios de servicios', ['Crea o edita categorías para organizar los servicios.', 'Completa nombre, descripción, unidad de cobro y tiempo estimado.', 'El precio es orientativo; déjalo vacío si siempre requiere cotización.', 'Indica si exige originales y si estará activo.', 'Abre Ver catálogo público para revisar lo que ve el cliente.']),
        serviceWork: guide('Atender un servicio', ['Revisa archivos, opciones, contacto y originales pendientes.', 'Envía una cotización con conceptos, importe y fecha.', 'Espera la aceptación antes de poner En proceso.', 'Responde mensajes y adjunta resultados durante la preparación.', 'Marca Lista para retirar y registra efectivo y entrega.', 'El administrador puede registrar un reembolso completo con su motivo.']),
        quote: guide('Cotización del servicio', ['Revisa los conceptos del trabajo, cantidades e importes.', 'El administrador envía la propuesta con fecha estimada.', 'El cliente debe aceptar el precio antes de iniciar la preparación.', 'Si el precio cambia, revisa la nueva cotización antes de continuar.']),
        files: guide('Archivos del servicio', ['Adjunta solo los documentos necesarios para el trabajo.', 'Se admiten PDF, JPG, PNG, WebP, Word y Excel sin macros; hasta 5 archivos, 10 MiB cada uno y 30 MiB en total.', 'Los archivos son privados para el cliente y el personal autorizado.', 'Los resultados se descargan desde el detalle cuando el personal los publique.'])
    };
    const route = () => {
        const path = location.pathname;
        return (base && (path === base || path.startsWith(base + '/')) ? path.slice(base.length) : path).replace(/\/$/,'') || '/';
    };
    const pageTopic = () => {
        const path = route();
        if (path === '/admin/catalog') return new URLSearchParams(location.search).get('kind') === 'brands' ? 'brands' : 'categories';
        if (path === '/orders/detail') return staff ? 'adminOrderDetail' : 'orderDetail';
        if (path === '/sales/detail') return staff ? 'sales' : 'receipt';
        if (path === '/services/detail') return staff ? 'serviceWork' : 'serviceDetail';
        return ({'/':'home','/store':'catalog','/product':'product','/checkout':'checkout','/orders':'orders','/purchases':'purchases','/profile':'profile',
            '/login':'login','/register':'register','/forgot-password':'recovery','/reset-password':'password','/auth/google/complete':'google',
            '/admin':'dashboard','/admin/products':'products','/admin/products/edit':'productForm','/admin/inventory':'inventory','/admin/orders':'adminOrders',
            '/admin/cash':'cash','/admin/sales':'sales','/admin/sales/reinvestment':'reinvestment','/admin/users':'users',
            '/services':'services','/services/request':'serviceRequest','/services/requests':'myServices','/admin/services':'serviceInbox','/admin/services/catalog':'serviceManage'})[path];
    };
    const panel = document.createElement('dialog');
    panel.className = 'context-help-panel'; panel.dataset.contextHelpPanel = ''; panel.id = 'context-help-panel';
    panel.setAttribute('aria-labelledby','context-help-title');
    const header = document.createElement('header'), heading = document.createElement('div'), eyebrow = document.createElement('small');
    eyebrow.textContent = 'Guía de uso';
    const title = document.createElement('h2'); title.id = 'context-help-title';
    const close = document.createElement('button'); close.type = 'button'; close.textContent = '×'; close.setAttribute('aria-label','Cerrar instrucciones');
    heading.append(eyebrow,title); header.append(heading,close);
    const content = document.createElement('div'); content.className = 'context-help-content'; content.tabIndex = 0; content.setAttribute('aria-label','Instrucciones paso a paso');
    panel.append(header,content); document.body.append(panel);
    let opener = null;
    const show = (topic, button) => {
        const selected = guides[topic]; if (!selected) return;
        title.textContent = selected.title; content.replaceChildren();
        const intro = document.createElement('p'); intro.textContent = 'Sigue estos pasos para utilizar esta parte del sistema.';
        const list = document.createElement('ol');
        selected.steps.forEach(step => { const item = document.createElement('li'); item.textContent = step; list.append(item); });
        content.append(intro,list);
        if (selected.tip) { const tip = document.createElement('p'); tip.className = 'context-help-tip'; tip.textContent = selected.tip; content.append(tip); }
        opener = button; if (!panel.open) panel.showModal();
        document.body.classList.add('context-help-open'); content.scrollTop = 0; close.focus();
    };
    close.addEventListener('click', () => panel.close());
    panel.addEventListener('keydown', event => {
        event.stopPropagation();
        if (event.key !== 'Tab') return;
        const targets = [...panel.querySelectorAll('button:not(:disabled),[tabindex="0"],a[href]')].filter(node => node.getClientRects().length);
        const first = targets[0], last = targets[targets.length - 1];
        if (event.shiftKey && (document.activeElement === first || !panel.contains(document.activeElement))) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && (document.activeElement === last || !panel.contains(document.activeElement))) { event.preventDefault(); first?.focus(); }
    });
    panel.addEventListener('close', () => { document.body.classList.remove('context-help-open'); if (opener?.isConnected) opener.focus({preventScroll:true}); });
    panel.addEventListener('click', event => {
        if (event.target !== panel) return;
        const rect = panel.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) panel.close();
    });
    const attach = (heading, topic) => {
        if (!guides[topic] || !heading || heading.closest('[data-context-help-panel]')) return;
        let button = [...heading.children].find(n => n.matches('button.context-help-trigger'));
        if (!button) {
            button = document.createElement('button'); button.type = 'button'; button.className = 'context-help-trigger';
            const symbol = document.createElement('span'); symbol.setAttribute('aria-hidden','true'); button.append(symbol); heading.append(button);
            button.setAttribute('aria-haspopup','dialog'); button.setAttribute('aria-controls',panel.id);
        }
        button.dataset.helpTopic = topic;
        button.setAttribute('aria-label','Instrucciones de ' + guides[topic].title); button.title = 'Cómo usar esta sección';
    };
    const modalTopic = modal => {
        if (modal.matches('[data-quick-entity-dialog]')) return modal.querySelector('[name=kind]')?.value === 'brands' ? 'newBrand' : 'newCategory';
        if (modal.matches('[data-catalog-modal]')) {
            if (modal.querySelector('[data-product-editor]')) return 'productForm';
            return modal.querySelector('[name=kind]')?.value === 'brands' ? 'brands' : 'categories';
        }
        if (modal.matches('[data-inventory-modal]')) return 'movement';
        if (modal.id === 'profile-password-modal') return 'password';
        if (modal.id === 'profile-edit-modal') return 'profile';
        if (modal.matches('[data-user-modal]')) return 'users';
        if (modal.classList.contains('service-dialog')) {
            const action = modal.querySelector('form')?.getAttribute('action') || '';
            if (action.endsWith('/quote')) return 'quote';
            if (action.endsWith('/result')) return 'files';
            return action.endsWith('/save') || action.endsWith('/category') ? 'serviceManage' : (staff ? 'serviceWork' : 'serviceDetail');
        }
        return pageTopic();
    };
    const refresh = () => {
        document.querySelectorAll('main h1').forEach(h => attach(h,pageTopic()));
        attach(document.querySelector('#catalog-title'),'catalog');
        attach(document.querySelector('#cart-drawer-title'),'cart'); attach(document.querySelector('#wishlist-drawer-title'),'favorites');
        document.querySelectorAll('dialog:not([data-context-help-panel]) h2').forEach(h => attach(h,modalTopic(h.closest('dialog'))));
        document.querySelectorAll('[data-product-editor] .catalog-card>h2').forEach(h => {
            const label = h.firstChild?.textContent.toLowerCase() || '';
            attach(h,label.includes('imagen') ? 'image' : label.includes('precio') ? 'prices' : 'productForm');
        });
        document.querySelectorAll('.service-panel>h2').forEach(h => {
            const label = h.firstChild?.textContent.toLowerCase() || '';
            attach(h,label.includes('archivo') || label.includes('resultado') ? 'files' : label.includes('cotiza') ? 'quote' : pageTopic());
        });
    };
    document.addEventListener('click', event => {
        const button = event.target.closest('button[data-help-topic]');
        if (button) { event.preventDefault(); show(button.dataset.helpTopic,button); }
    });
    let scheduled = false;
    const observer = new MutationObserver(records => {
        if (scheduled || records.every(record => panel.contains(record.target))) return;
        scheduled = true; requestAnimationFrame(() => { scheduled = false; refresh(); });
    });
    refresh(); observer.observe(document.body,{childList:true,subtree:true});
    document.addEventListener('admin:content-replaced',refresh);
})();
