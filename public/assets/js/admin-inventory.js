(() => {
    'use strict';
    const dialog = document.querySelector('[data-inventory-modal]');
    const form = dialog?.querySelector('form');
    if (!form) return;
    let opener; let busy = false;
    const field = name => form.elements.namedItem(name);
    const error = form.querySelector('[data-inventory-error]');
    const toast = (message, failed = false) => {
        const item = document.createElement('div'); item.className = 'inventory-toast' + (failed?' inventory-toast--error':'');
        const text = document.createElement('span'); text.textContent = message;
        const close = document.createElement('button'); close.type='button'; close.textContent='×'; close.setAttribute('aria-label','Cerrar notificación'); close.onclick=()=>item.remove();
        item.append(text,close); document.querySelector('[data-inventory-toasts]').append(item);
        setTimeout(()=>item.remove(),10000);
    };
    const operation = () => {
        const type = field('tipo').value;
        const labels = {entrada:'Unidades que ingresan',salida:'Unidades que salen',ajuste:'Total físico contado',minimo:'Mínimo de unidades disponibles'};
        form.querySelector('[data-quantity-label]').textContent=labels[type];
        field('cantidad').min = ['entrada','salida'].includes(type)?'0.001':'0';
        form.querySelector('[data-inventory-help]').textContent = type==='ajuste'?'Se sustituye el total físico por el conteo indicado. No puede quedar por debajo del reservado.':type==='minimo'?'Se mostrará una alerta cuando el disponible sea igual o inferior a este mínimo.':'El disponible siempre es el físico menos el reservado. Las reservas se gestionarán desde los pedidos.';
    };
    field('tipo').addEventListener('change',() => { field('cantidad').value = field('tipo').value==='minimo'?form.dataset.minimum:''; operation(); });
    const close = () => { if (!busy) { dialog.close(); opener?.focus(); } };
    dialog.querySelectorAll('[data-inventory-close]').forEach(button=>button.addEventListener('click',close));
    dialog.addEventListener('cancel',event=>{ if(busy) event.preventDefault(); });
    dialog.addEventListener('click',event=>{ if(event.target===dialog && (event.clientX<dialog.getBoundingClientRect().left || event.clientX>dialog.getBoundingClientRect().right || event.clientY<dialog.getBoundingClientRect().top || event.clientY>dialog.getBoundingClientRect().bottom)) close(); });
    document.addEventListener('click',event=>{
        const history=event.target.closest('[data-inventory-history]');
        if(history) document.dispatchEvent(new CustomEvent('inventory:history',{detail:history.dataset.inventoryHistory}));
        const button=event.target.closest('[data-inventory-edit]');
        if(!button) return;
        opener=button; form.reset(); error.hidden=true;
        field('id_producto').value=button.dataset.id;
        field('solicitud').value=Array.from(crypto.getRandomValues(new Uint8Array(32)),byte=>byte.toString(16).padStart(2,'0')).join('');
        field('stock_esperado').value=button.dataset.stock;
        form.dataset.minimum=button.dataset.minimum;
        form.querySelector('[data-inventory-name]').textContent=button.dataset.name;
        const available=Number((Number(button.dataset.stock)-Number(button.dataset.reserved)).toFixed(3));
        form.querySelector('[data-inventory-balance]').textContent=`Físico: ${Number(button.dataset.stock)} · Reservado: ${Number(button.dataset.reserved)} · Disponible: ${available} · Mínimo: ${Number(button.dataset.minimum)}`;
        operation(); dialog.showModal(); field('tipo').focus();
    });
    form.addEventListener('submit',async event=>{
        event.preventDefault(); if(busy || !form.reportValidity()) return;
        busy=true; error.hidden=true;
        const data=new FormData(form); form.querySelectorAll('button').forEach(button=>button.disabled=true);
        const controller=new AbortController(); const timeout=setTimeout(()=>controller.abort(),20000);
        try {
            const response=await fetch(form.action,{method:'POST',body:data,headers:{Accept:'application/json'},credentials:'same-origin',signal:controller.signal});
            const result=await response.json();
            if(!response.ok || !result.ok) throw new Error(result.message || 'No se pudo guardar el movimiento.');
            busy=false; close(); toast(result.message);
            document.dispatchEvent(new Event('admin:reload-tables'));
            // Los indicadores se vuelven a consultar; nunca se calculan con una página parcial.
            try {
                const page=await fetch(location.href,{credentials:'same-origin',signal:controller.signal});
                if(!page.ok) throw new Error();
                const doc=new DOMParser().parseFromString(await page.text(),'text/html');
                const metrics=doc.querySelector('.admin-metrics');
                if(metrics) document.querySelector('.admin-metrics').replaceWith(metrics);
                const actors=doc.querySelector('[name="actor"]'); const current=document.querySelector('[name="actor"]');
                if(actors && current) { const selected=current.value; current.replaceChildren(...actors.children); current.value=selected; }
            } catch { toast('Movimiento guardado. Actualiza la página para revisar los indicadores.',true); }
        } catch(failure) {
            error.textContent=controller.signal.aborted?'No se pudo confirmar la respuesta. Reintenta sin cerrar el formulario: la misma solicitud no duplica el movimiento.':failure.message;
            error.hidden=false; error.tabIndex=-1; error.focus();
        } finally { clearTimeout(timeout); busy=false; form.querySelectorAll('button').forEach(button=>button.disabled=false); }
    });
})();
