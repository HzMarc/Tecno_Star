(() => {
    'use strict';
    const toasts=document.querySelector('[data-service-toasts]');
    const notify=message=>{
        if(!toasts || !message)return;
        const toast=document.createElement('div');
        toast.className='service-toast';toast.setAttribute('role','status');toast.setAttribute('aria-atomic','true');
        const icon=document.createElement('i');icon.className='bi bi-check-circle-fill';icon.setAttribute('aria-hidden','true');
        const text=document.createElement('span');text.textContent=message;
        const close=document.createElement('button');close.type='button';close.textContent='×';close.setAttribute('aria-label','Cerrar notificación');
        let timer;
        const dismiss=()=>{clearTimeout(timer);toast.remove();};
        const schedule=()=>{clearTimeout(timer);timer=setTimeout(dismiss,6500);};
        close.addEventListener('click',dismiss);
        toast.addEventListener('mouseenter',()=>clearTimeout(timer));
        toast.addEventListener('mouseleave',()=>{if(!toast.contains(document.activeElement))schedule();});
        toast.addEventListener('focusin',()=>clearTimeout(timer));
        toast.addEventListener('focusout',()=>{if(!toast.matches(':hover'))schedule();});
        toast.append(icon,text,close);toasts.append(toast);schedule();
    };
    try{
        const message=sessionStorage.getItem('tecno-service-flash');
        if(message){sessionStorage.removeItem('tecno-service-flash');notify(message);}
    }catch{}
    const dialogTriggers=new WeakMap();
    document.querySelectorAll('[data-service-open]').forEach(button=>button.addEventListener('click',event=>{
        const dialog=document.getElementById(button.dataset.serviceOpen);if(!dialog)return;
        if(button.dataset.serviceTarget){dialog.querySelector('[name="estado"]').value=button.dataset.serviceTarget;dialog.querySelector('[data-service-state-title]').textContent=button.textContent.trim();}
        dialogTriggers.set(dialog,{button,pointer:event.detail>0});dialog.showModal();
    }));
    document.querySelectorAll('.service-dialog').forEach(dialog=>{
        dialog.addEventListener('keydown',()=>{const trigger=dialogTriggers.get(dialog);if(trigger)trigger.pointer=false;});
        dialog.addEventListener('pointerdown',()=>{const trigger=dialogTriggers.get(dialog);if(trigger)trigger.pointer=true;});
        dialog.addEventListener('close',()=>{const trigger=dialogTriggers.get(dialog);if(trigger?.pointer && document.activeElement===trigger.button)trigger.button.blur();});
    });
    document.querySelectorAll('[data-service-close]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog')?.close()));
    const filter=document.querySelector('[data-service-filter]');
    filter?.addEventListener('submit',event=>{event.preventDefault();const term=filter.elements.q.value.trim().toLocaleLowerCase(),category=filter.elements.category.value;let found=0;document.querySelectorAll('[data-service-card]').forEach(card=>{card.hidden=!(card.dataset.name.toLocaleLowerCase().includes(term)&&(!category||card.dataset.category===category));if(!card.hidden)found++;});document.querySelector('[data-service-empty]').hidden=found>0;});
    filter?.querySelector('[data-service-clear]').addEventListener('click',()=>{filter.elements.q.value='';filter.elements.category.value='';filter.requestSubmit();});
    document.querySelectorAll('[data-service-form]').forEach(form=>{
        let busy=false;
        form.addEventListener('submit',async event=>{
            event.preventDefault();if(busy || !form.reportValidity())return;
            if(form.dataset.serviceConfirm && !window.confirm(form.dataset.serviceConfirm))return;
            const error=form.querySelector('[data-service-error]'),button=form.querySelector('[type=submit]');error.hidden=true;
            const files=[...(form.querySelector('[data-service-files]')?.files||[])];
            if(files.length>5 || files.some(file=>file.size>10485760) || files.reduce((n,file)=>n+file.size,0)>31457280){error.textContent='Adjunta hasta 5 archivos de 10 MiB por archivo y 30 MiB en total.';error.hidden=false;return;}
            const data=new FormData(form),label=button.textContent;busy=true;button.disabled=true;button.textContent='Guardando…';
            try{const response=await fetch(form.action,{method:'POST',credentials:'same-origin',headers:{Accept:'application/json'},body:data});const result=await response.json();if(!response.ok || !result.ok)throw new Error(result.error||'No se pudo guardar.');try{sessionStorage.setItem('tecno-service-flash',result.message||'Datos guardados.');}catch{}location.assign(result.location);}
            catch(failure){error.textContent=failure instanceof SyntaxError?'El servidor no pudo recibir el formulario. Revisa el tamaño de los adjuntos.':failure.message;error.hidden=false;}
            finally{busy=false;button.disabled=false;button.textContent=label;}
        });
        form.closest('dialog')?.addEventListener('cancel',event=>{if(busy)event.preventDefault();});
    });
    document.querySelector('[data-quote-add]')?.addEventListener('click',()=>{const container=document.querySelector('[data-quote-lines]');if(container.children.length>=20)return;const row=container.firstElementChild.cloneNode(true);row.querySelectorAll('input').forEach(input=>input.value=input.name==='unidades[]'?'1':'');container.append(row);});
    document.querySelector('[data-quote-lines]')?.addEventListener('click',event=>{const button=event.target.closest('[data-quote-remove]');if(button && button.closest('[data-quote-lines]').children.length>1)button.closest('.service-quote-line').remove();});
    const received=document.querySelector('[data-service-received]');received?.addEventListener('input',()=>{const cents=Math.round(Number(received.value)*100)-Math.round(Number(received.dataset.total)*100);document.querySelector('[data-service-change]').textContent=received.value && cents>=0?'$'+(cents/100).toFixed(2):'Importe insuficiente';});
    const table=document.querySelector('[data-service-table]');
    if(table && window.DataTable){
        const form=document.querySelector('[data-service-list-filter]');
        const error=document.querySelector('[data-service-table-error]');
        const states=[...document.querySelectorAll('[data-service-status]')];
        let controller,lastResponse;
        const syncFilters=()=>{
            states.forEach(link=>{if(link.dataset.serviceStatus===form.elements.status.value)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current');});
            const url=new URL(location.href);url.search='';if(form.elements.q.value.trim())url.searchParams.set('q',form.elements.q.value.trim());if(form.elements.status.value)url.searchParams.set('status',form.elements.status.value);history.replaceState(null,'',url);
        };
        const grid=new DataTable(table,{
            serverSide:true,processing:true,autoWidth:false,pageLength:12,lengthChange:false,ordering:false,searching:true,
            search:{search:form.elements.q.value},layout:{topStart:null,topEnd:null,bottomStart:'info',bottomEnd:'paging'},
            createdRow:row=>[...row.cells].forEach((cell,index)=>{cell.dataset.label=table.tHead.rows[0].cells[index].textContent.trim();}),
            ajax:async (data,callback)=>{
                controller?.abort();const active=new AbortController();controller=active;
                const timeout=setTimeout(()=>active.abort(),15000),url=new URL(table.dataset.url,location.href);
                url.searchParams.set('draw',String(data.draw));url.searchParams.set('start',String(data.start));url.searchParams.set('length',String(data.length));url.searchParams.set('search[value]',data.search.value);url.searchParams.set('status',form.elements.status.value);
                try{
                    const response=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'},signal:active.signal});
                    if(!response.ok)throw new Error(response.status===403?'Tu sesión o permisos cambiaron. Vuelve a iniciar sesión.':'No se pudieron cargar las solicitudes. Vuelve a intentarlo.');
                    const result=await response.json();if(!Array.isArray(result.data) || !Number.isInteger(result.recordsFiltered) || !Number.isInteger(result.recordsTotal))throw new Error('No se pudieron confirmar los datos de las solicitudes.');
                    if(controller!==active)return;lastResponse=result;error.hidden=true;callback(result);document.querySelector('[data-service-filter-count]').textContent=result.recordsFiltered+(result.recordsFiltered===1?' solicitud':' solicitudes');
                }catch(failure){
                    if(controller!==active)return;error.querySelector('span').textContent=failure.name==='TypeError' || active.signal.aborted?'No se pudieron cargar las solicitudes. Revisa tu conexión y vuelve a intentarlo.':failure.message;error.hidden=false;callback({...lastResponse||{recordsTotal:0,recordsFiltered:0,data:[]},draw:data.draw});
                }finally{clearTimeout(timeout);}
            },
            language:{info:'Mostrando _START_ a _END_ de _TOTAL_ solicitudes',infoEmpty:'Sin solicitudes',infoFiltered:'(de _MAX_ en total)',zeroRecords:'No hay solicitudes con estos filtros. Usa Limpiar para verlas todas.',emptyTable:'Todavía no hay solicitudes de servicios.',processing:'Cargando…',paginate:{first:'Primera',last:'Última',next:'Siguiente',previous:'Anterior'}}
        });
        const applyFilters=()=>{syncFilters();grid.search(form.elements.q.value.trim()).draw();};
        form.addEventListener('submit',event=>{event.preventDefault();applyFilters();});document.querySelector('[data-service-fallback]').hidden=true;
        form.elements.status.addEventListener('change',applyFilters);
        states.forEach(link=>link.addEventListener('click',event=>{if(event.ctrlKey || event.metaKey || event.shiftKey || event.altKey)return;event.preventDefault();form.elements.status.value=link.dataset.serviceStatus;applyFilters();}));
        form.querySelector('[data-service-clear]').addEventListener('click',event=>{if(event.ctrlKey || event.metaKey || event.shiftKey || event.altKey)return;event.preventDefault();form.elements.q.value='';form.elements.status.value='';applyFilters();});
        document.querySelector('[data-service-table-retry]').addEventListener('click',()=>grid.ajax.reload(null,false));
    }
})();
