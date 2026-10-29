const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('public/assets/js/order-scanner.js','utf8');
function setup({secure=true,failure=false,anchor=false}={}){
    const nodes=new Map();const node=()=>({value:'',hidden:true,disabled:false,listeners:{},addEventListener(name,fn){this.listeners[name]=fn;},replaceChildren(){}});
    for(const name of ['order-scan','scanner-error','camera-panel','camera-video','camera-select','camera-start','camera-stop'])nodes.set(name,node());
    const form=nodes.get('order-scan');form.action='http://localhost:8012/Tecno_Star/public/orders/scan';form.elements={codigo:{value:''}};form.dataset=anchor?{scanAnchor:'cash-payment'}:{};
    let callback,stops=0,opened=0,url=null;
    class Reader{async decodeFromVideoDevice(id,video,fn){opened++;if(failure)throw Object.assign(new Error(),{name:'NotAllowedError'});callback=fn;video.srcObject={getTracks:()=>[{stop(){stops++;}}],getVideoTracks:()=>[{getSettings:()=>({deviceId:'test-camera'})}]};return{stop(){stops++;}};}}
    const document={hidden:false,querySelector(selector){return nodes.get(selector.slice(6,-1));},createElement(){return{};},addEventListener(){}};
    const window={isSecureContext:secure,ZXingBrowser:{},addEventListener(){}};
    vm.runInNewContext(source,{document,window,navigator:{mediaDevices:{getUserMedia(){}}},URL,location:{href:form.action,assign(value){url=String(value);}},ZXingBrowser:{BrowserQRCodeReader:Reader,BrowserCodeReader:{listVideoInputDevices:async()=>[{deviceId:'test-camera',label:'Cámara de prueba'}]}}});
    return{nodes,form,get url(){return url;},get stops(){return stops;},get opened(){return opened;},decode(text){callback({getText:()=>text});}};
}
(async()=>{
    let test=setup();test.form.elements.codigo.value='https://malicioso.example/otra-ruta?codigo=TS-261018-ABCDEF123456';test.form.listeners.submit({preventDefault(){}});
    assert.equal(test.url,'http://localhost:8012/Tecno_Star/public/orders/scan?codigo=TS-261018-ABCDEF123456');
    test=setup({anchor:true});test.form.action='http://localhost:8012/Tecno_Star/public/admin/cash';test.form.elements.codigo.value='TS-261018-ABCDEF123456';test.form.listeners.submit({preventDefault(){}});assert.equal(test.url,'http://localhost:8012/Tecno_Star/public/admin/cash?codigo=TS-261018-ABCDEF123456#cash-payment');
    test=setup();test.form.elements.codigo.value='javascript:alert(1)';test.form.listeners.submit({preventDefault(){}});assert.equal(test.url,null);assert.equal(test.nodes.get('scanner-error').hidden,false);
    test=setup({secure:false});await test.nodes.get('camera-start').listeners.click();assert.equal(test.opened,0);assert.match(test.nodes.get('scanner-error').textContent,/HTTPS/);
    test=setup();await test.nodes.get('camera-start').listeners.click();assert.equal(test.opened,1);test.nodes.get('camera-stop').listeners.click();assert.ok(test.stops>=2);assert.equal(test.nodes.get('camera-panel').hidden,true);
    test=setup();await test.nodes.get('camera-start').listeners.click();test.decode('TS-261018-ABCDEF123456');assert.ok(test.stops>=2);assert.match(test.url,/codigo=TS-261018-ABCDEF123456$/);
    test=setup({failure:true});await test.nodes.get('camera-start').listeners.click();assert.match(test.nodes.get('scanner-error').textContent,/Permite el acceso/);
    console.log('OK: lector USB, enlaces externos neutralizados, contexto seguro, apagado de cámara, lectura y rechazo de permiso.');
})().catch(error=>{console.error(error);process.exitCode=1;});
