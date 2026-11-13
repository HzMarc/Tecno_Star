(() => {
    const toast=document.querySelector('[data-commerce-toasts]');
    const notify=text=>{if(!toast)return;const item=document.createElement('div');item.className='order-toast';item.textContent=text;toast.replaceChildren(item);setTimeout(()=>item.remove(),5500);};
    const flash=sessionStorage.getItem('tecno-commerce-flash');if(flash){sessionStorage.removeItem('tecno-commerce-flash');notify(flash);}
    document.addEventListener('commerce:summary',event=>{const money=value=>'$'+Number(value).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});const paid=document.querySelector('[data-sales-paid]'),voided=document.querySelector('[data-sales-voided]');if(paid)paid.textContent=money(event.detail.paid);if(voided)voided.textContent=money(event.detail.voided);});
    document.querySelectorAll('[data-commerce-open]').forEach(button=>button.addEventListener('click',()=>document.getElementById(button.dataset.commerceOpen)?.showModal()));
    document.querySelectorAll('[data-commerce-close]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog')?.close()));
    document.querySelectorAll('[data-commerce-form]').forEach(form=>{
        let busy=false;form.addEventListener('submit',async event=>{
            event.preventDefault();if(busy || !form.reportValidity())return;busy=true;const submit=form.querySelector('[type=submit]'),error=form.querySelector('[data-commerce-error]');submit.dataset.loadingLabel=submit.innerHTML;submit.innerHTML='<span class="order-loading-spinner" aria-hidden="true"></span><span>Guardando…</span>';submit.disabled=true;error.hidden=true;
            const controller=new AbortController(),timer=setTimeout(()=>controller.abort(),20000);
            try{const response=await fetch(form.action,{method:'POST',credentials:'same-origin',headers:{Accept:'application/json'},body:new FormData(form),signal:controller.signal});const result=await response.json();if(!response.ok || !result.ok)throw new Error(result.error||'No se pudo guardar.');
                form.querySelectorAll('[type=password]').forEach(field=>field.value='');if(form.hasAttribute('data-user-form')){form.closest('dialog')?.close();document.dispatchEvent(new CustomEvent('admin:reload-tables'));notify(result.message);}else if(result.location){sessionStorage.setItem('tecno-commerce-flash',result.message);location.assign(result.location);}else notify(result.message);
            }catch(failure){error.textContent=failure.name==='AbortError'?'No se pudo confirmar la respuesta. Reintenta sin cambiar los datos; no se duplica la operación.':failure.message;error.hidden=false;}
            finally{clearTimeout(timer);busy=false;submit.disabled=false;if(submit.dataset.loadingLabel)submit.innerHTML=submit.dataset.loadingLabel;}
        });form.closest('dialog')?.addEventListener('cancel',event=>{if(busy)event.preventDefault();});
    });
    const userModal=document.querySelector('[data-user-modal]'),userForm=document.querySelector('[data-user-form]');
    const permission=()=>{if(userForm){const employee=userForm.elements.rol.value==='Empleado';userForm.elements.pedidos.disabled=!employee;if(userForm.elements.servicios)userForm.elements.servicios.disabled=!employee;const serviceRow=userForm.querySelector('[data-user-service-permission]');if(serviceRow)serviceRow.hidden=!employee;const row=userForm.querySelector('[data-user-permission]');if(row)row.hidden=!employee;const help=userForm.querySelector('[data-employee-access-help]');if(help)help.hidden=!employee;}};userForm?.elements.rol.addEventListener('change',permission);userModal?.addEventListener('toggle',permission);
    const prepare=()=>{userForm.reset();userForm.elements.id.value='';userForm.elements.revision.value='';userForm.querySelector('[data-commerce-error]').hidden=true;permission();};
    document.querySelectorAll('[data-user-new]').forEach(button=>button.addEventListener('click',()=>{prepare();const role=button.dataset.userNew==='Empleado'?'Empleado':'Cliente';userForm.elements.rol.value=role;userForm.elements.correo.readOnly=false;userForm.querySelector('[data-user-password]').hidden=false;['nueva','confirmacion'].forEach(name=>userForm.elements[name].required=true);userModal.querySelector('h2').textContent=role==='Empleado'?'Nuevo empleado':'Nuevo usuario';permission();userModal.showModal();}));
    document.addEventListener('click',async event=>{const button=event.target.closest('[data-user-edit]');if(!button || !userForm)return;button.disabled=true;
        try{const response=await fetch(userForm.dataset.editorUrl+'?id='+encodeURIComponent(button.dataset.userEdit),{headers:{Accept:'application/json'},credentials:'same-origin'});const result=await response.json();if(!response.ok)throw new Error(result.error||'No se pudo abrir la cuenta.');prepare();const user=result.user;for(const name of ['nombres','apellidos','telefono','correo','estado'])userForm.elements[name].value=user[name]||'';userForm.elements.id.value=user.id_usuario;userForm.elements.revision.value=user.cuenta_revision;userForm.elements.rol.value=user.nombre_rol;userForm.elements.pedidos.checked=user.pedidos==='1';if(userForm.elements.servicios)userForm.elements.servicios.checked=user.servicios==='1';userForm.elements.correo.readOnly=true;userForm.querySelector('[data-user-password]').hidden=true;['nueva','confirmacion'].forEach(name=>userForm.elements[name].required=false);userModal.querySelector('h2').textContent='Editar usuario';permission();userModal.showModal();}catch(failure){notify(failure.message);}finally{button.disabled=false;}
    });
    const historyModal=document.querySelector('[data-history-modal]');
    let historyRequest=null;
    const historyMoney=value=>'$'+Number(value||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    const historyDate=value=>{if(!value)return'—';const date=new Date(String(value).replace(' ','T')+'Z');return Number.isNaN(date.getTime())?'—':new Intl.DateTimeFormat('es-SV',{dateStyle:'medium',timeStyle:'short',timeZone:'America/El_Salvador'}).format(date);};
    const historyCell=(row,value,tag='td')=>{const cell=document.createElement(tag);cell.textContent=String(value??'—');row.append(cell);return cell;};
    const historyLink=(row,base,id,label)=>{const cell=document.createElement('td'),link=document.createElement('a');const url=new URL(base,location.href);url.searchParams.set('id',String(id));link.href=url.href;link.className='history-link';link.textContent=label;cell.append(link);row.append(cell);};
    const historyEmpty=(body,message,columns)=>{const row=document.createElement('tr'),cell=document.createElement('td');cell.colSpan=columns;cell.className='history-empty';cell.textContent=message;row.append(cell);body.append(row);};
    document.addEventListener('click',async event=>{
        const button=event.target.closest('[data-user-history]');
        if(!button||!historyModal)return;
        historyRequest?.abort();historyRequest=new AbortController();
        const signal=historyRequest.signal,loading=historyModal.querySelector('[data-history-loading]'),error=historyModal.querySelector('[data-history-error]'),content=historyModal.querySelector('[data-history-content]');
        loading.hidden=false;error.hidden=true;content.hidden=true;error.textContent='';
        historyModal.querySelector('[data-history-customer]').textContent='';
        historyModal.querySelector('[data-history-purchase-rows]').replaceChildren();historyModal.querySelector('[data-history-service-rows]').replaceChildren();
        historyModal.showModal();button.disabled=true;
        try{
            const url=new URL(historyModal.dataset.historyUrl,location.href);url.searchParams.set('id',button.dataset.userHistory);
            const response=await fetch(url,{headers:{Accept:'application/json'},credentials:'same-origin',signal});const result=await response.json();
            if(!response.ok||!result.ok)throw new Error(result.error||'No se pudo cargar el historial.');
            const customer=result.customer,summary=result.summary;
            historyModal.querySelector('[data-history-customer]').textContent=`${customer.nombres} ${customer.apellidos} · ${customer.correo}`;
            historyModal.querySelector('[data-history-purchases-count]').textContent=String(summary.purchases);
            historyModal.querySelector('[data-history-purchases-paid]').textContent=historyMoney(summary.purchases_paid);
            historyModal.querySelector('[data-history-refunds]').textContent=historyMoney(summary.refunds);
            historyModal.querySelector('[data-history-services-count]').textContent=String(summary.services);
            historyModal.querySelector('[data-history-services-paid]').textContent=historyMoney(summary.services_paid);
            const purchaseBody=historyModal.querySelector('[data-history-purchase-rows]');
            for(const purchase of result.purchases){
                const row=document.createElement('tr'),receipt=historyCell(row,'');receipt.replaceChildren();const saleCode=document.createElement('strong'),orderCode=document.createElement('small');saleCode.textContent=purchase.codigo_venta||'Venta';orderCode.textContent=purchase.codigo_pedido||'Pedido';receipt.append(saleCode,orderCode);const products=historyCell(row,purchase.productos);products.className='history-products';historyCell(row,historyDate(purchase.fecha_pago));historyCell(row,historyMoney(purchase.total));
                const state=historyCell(row,purchase.estado==='anulada'?'Reembolsada':'Pagada');state.className='history-status '+(purchase.estado==='anulada'?'history-status--refunded':'history-status--paid');historyLink(row,historyModal.dataset.saleUrl,purchase.id_venta,'Ver compra');purchaseBody.append(row);
            }
            if(!result.purchases.length)historyEmpty(purchaseBody,'Este cliente aún no tiene compras cobradas.',6);
            const serviceBody=historyModal.querySelector('[data-history-service-rows]');
            const states={nueva:'Nueva',en_cotizacion:'En cotización',cotizada:'Cotizada',aceptada:'Aceptada',en_proceso:'En proceso',lista:'Lista',entregada:'Entregada',cancelada:'Cancelada'};
            for(const service of result.services){
                const row=document.createElement('tr');historyCell(row,service.codigo);historyCell(row,`${service.nombre_servicio} · ${service.cantidad} ${service.unidad}`);historyCell(row,historyDate(service.created_at));const total=service.paid_total??service.total;historyCell(row,total===null?'Por cotizar':historyMoney(total));
                let status=states[service.estado]||service.estado;if(service.sale_state==='pagada')status+=' · Pagado';else if(service.sale_state==='anulada')status+=' · Reembolsado';
                const state=historyCell(row,status);state.className='history-status '+(service.sale_state==='anulada'?'history-status--refunded':service.sale_state==='pagada'?'history-status--paid':'');
                historyLink(row,historyModal.dataset.serviceUrl,service.id_solicitud,'Ver solicitud');serviceBody.append(row);
            }
            if(!result.services.length)historyEmpty(serviceBody,'Este cliente aún no tiene solicitudes de servicio.',6);
            content.hidden=false;
        }catch(failure){if(!signal.aborted){error.textContent=failure.message||'No se pudo cargar el historial.';error.hidden=false;}}
        finally{if(!signal.aborted){loading.hidden=true;button.disabled=false;}}
    });
    const received=document.querySelector('[data-cash-received]'),change=document.querySelector('[data-cash-change]');
    received?.addEventListener('input',()=>{const total=Math.round(Number(received.dataset.total)*100),cash=Math.round(Number(received.value)*100);change.textContent=Number.isFinite(cash)&&cash>=total?'$'+((cash-total)/100).toFixed(2):'Importe insuficiente';});
})();
