(() => {
    const form=document.querySelector('[data-order-scan]');if(!form)return;
    const error=document.querySelector('[data-scanner-error]'),panel=document.querySelector('[data-camera-panel]'),video=document.querySelector('[data-camera-video]'),select=document.querySelector('[data-camera-select]'),start=document.querySelector('[data-camera-start]');
    let controls=null,reader=null,starting=false,generation=0,navigating=false;
    const show=message=>{error.textContent=message;error.hidden=false;};
    const stop=()=>{generation++;controls?.stop();controls=null;video.srcObject?.getTracks().forEach(track=>track.stop());video.srcObject=null;panel.hidden=true;start.disabled=false;};
    const open=value=>{
        if(navigating)return false;
        let code=String(value).trim();if(!/^TS-\d{6}-[A-F0-9]{12}$/.test(code)){try{code=new URL(code).searchParams.get('codigo')||'';}catch{}}
        if(!/^TS-\d{6}-[A-F0-9]{12}$/.test(code)){show('El QR o código no corresponde a un pedido de Tecno Star.');return false;}
        // Only use the extracted code. Never navigate to the URL supplied by a scanner.
        navigating=true;stop();const url=new URL(form.action,location.href);url.searchParams.set('codigo',code);if(form.dataset?.scanAnchor==='cash-payment')url.hash='cash-payment';location.assign(url);return true;
    };
    form.addEventListener('submit',event=>{event.preventDefault();open(form.elements.codigo.value);});
    const camera=async()=>{
        if(starting)return;if(!window.isSecureContext || !navigator.mediaDevices?.getUserMedia){show('La cámara necesita localhost o HTTPS. En la red Wi-Fi usa HTTPS o el lector USB.');return;}
        if(!window.ZXingBrowser){show('No se pudo cargar el lector de cámara. Recarga o utiliza el lector USB.');return;}
        stop();const session=generation;starting=true;start.disabled=true;error.hidden=true;panel.hidden=false;
        try{
            reader??=new ZXingBrowser.BrowserQRCodeReader();
            const active=await reader.decodeFromVideoDevice(select.value||undefined,video,result=>{if(result && session===generation)open(result.getText());});
            if(session!==generation){active.stop();return;}controls=active;
            const devices=await ZXingBrowser.BrowserCodeReader.listVideoInputDevices();
            if(session!==generation)return;
            const current=video.srcObject?.getVideoTracks()[0]?.getSettings().deviceId;
            select.replaceChildren(...devices.map((device,index)=>{const option=document.createElement('option');option.value=device.deviceId;option.textContent=device.label||`Cámara ${index+1}`;option.selected=device.deviceId===current;return option;}));
        }catch(failure){if(session===generation){stop();show(failure.name==='NotAllowedError'?'Permite el acceso a la cámara en el navegador.':'No se pudo abrir la cámara. Comprueba que esté conectada y libre.');}}
        finally{starting=false;if(!controls)start.disabled=false;}
    };
    start.addEventListener('click',camera);select.addEventListener('change',()=>{stop();camera();});
    document.querySelector('[data-camera-stop]').addEventListener('click',stop);
    window.addEventListener('pagehide',stop);document.addEventListener('visibilitychange',()=>{if(document.hidden)stop();});
})();
