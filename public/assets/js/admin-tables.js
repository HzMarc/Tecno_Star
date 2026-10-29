(() => {
    'use strict';
    const instances = new Map();
    const saved = new Map();
    if (typeof DataTable === 'undefined') return;
    DataTable.ext.errMode = 'none';
    const language = {
        emptyTable: 'No hay registros disponibles.', zeroRecords: 'No se encontraron resultados.',
        info: 'Mostrando _START_–_END_ de _TOTAL_ registros', infoEmpty: 'Sin registros para mostrar',
        infoFiltered: '(de _MAX_ en total)', lengthMenu: 'Mostrar _MENU_ filas',
        loadingRecords: 'Cargando…', processing: 'Consultando datos…', search: 'Buscar:',
        paginate: { first: 'Primera', previous: 'Anterior', next: 'Siguiente', last: 'Última' },
        aria: { orderable: 'Ordenar esta columna', orderableReverse: 'Invertir orden', orderableRemove: 'Quitar orden', paginate:{ first:'Primera página',previous:'Página anterior',next:'Página siguiente',last:'Última página' } },
    };
    const remember = (table, api) => {
        const filters = Object.fromEntries(new FormData(table.closest('.admin-table-panel').querySelector('form.catalog-filters') || document.createElement('form')));
        saved.set(table.dataset.tableKind, { search:api.search(), order:api.order(), length:api.page.len(), start:api.page.info().start, filters });
    };
    const init = () => document.querySelectorAll('table[data-admin-datatable]').forEach((table) => {
        if (instances.has(table)) return;
        const panel = table.closest('.admin-table-panel');
        const form = panel.querySelector('form.catalog-filters');
        const search = form?.querySelector('[name="q"]') || panel.querySelector('[data-admin-table-search]');
        const kind = table.dataset.tableKind;
        const state = saved.get(kind);
        if (state && form) Object.entries(state.filters).forEach(([key,value]) => {
            const field = form.elements.namedItem(key);
            if (field && field.type !== 'hidden') field.value = value;
        });
        if (state && search) search.value = state.search;
        const error = document.createElement('div');
        error.className = 'admin-table-error'; error.hidden = true; error.setAttribute('role','alert');
        const errorText = document.createElement('span');
        const retry = document.createElement('button'); retry.type = 'button'; retry.textContent = 'Reintentar';
        error.append(errorText,retry); panel.prepend(error);
        let controller = null; let lastResponse = null; let api; let timer;
        const tableOptions = {
            serverSide:true, processing:true, autoWidth:false,
            pageLength:state?.length || 12, lengthMenu:[12,25,50,100],
            displayStart:state?.start ?? Number(table.dataset.tableStart || 0),
            order:state?.order || (kind==='inventory' ? [[4,'asc']] : ['products','movements','orders','sales','purchases','users','reinvestment'].includes(kind) ? [] : [[0,'asc']]),
            search:{ search:state?.search ?? search?.value ?? '' },
            layout:{ topStart:'pageLength', topEnd:null, bottomStart:'info', bottomEnd:'paging' },
            language:table.hasAttribute('data-customer-table')?{...language,emptyTable:kind==='orders'?'Todavía no tienes pedidos.':'Todavía no tienes compras registradas.',zeroRecords:kind==='orders'?'No encontramos pedidos con esos filtros.':'No encontramos compras con esos filtros.',info:'Mostrando _START_–_END_ de _TOTAL_ '+(kind==='orders'?'pedidos':'compras'),infoEmpty:kind==='orders'?'Sin pedidos para mostrar':'Sin compras para mostrar'}:language,
            columnDefs:kind==='movements' ? [] : [{ targets:-1,orderable:false,searchable:false }],
            ajax:async (request, callback) => {
                controller?.abort(); controller = new AbortController();
                const active = controller;
                const timeout = setTimeout(() => active.abort('timeout'),15000);
                const url = new URL(table.dataset.tableUrl,location.href);
                url.searchParams.set('kind',kind);
                url.searchParams.set('draw',String(request.draw));
                url.searchParams.set('start',String(request.start));
                url.searchParams.set('length',String(request.length));
                url.searchParams.set('search[value]',request.search.value);
                url.searchParams.set('search[regex]','false');
                request.order.forEach((item,index) => {
                    url.searchParams.set(`order[${index}][column]`,String(item.column));
                    url.searchParams.set(`order[${index}][dir]`,item.dir);
                });
                if (form) ['bucket','category','brand','status','low','product','actor','type','from','to','method'].forEach((key) => {
                    const field = form.elements.namedItem(key);
                    if (field) url.searchParams.set(key,field.value);
                });
                try {
                    const response = await fetch(url,{ credentials:'same-origin',headers:{ Accept:'application/json' },signal:active.signal });
                    if (!response.ok) throw new Error(response.status===403 ? 'Tu sesión o permisos cambiaron. Vuelve a iniciar sesión.' : 'No se pudo cargar la tabla. Los datos anteriores pueden estar desactualizados.');
                    const data = await response.json();
                    if (!Array.isArray(data.data) || !Number.isInteger(data.recordsTotal) || !Number.isInteger(data.recordsFiltered)) throw new Error('El servidor no pudo confirmar los datos de la tabla.');
                    if (controller!==active || !table.isConnected) return;
                    lastResponse=data; error.hidden=true; callback(data);if(data.summary)panel.dispatchEvent(new CustomEvent('commerce:summary',{detail:data.summary,bubbles:true}));
                } catch (failure) {
                    if (controller!==active || !table.isConnected) return;
                    errorText.textContent = failure.name==='TypeError' || failure==='timeout' || active.signal.aborted
                        ? 'No se pudo consultar la tabla. Revisa la conexión e intenta nuevamente.' : failure.message;
                    error.hidden=false;
                    callback({ ...(lastResponse || { recordsTotal:0,recordsFiltered:0,data:[] }),draw:request.draw });
                } finally { clearTimeout(timeout); }
            },
            drawCallback:function () {
                const info=this.api().page.info();
                if (info.pages && info.page>=info.pages) this.api().page('last').draw('page');
            },
        };
        api = new DataTable(table,tableOptions);
        instances.set(table,{ api,abort:() => { clearTimeout(timer); controller?.abort(); },error });
        table.closest('[data-inventory-history-panel]')?.addEventListener('toggle',event=>{if(event.currentTarget.open)api.columns.adjust();});
        panel.classList.add('admin-table-panel--enhanced');
        retry.addEventListener('click',() => api.ajax.reload(null,false));
        search?.addEventListener('input',() => {
            clearTimeout(timer); timer=setTimeout(() => api.search(search.value).draw(),300);
        });
        form?.addEventListener('submit',(event) => {
            event.preventDefault(); clearTimeout(timer); api.search(search?.value || '').draw();
        });
        form?.querySelectorAll('select').forEach((field) => field.addEventListener('change',() => api.ajax.reload()));
        form?.addEventListener('reset',() => { clearTimeout(timer); setTimeout(()=>{form.querySelectorAll('input:not([type="hidden"]),select').forEach(field=>{field.value='';});api.search('').draw();},0); });
        panel.querySelector('[data-table-refresh]')?.addEventListener('click',() => api.ajax.reload(null,false));
    });
    document.addEventListener('admin:before-content-replace',() => {
        instances.forEach(({ api,abort,error },table) => { remember(table,api); abort(); api.destroy(); error.remove(); });
        instances.clear();
    });
    document.addEventListener('admin:content-replaced',init);
    document.addEventListener('admin:reload-tables',() => instances.forEach(({api}) => api.ajax.reload(null,false)));
    document.addEventListener('inventory:history',(event) => {
        const table = document.querySelector('[data-table-kind="movements"]');
        if (!table) return;
        const form = table.closest('.admin-table-panel').querySelector('form');
        const history = table.closest('[data-inventory-history-panel]');
        if (history) history.open = true;
        form.elements.namedItem('product').value = String(event.detail);
        instances.get(table)?.api.ajax.reload();
        table.closest('section').scrollIntoView({behavior:'smooth',block:'start'});
    });
    init();
})();
