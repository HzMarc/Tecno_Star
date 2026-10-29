(() => {
    const toast=document.querySelector('[data-order-toasts]');
    const notify=message=>{const item=document.createElement('div');item.className='order-toast';item.textContent=message;toast?.replaceChildren(item);setTimeout(()=>item.remove(),5500);};
    const flash=sessionStorage.getItem('tecno-order-flash');if(flash){sessionStorage.removeItem('tecno-order-flash');notify(flash);}
    const modal=document.querySelector('[data-order-modal]');
    document.querySelectorAll('[data-order-open]').forEach(button=>button.addEventListener('click',()=>{modal.querySelector('[name=estado]').value=button.dataset.orderOpen;modal.querySelector('h2').textContent=button.dataset.actionLabel;modal.querySelector('[type=submit]').textContent=button.dataset.actionLabel;modal.querySelector('[data-order-action-description]').textContent=button.dataset.orderOpen==='cancelado'?'Se cancelará el pedido y se liberará la reserva. Explica el motivo.':'El cambio quedará en el seguimiento del pedido.';const reason=modal.querySelector('[name=motivo]');reason.value=button.dataset.orderOpen==='cancelado'?'':button.dataset.actionLabel;modal.showModal();}));
    document.querySelectorAll('[data-order-close]').forEach(button=>button.addEventListener('click',()=>modal.close()));
    if(location.hash==='#cancel')document.querySelector('[data-order-open="cancelado"]')?.click();
    document.querySelector('[data-order-refresh]')?.addEventListener('click',()=>location.reload());
    document.querySelectorAll('[data-order-form]').forEach(form=>{
        const checkout=form.hasAttribute('data-order-checkout'),key='tecno-order-pending-'+form.dataset.owner;
        const error=form.querySelector('[data-order-error]'),submit=form.querySelector('[type=submit]'),retry=form.querySelector('[data-order-retry]');
        let busy=false,pending=null;
        if(checkout){try{pending=JSON.parse(sessionStorage.getItem(key));}catch{}if(pending){retry.hidden=false;submit.disabled=true;}}
        const send=async body=>{
            if(busy)return;busy=true;submit.disabled=true;submit.dataset.loadingLabel=submit.innerHTML;submit.innerHTML='<span class="order-loading-spinner" aria-hidden="true"></span><span>'+(checkout?'Confirmando pedido…':'Guardando cambio…')+'</span>';error.hidden=true;
            if(checkout){pending=body;sessionStorage.setItem(key,JSON.stringify(body));retry.hidden=false;}
            const controller=new AbortController(),timer=setTimeout(()=>controller.abort(),20000);
            try{
                // Refresh the CSRF token, keeping the original purchase payload for uncertain retries.
                body={...body,_csrf:form.elements._csrf.value};
                const response=await fetch(form.action,{method:'POST',credentials:'same-origin',headers:{Accept:'application/json'},body:new URLSearchParams(body),signal:controller.signal});
                const result=await response.json();
                if(!response.ok || !result.ok){
                    if(checkout && [401,403,404,409,422].includes(response.status)){sessionStorage.removeItem(key);pending=null;retry.hidden=true;}
                    throw new Error(result.error||result.message||'No se pudo completar la operación.');
                }
                if(checkout)sessionStorage.removeItem(key);
                sessionStorage.setItem('tecno-order-flash',result.message);
                const destination=new URL(result.location,location.href);
                // A fragment navigation alone does not refresh the newly prepared order.
                if(destination.origin===location.origin && destination.pathname===location.pathname && destination.search===location.search)location.reload();else location.assign(result.location);
            }catch(failure){error.textContent=failure.name==='AbortError'?'La respuesta tardó demasiado. Reintenta para comprobar el resultado.':(failure.message||'No se pudo comprobar la respuesta. Reintenta.');error.hidden=false;}
            finally{clearTimeout(timer);busy=false;submit.disabled=!!pending;if(!pending&&submit.dataset.loadingLabel)submit.innerHTML=submit.dataset.loadingLabel;}
        };
        form.addEventListener('submit',event=>{event.preventDefault();if(!form.reportValidity())return;send(Object.fromEntries(new FormData(form)));});
        form.querySelector('[data-order-resume]')?.addEventListener('click',()=>{if(pending)send(pending);});
        modal?.addEventListener('cancel',event=>{if(busy)event.preventDefault();});
    });
})();
