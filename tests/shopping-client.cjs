const fs=require('node:fs');
const vm=require('node:vm');
const assert=require('node:assert/strict');
const {webcrypto}=require('node:crypto');
const source=fs.readFileSync('public/assets/js/shopping-client.js','utf8');
const storage=new Map();const ledger=new Map();const carts=new Map();
let account=1;let loseResponse=false;let tail=Promise.resolve();
const getCart=id=>{if(!carts.has(id)) carts.set(id,{user_id:id,revision:0,cart:[],wishlist:[]});return carts.get(id);};
const response=(status,data)=>({ok:status>=200&&status<300,status,json:async()=>JSON.parse(JSON.stringify(data))});
const fetch=async(url,options={})=>{
    if(url==='state') return account?response(200,getCart(account)):response(401,{error:'login'});
    if(url.startsWith('product')) return response(200,{id:1,name:'Producto',price:10,stock:20});
    const operation=JSON.parse(options.body);assert.equal(operation.target_user_id,account);
    let cart=getCart(account);
    if(!ledger.has(operation.operation_id)) {
        const quantity=operation.action==='sync'?operation.cart.reduce((n,row)=>n+row.quantity,0):Number(operation.quantity||1);
        cart.cart=[{id:1,name:'Producto',price:10,stock:20,quantity:(cart.cart[0]?.quantity||0)+quantity}];cart.revision++;
        ledger.set(operation.operation_id,JSON.stringify(operation));
    } else assert.equal(ledger.get(operation.operation_id),JSON.stringify(operation));
    if(loseResponse) {loseResponse=false;throw new TypeError('Respuesta perdida después del commit');}
    return response(200,{...cart,warnings:[]});
};
const makeClient=id=>{
    const events={};const context={console,crypto:webcrypto,fetch,AbortController,setTimeout,clearTimeout,JSON,Number,Map,Array,Error,window:{addEventListener:(name,handler)=>events[name]=handler},document:{addEventListener:()=>{}},navigator:{locks:{request:(name,task)=>{const next=tail.then(task);tail=next.catch(()=>{});return next;}}},localStorage:{getItem:key=>storage.get(key)??null,setItem:(key,value)=>storage.set(key,value),removeItem:key=>storage.delete(key)}};
    vm.createContext(context);vm.runInContext(source,context);
    let view;const client=new context.window.ShoppingClient({userId:id,stateUrl:'state',updateUrl:'update',productUrl:'product',csrf:'test',onChange:(state,status,notice)=>view={state,status,notice}});
    return {client,events,get view(){return view;}};
};
(async()=>{
    const first=makeClient(1);await first.client.ready;
    loseResponse=true;assert.equal(await first.client.mutate({action:'cart_add',product_id:1,quantity:2}),false);
    assert.equal(first.view.status,'pending');assert.equal(getCart(1).cart[0].quantity,2);
    assert.ok(storage.has('tecno-star-shopping-user-1-pending'));assert.match(first.view.notice,/Reintentar/);
    assert.equal(await first.client.refresh(),true);assert.equal(getCart(1).cart[0].quantity,2);assert.equal(first.view.status,'account');
    assert.ok(!storage.has('tecno-star-shopping-user-1-pending'));
    console.log('OK: respuesta perdida después del commit se reintenta sin duplicar y nunca muestra éxito falso.');
    storage.set('tecno-star-shopping-guest',JSON.stringify({cart:[{id:1,quantity:3}],wishlist:[]}));
    loseResponse=true;await first.client.refresh();assert.equal(first.view.status,'pending');assert.equal(getCart(1).cart[0].quantity,5);
    assert.equal(JSON.parse(storage.get('tecno-star-shopping-transfer')).owner,1);
    account=2;const second=makeClient(2);await second.client.ready;
    assert.equal(second.view.state.cart.length,0);assert.equal(getCart(2).cart.length,0);
    first.events.storage({key:'tecno-star-shopping-account',newValue:'2'});
    assert.equal(first.view.status,'invalid');assert.equal(first.view.state.cart.length,0);
    console.log('OK: cambiar de cuenta vacía la vista antigua y no importa el lote pendiente en otra cuenta.');
    account=1;const recovered=makeClient(1);await recovered.client.ready;
    assert.equal(getCart(1).cart[0].quantity,5);assert.ok(!storage.has('tecno-star-shopping-transfer'));
    console.log('OK: volver a la cuenta original recupera su lote sin sumarlo otra vez.');
    const otherTab=makeClient(1);await otherTab.client.ready;
    await Promise.all([recovered.client.mutate({action:'cart_add',product_id:1,quantity:1}),otherTab.client.mutate({action:'cart_add',product_id:1,quantity:1})]);
    assert.equal(getCart(1).cart[0].quantity,7);
    console.log('OK: dos pestañas coordinadas conservan ambos cambios consecutivos.');
})().catch(error=>{console.error(error);process.exitCode=1;});
