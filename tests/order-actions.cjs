const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
async function check(target){
    const storage=new Map(),listeners={};let reloaded=0,assigned=null;
    const error={hidden:true},submit={disabled:false,dataset:{},innerHTML:'Guardar cambio'};
    const form={dataset:{},elements:{_csrf:{value:'csrf'}},action:'http://localhost/orders/state',hasAttribute:()=>false,reportValidity:()=>true,querySelector:s=>s==='[data-order-error]'?error:s==='[type=submit]'?submit:null,addEventListener:(name,fn)=>listeners[name]=fn};
    const location=new URL('http://localhost/admin/cash?id=7#cash-payment');location.reload=()=>reloaded++;location.assign=value=>assigned=value;
    class FormData{constructor(){return [['id','7'],['estado','listo']];}}
    vm.runInNewContext(fs.readFileSync('public/assets/js/orders.js','utf8'),{document:{querySelector:()=>null,querySelectorAll:s=>s==='[data-order-form]'?[form]:[]},location,sessionStorage:{getItem:k=>storage.get(k),setItem:(k,v)=>storage.set(k,v),removeItem:k=>storage.delete(k)},FormData,URL,URLSearchParams,AbortController,setTimeout,clearTimeout,fetch:async()=>({ok:true,json:async()=>({ok:true,message:'Estado actualizado.',location:target})})});
    listeners.submit({preventDefault(){}});await new Promise(resolve=>setImmediate(resolve));assert.equal(submit.disabled,false);assert.equal(submit.innerHTML,'Guardar cambio');return {reloaded,assigned,flash:storage.get('tecno-order-flash')};
}
(async()=>{assert.deepEqual(await check('/admin/cash?id=7#cash-payment'),{reloaded:1,assigned:null,flash:'Estado actualizado.'});assert.deepEqual(await check('/orders/detail?id=7'),{reloaded:0,assigned:'/orders/detail?id=7',flash:'Estado actualizado.'});console.log('OK: preparar en Caja recarga los datos aunque la respuesta conserve la misma URL con fragmento; otros destinos navegan correctamente.');})().catch(error=>{console.error(error);process.exitCode=1;});
