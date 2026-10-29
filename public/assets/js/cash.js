(() => {
    'use strict';
    const form=document.querySelector('[data-cash-form]');if(!form)return;
    const input=form.elements.recibido,review=form.querySelector('[data-cash-review]'),modal=form.querySelector('[data-cash-modal]'),confirm=form.querySelector('[data-cash-confirm]'),error=form.querySelector('[data-cash-error]'),panel=form.querySelector('[data-cash-pending]'),retry=form.querySelector('[data-cash-retry]'),check=form.querySelector('[data-cash-check]');
    const key='tecno-cash-'+form.dataset.owner+'-'+form.elements.id_pedido.value;
    const cents=value=>/^\d{1,10}(\.\d{1,2})?$/.test(value)?Number(value.split('.')[0])*100+Number((value.split('.')[1]||'').padEnd(2,'0')):null;
    const money=value=>'$'+(value/100).toFixed(2),total=cents(form.dataset.total);let busy=false,pending=null;
    try{const old=JSON.parse(sessionStorage.getItem(key));if(old && old.target_user_id===form.dataset.owner && old.id_pedido===form.elements.id_pedido.value && /^[a-f0-9]{64}$/.test(old.solicitud) && cents(old.recibido)!==null)pending=old;}catch{}
    const preview=()=>{const received=cents(input.value);form.querySelector('[data-cash-preview]').textContent=received!==null && received>=total?money(received-total):'—';};
    const controls=()=>{input.readOnly=!!pending;review.disabled=busy||!!pending;confirm.disabled=busy;check.disabled=busy;retry.disabled=busy;panel.hidden=!pending;};
    if(pending){input.value=pending.recibido;form.elements.solicitud.value=pending.solicitud;}preview();controls();input.addEventListener('input',preview);
    const fail=message=>{error.textContent=message;error.hidden=false;};
    const remember=()=>{try{sessionStorage.setItem(key,JSON.stringify(pending));return true;}catch{fail('El navegador no permite conservar la solicitud. Habilita el almacenamiento de esta página antes de cobrar.');return false;}};
    const forget=()=>{try{sessionStorage.removeItem(key);}catch{}pending=null;};
    const request=async(url,options={})=>{const controller=new AbortController(),timer=setTimeout(()=>controller.abort(),20000);try{const response=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'},...options,signal:controller.signal});const result=await response.json();return {response,result};}finally{clearTimeout(timer);}};
    const redirect=result=>{forget();try{sessionStorage.setItem('tecno-commerce-flash',result.message||'Cobro comprobado.');}catch{}const destination=new URL(result.location,location.href);if(destination.origin===location.origin && destination.pathname===location.pathname && destination.search===location.search)location.reload();else location.assign(result.location);};
    const send=async()=>{
        if(busy)return;if(!pending){pending=Object.fromEntries(new FormData(form));if(!remember()){pending=null;return;}}
        busy=true;controls();error.hidden=true;modal.close();retry.hidden=true;
        try{const {response,result}=await request(form.action,{method:'POST',body:new URLSearchParams({...pending,_csrf:form.elements._csrf.value})});if(response.ok && result.ok){redirect(result);return;}
            if([401,403,404,409,422].includes(response.status)){forget();if(response.status===409)fail((result.error||result.message||'El pedido cambió.')+' Actualiza el pedido antes de continuar.');else fail(result.error||result.message||'No se pudo registrar el cobro.');}
            else fail('No se pudo comprobar el resultado. Usa «Comprobar resultado» antes de volver a cobrar.');
        }catch{fail('Se perdió la respuesta del cobro. Comprueba el resultado para evitar repetirlo.');}finally{busy=false;controls();}
    };
    form.addEventListener('submit',event=>{event.preventDefault();if(busy||pending||!form.reportValidity())return;const received=cents(input.value);if(received===null||received<total){fail('El efectivo recibido no cubre el total.');return;}error.hidden=true;form.querySelector('[data-cash-confirm-received]').textContent=money(received);form.querySelector('[data-cash-confirm-change]').textContent=money(received-total);modal.showModal();});
    confirm.addEventListener('click',()=>{if(!busy && !pending && form.reportValidity())send();});
    form.querySelectorAll('[data-cash-close]').forEach(button=>button.addEventListener('click',()=>{if(!busy)modal.close();}));modal.addEventListener('cancel',event=>{if(busy)event.preventDefault();});
    check.addEventListener('click',async()=>{if(busy||!pending)return;busy=true;controls();error.hidden=true;try{const url=new URL(form.dataset.statusUrl,location.href);url.searchParams.set('solicitud',pending.solicitud);url.searchParams.set('id_pedido',pending.id_pedido);const {response,result}=await request(url);if(!response.ok || !result.ok)throw new Error(result.error||result.message||'No se pudo comprobar el cobro.');if(result.found||result.charged){redirect(result);return;}retry.hidden=false;fail('Todavía no hay un cobro registrado. Puedes reintentar con el mismo importe y la misma solicitud.');}catch(failure){fail(failure.message||'No se pudo comprobar el resultado. Conservamos la solicitud.');}finally{busy=false;controls();}});
    retry.addEventListener('click',()=>{if(pending)send();});
})();
