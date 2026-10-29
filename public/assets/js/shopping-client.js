/* Persistencia del carrito: confirmación del servidor, diario de reintentos y aislamiento por cuenta. */
(() => {
    'use strict';
    const copy=value=>JSON.parse(JSON.stringify(value));
    const key=()=>Array.from(crypto.getRandomValues(new Uint8Array(32)),byte=>byte.toString(16).padStart(2,'0')).join('');
    class ShoppingClient {
        constructor(options) {
            Object.assign(this,options); this.state={cart:[],wishlist:[],revision:0}; this.status='loading'; this.notice=''; this.busy=false; this.invalid=false;
            this.prefix=`tecno-star-shopping-user-${this.userId}`;
            this.pendingKey=this.prefix+'-pending'; this.cacheKey=this.prefix+'-cache';
            this.guestKey='tecno-star-shopping-guest'; this.transferKey='tecno-star-shopping-transfer';
            window.addEventListener('storage',event=>{
                if(event.key==='tecno-star-shopping-account' && event.newValue!==String(this.userId)) {this.invalidate();return;}
                if([this.userId?this.cacheKey:this.guestKey,this.pendingKey].includes(event.key) && !this.busy && !this.invalid) this.refresh();
            });
            window.addEventListener('pageshow',()=>{if(!this.busy) this.refresh();});
            document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible' && !this.busy) this.refresh();});
            this.ready=this.refresh();
        }
        read(name,fallback=null) {try {return JSON.parse(localStorage.getItem(name))??fallback;}catch{return fallback;}}
        write(name,value) {localStorage.setItem(name,JSON.stringify(value));}
        emit() {this.onChange(copy(this.state),this.status,this.notice);}
        invalidate() {this.invalid=true;this.state={cart:[],wishlist:[],revision:0};this.status='invalid';this.notice='La sesión cambió. Actualiza esta página antes de continuar.';this.emit();}
        async lock(task) {
            if(!navigator.locks?.request) throw new Error('Este navegador no permite coordinar las listas entre pestañas. Usa un navegador actualizado para guardar o sincronizar.');
            return navigator.locks.request('tecno-star-shopping',task);
        }
        async request(url,options={}) {
            const controller=new AbortController();const timeout=setTimeout(()=>controller.abort(),12000);
            try {const response=await fetch(url,{...options,credentials:'same-origin',cache:'no-store',signal:controller.signal});const data=await response.json();return {response,data};}
            finally {clearTimeout(timeout);}
        }
        accept(data) {
            if(this.invalid) throw new Error(this.notice);
            if(data.user_id!==this.userId || !Number.isSafeInteger(data.revision) || !Array.isArray(data.cart) || !Array.isArray(data.wishlist)) {this.invalidate();throw new Error(this.notice);}
            if(data.revision<this.state.revision) return;
            this.state=data;
            this.write(this.cacheKey,data);
            localStorage.setItem('tecno-star-shopping-account',String(this.userId));
        }
        async accountState() {
            const {response,data}=await this.request(this.stateUrl);
            if([401,403].includes(response.status)) {this.invalidate();throw new Error(this.notice);}
            if(!response.ok) throw new Error(data.error||'No se pudieron consultar tus listas.');
            this.accept(data);
        }
        async send(operation) {
            const {response,data}=await this.request(this.updateUrl,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':this.csrf},body:JSON.stringify(operation)});
            if([401,403].includes(response.status)) {this.invalidate();throw new Error(this.notice);}
            if(!response.ok) {
                if(data.state) this.accept(data.state);
                if([400,409,422].includes(response.status)) localStorage.removeItem(this.pendingKey);
                if(response.status===409 && !data.state) this.invalidate();
                throw new Error(data.error||'No se pudo confirmar el guardado.');
            }
            this.accept(data);localStorage.removeItem(this.pendingKey);
            this.notice=(data.warnings||[]).join(' ');return true;
        }
        async guestState() {
            let stored=this.read(this.guestKey);
            if(!stored) {
                stored={cart:this.read('tecno-star-cart',[]),wishlist:this.read('tecno-star-wishlist',[])};
                this.write(this.guestKey,stored);localStorage.removeItem('tecno-star-cart');localStorage.removeItem('tecno-star-wishlist');
            }
            const hydrate=async items=>{
                const result=[];
                for(const item of Array.isArray(items)?items.slice(0,200):[]) {
                    const id=Number(item.id??item.product_id??item.id_producto??item);
                    if(!Number.isSafeInteger(id)||id<1) continue;
                    const {response,data}=await this.request(this.productUrl+'?id='+id);
                    if(response.status===404) {result.push({...item,id,available:false,stock:0,name:item.name||'Producto no disponible',quantity:Number(item.quantity)||1,issues:['Este producto ya no está disponible.']});continue;}
                    if(!response.ok) throw new Error('No se pudieron actualizar los productos. Reintenta antes de continuar.');
                    const seen=item.seen_price??item.price??data.price;
                    const issues=[];if(Number(seen)!==Number(data.price)) issues.push(`El precio cambió a $${Number(data.price).toFixed(2)}.`);
                    const quantity=Math.max(1,Math.floor(Number(item.quantity??item.cantidad)||1));
                    if(quantity>Number(data.stock)) issues.push('La cantidad supera las existencias disponibles.');
                    result.push({...data,seen_price:seen,available:true,quantity,issues,price_changed:issues.some(text=>text.startsWith('El precio'))});
                }
                return result;
            };
            const cart=await hydrate(stored.cart),wishlist=await hydrate(stored.wishlist);
            if(this.invalid) throw new Error(this.notice);
            this.state={cart,wishlist,revision:0};
            this.write(this.guestKey,this.state);localStorage.setItem('tecno-star-shopping-account','0');
        }
        async transfer() {
            let batch=this.read(this.transferKey);
            if(batch && batch.owner!==this.userId) return;
            if(!batch) {
                const guest=this.read(this.guestKey)||{cart:this.read('tecno-star-cart',[]),wishlist:this.read('tecno-star-wishlist',[])};
                if(!guest.cart?.length && !guest.wishlist?.length) return;
                batch={owner:this.userId,operation:{action:'sync',operation_id:key(),target_user_id:this.userId,expected_revision:this.state.revision,cart:guest.cart.map(item=>({id:Number(item.id??item.id_producto),quantity:Number(item.quantity??item.cantidad)||1,price:item.seen_price??item.price})),wishlist:guest.wishlist.map(item=>({id:Number(item.id??item.id_producto??item)}))}};
                this.write(this.transferKey,batch);
                // La copia pertenece ya a esta cuenta, incluso si cambia la sesión antes de la respuesta.
                localStorage.removeItem(this.guestKey);localStorage.removeItem('tecno-star-cart');localStorage.removeItem('tecno-star-wishlist');
            }
            this.write(this.pendingKey,batch.operation);
            await this.send(batch.operation);localStorage.removeItem(this.transferKey);
        }
        async refresh() {
            if(this.busy || this.invalid) return false;
            this.busy=true;this.status='loading';this.notice='';this.emit();
            try {
                await this.lock(async()=>{
                    if(!this.userId) {
                        const {response}=await this.request(this.stateUrl);
                        if(response.ok) {this.invalidate();return;}
                        if(response.status!==401) throw new Error('No se pudo comprobar la sesión.');
                        await this.guestState();this.status='browser';return;
                    }
                    await this.accountState();
                    const pending=this.read(this.pendingKey);
                    if(pending) await this.send(pending);
                    await this.transfer();this.status='account';
                });
                this.emit();return true;
            } catch(error) {this.failure(error);return false;}
            finally {this.busy=false;}
        }
        failure(error) {
            if(!this.invalid) {this.status=this.read(this.pendingKey)?'pending':'error';this.notice=(error.name==='AbortError'?'No se pudo confirmar la respuesta.':error.message)+' '+(this.status==='pending'?'Hay un cambio pendiente. Pulsa Reintentar; no se duplicará.':'No se guardaron cambios. Revisa y vuelve a intentar.');}
            this.emit();
        }
        async mutate(mutation) {
            if(this.busy || this.invalid || this.status==='pending') return false;
            this.busy=true;this.status='saving';this.emit();let success=false;
            const expected=this.state.revision;
            try {
                await this.lock(async()=>{
                    if(!this.userId) {await this.guestMutation(mutation);this.status='browser';success=true;return;}
                    if(this.read(this.pendingKey)) throw new Error('Hay un cambio pendiente. Pulsa Reintentar.');
                    await this.accountState();
                    const {product,...payload}=mutation;
                    const operation={...payload,operation_id:key(),target_user_id:this.userId,expected_revision:expected};
                    this.write(this.pendingKey,operation);
                    success=await this.send(operation);this.status='account';
                });
                this.emit();return success;
            } catch(error) {this.failure(error);return false;}
            finally {this.busy=false;}
        }
        async guestMutation(mutation) {
            await this.guestState();const next=copy(this.state);const id=Number(mutation.product_id);
            let product=mutation.product;const item=next.cart.find(row=>Number(row.id)===id);
            if(['cart_add','wishlist_add'].includes(mutation.action)) {
                const {response,data}=await this.request(this.productUrl+'?id='+id);
                if(!response.ok) throw new Error('El producto ya no está disponible.');
                product={...data,seen_price:product?.price??data.price,available:true,issues:[]};
            }
            if(mutation.action==='cart_add') {
                const quantity=(item?.quantity||0)+Number(mutation.quantity||1);
                if(!product || product.available===false || quantity>Number(product.stock)) throw new Error('No hay existencias suficientes para esa cantidad.');
                if(item) item.quantity=quantity;else next.cart.push({...product,quantity});
            } else if(mutation.action==='cart_change') {
                if(!item) throw new Error('El producto ya no está en el carrito.');
                const quantity=item.quantity+mutation.delta;
                if(quantity>Number(item.stock)) throw new Error('No hay existencias suficientes.');
                if(quantity<=0) next.cart=next.cart.filter(row=>row!==item);else item.quantity=quantity;
            } else if(mutation.action==='cart_remove') next.cart=next.cart.filter(row=>Number(row.id)!==id);
            else if(mutation.action==='wishlist_add') {if(!next.wishlist.some(row=>Number(row.id)===id)) next.wishlist.push(product);}
            else if(mutation.action==='wishlist_remove') next.wishlist=next.wishlist.filter(row=>Number(row.id)!==id);
            else if(mutation.action==='cart_set_quantity') {if(!item || mutation.quantity<1 || mutation.quantity>item.stock) throw new Error('La cantidad no está disponible.');item.quantity=mutation.quantity;}
            else if(mutation.action==='cart_review') next.cart.forEach(row=>{row.seen_price=row.price;row.price_changed=false;row.issues=(row.issues||[]).filter(text=>!text.startsWith('El precio'));});
            this.write(this.guestKey,next);this.state=next;this.notice='';
        }
    }
    window.ShoppingClient=ShoppingClient;
})();
