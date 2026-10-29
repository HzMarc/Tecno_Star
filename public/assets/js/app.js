'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const toastElement = document.querySelector('#auth-toast');
    const toastMessage = document.querySelector('[data-toast-message]');
    const showToast = (message, type = 'error') => {
        if (!toastElement || !toastMessage || typeof bootstrap === 'undefined') {
            const toasts = document.querySelector('[data-shopping-toasts]');
            if (toasts) {
                const toast = document.createElement('div');
                toast.className = 'shopping-toast shopping-toast--' + type;
                toast.textContent = message;
                toasts.replaceChildren(toast);
                window.setTimeout(() => toast.remove(), 5500);
                return;
            }
            const feedback = document.querySelector('[data-shopping-feedback]');
            if (feedback) { feedback.textContent = message; feedback.dataset.kind = type; }
            return;
        }

        const toastTypes = {
            error: {
                title: 'Error',
                icon: 'bi bi-x-circle-fill',
                className: 'auth-toast--error',
            },
            warning: {
                title: 'Advertencia',
                icon: 'bi bi-exclamation-triangle-fill',
                className: 'auth-toast--warning',
            },
            success: {
                title: 'Éxito',
                icon: 'bi bi-check-circle-fill',
                className: 'auth-toast--success',
            },
        };
        const toastType = toastTypes[type] || toastTypes.error;
        const toastIcon = toastElement.querySelector('[data-toast-icon]');
        const toastTitle = toastElement.querySelector('[data-toast-title]');

        toastElement.classList.remove(
            'auth-toast--error',
            'auth-toast--warning',
            'auth-toast--success'
        );
        toastElement.classList.add(toastType.className);
        if (toastIcon) toastIcon.className = `${toastType.icon} me-2`;
        if (toastTitle) toastTitle.textContent = toastType.title;
        toastMessage.textContent = message;
        bootstrap.Toast.getOrCreateInstance(toastElement).show();
    };

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const input = document.getElementById(toggle.dataset.target);
            const icon = toggle.querySelector('i');
            if (!input || !icon) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            toggle.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    document.querySelectorAll('[data-auth-form]').forEach((form) => {
        const password = form.querySelector('input[name="password"]');
        const confirmation = form.querySelector('[data-password-confirmation]');
        const validateConfirmation = () => {
            if (!password || !confirmation) return;
            confirmation.setCustomValidity(
                confirmation.value !== '' && confirmation.value !== password.value
                    ? 'Las contraseñas no coinciden.'
                    : ''
            );
        };

        form.querySelectorAll('input').forEach((input) => {
            input.addEventListener('input', () => {
                validateConfirmation();
                input.classList.toggle('is-invalid', !input.checkValidity());
                input.classList.toggle('is-valid', input.value !== '' && input.checkValidity());
            });
        });

        form.addEventListener('submit', (event) => {
            validateConfirmation();
            if (form.checkValidity()) return;
            event.preventDefault();
            form.classList.add('was-validated');
            showToast('Completa los campos correctamente para continuar.', 'error');
        });
    });

    const serverMessage = document.querySelector('[data-server-message]');
    if (serverMessage) {
        showToast(
            serverMessage.dataset.serverMessage,
            serverMessage.dataset.messageType || 'error'
        );
    }

    const catalogSearch = document.querySelector('[data-catalog-search]');
    const catalogSearchNav = document.querySelector('[data-catalog-search-nav]');
    const catalogFilter = document.querySelector('[data-catalog-filter]');
    const categoryChoices = [...document.querySelectorAll('[data-category-choice]')];
    const catalogSort = document.querySelector('[data-catalog-sort]');
    const catalogAvailable = document.querySelector('[data-catalog-available]');
    const catalogGrid = document.querySelector('[data-catalog-grid]');
    catalogSearchNav?.closest('form')?.addEventListener('submit', (event) => {
        if (!catalogGrid) return;
        event.preventDefault();
        document.querySelector('#catalog-title')?.focus();
    });
    const productCards = [...document.querySelectorAll('[data-product-card]')];
    const catalogEmpty = document.querySelector('[data-catalog-empty]');
    let selectedCategory = '';
    const normalizeSearch = (value) => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
    const filterCatalog = () => {
        if (!catalogGrid || !catalogEmpty) return;
        const query = normalizeSearch(catalogSearch?.value || catalogSearchNav?.value);
        const category = catalogFilter?.value || selectedCategory;
        const sort = catalogSort?.value || '';
        const availableOnly = Boolean(catalogAvailable?.checked);
        let visibleProducts = 0;
        productCards.forEach((card) => {
            const visible = (!query || normalizeSearch(card.dataset.search).includes(query)) &&
                (!category || card.dataset.category === category) &&
                (!availableOnly || Number(card.dataset.stock) > 0);
            card.hidden = !visible;
            card.classList.toggle('d-none', !visible);
            if (visible) visibleProducts += 1;
        });
        const sorted = [...productCards].sort((a, b) => {
            const fallback = Number(a.dataset.catalogOrder || 0) - Number(b.dataset.catalogOrder || 0);
            if (sort === 'price-asc') return Number(a.dataset.price) - Number(b.dataset.price) || fallback;
            if (sort === 'price-desc') return Number(b.dataset.price) - Number(a.dataset.price) || fallback;
            if (sort === 'newest') return Number(b.dataset.created || 0) - Number(a.dataset.created || 0) || fallback;
            if (sort === 'name') return String(a.dataset.search).localeCompare(String(b.dataset.search), 'es') || fallback;
            return fallback;
        });
        sorted.forEach((card) => catalogGrid.appendChild(card));
        catalogEmpty.hidden = visibleProducts > 0;
        const noStock = availableOnly && productCards.every((card) => Number(card.dataset.stock) <= 0);
        const emptyTitle = catalogEmpty.querySelector('h3');
        if (emptyTitle) emptyTitle.textContent = noStock ? 'No hay existencias disponibles por ahora' : 'No encontramos coincidencias';
        const count = document.querySelector('[data-catalog-result-count]');
        if (count) count.textContent = `${visibleProducts} ${visibleProducts === 1 ? 'producto' : 'productos'}`;
        const title = document.querySelector('#catalog-title');
        if (title) title.textContent = category || 'Todos los productos';
        categoryChoices.forEach((button) => {
            const active = button.dataset.categoryChoice === category;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        const activeCategory = document.querySelector('[data-active-category]');
        if (activeCategory) {
            activeCategory.textContent = category;
            activeCategory.hidden = !category;
        }
        document.querySelectorAll('[data-clear-catalog]').forEach((button) => {
            if (!button.closest('[data-catalog-empty]')) button.hidden = !(query || category || availableOnly || sort);
        });
        const novelty = document.querySelector('[data-novelty-section]');
        if (novelty) novelty.hidden = Boolean(query || category || availableOnly || sort);
    };
    [catalogSearch, catalogSearchNav].filter(Boolean).forEach((input) => {
        input.addEventListener('input', () => {
            [catalogSearch, catalogSearchNav].filter(Boolean).forEach((other) => { other.value = input.value; });
            filterCatalog();
        });
    });
    catalogFilter?.addEventListener('change', filterCatalog);
    catalogSort?.addEventListener('change', filterCatalog);
    catalogAvailable?.addEventListener('change', filterCatalog);
    categoryChoices.forEach((button) => button.addEventListener('click', () => {
        selectedCategory = button.dataset.categoryChoice || '';
        if (catalogFilter) catalogFilter.value = selectedCategory;
        filterCatalog();
    }));
    document.querySelectorAll('[data-clear-catalog]').forEach((button) => button.addEventListener('click', () => {
        selectedCategory = '';
        [catalogSearch, catalogSearchNav, catalogFilter, catalogSort].filter(Boolean).forEach((input) => { input.value = ''; });
        if (catalogAvailable) catalogAvailable.checked = false;
        filterCatalog();
        if (catalogGrid) {
            const address = new URL(window.location.href);
            address.searchParams.delete('q');
            window.history.replaceState(null, '', address);
        }
        document.querySelector('#catalog-title')?.focus();
    }));
    document.querySelectorAll('[data-store-image]').forEach((img) => {
        const fallback = () => {
            img.hidden = true;
            const placeholder = img.parentElement.querySelector('.store-image-fallback');
            if (placeholder) placeholder.hidden = false;
        };
        img.addEventListener('error', fallback, { once: true });
        if (img.complete && img.naturalWidth === 0) fallback();
    });
    filterCatalog();

    const shoppingBackdrop = document.querySelector('[data-shopping-backdrop]');
    const shoppingDrawers = [...document.querySelectorAll('[data-shopping-drawer]')];
    const shoppingUserId = Number(shoppingBackdrop?.dataset.userId || 0);
    const shoppingStatus = document.querySelector('[data-shopping-status]');
    let shoppingSource = 'loading';

    const announceShopping = (message) => {
        showToast(message, 'success');
    };
    const setShoppingNotice = (message = '') => {
        if (shoppingStatus) { shoppingStatus.textContent = message; shoppingStatus.hidden = message === ''; }
        const statusContainer = document.querySelector('[data-shopping-status-container]');
        if(statusContainer) statusContainer.hidden = message === '';
        const inline = document.querySelector('[data-store-shopping-notice]');
        if (inline) inline.textContent = message;
        const container = document.querySelector('[data-store-shopping-notice-container]');
        if (container) container.hidden = message === '';
    };
    let cartItems = [];
    let wishlistItems = [];

    const makeButton = (label, icon, action, id, className = '') => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.setAttribute(`data-${action}`, '');
        button.dataset.productId = String(id);
        button.setAttribute('aria-label', label);
        const iconElement = document.createElement('i');
        iconElement.className = `bi ${icon}`;
        iconElement.setAttribute('aria-hidden', 'true');
        button.append(iconElement);
        return button;
    };
    const createShoppingItem = (item, type) => {
        const row = document.createElement('article');
        row.className = 'shopping-item';
        row.classList.toggle('shopping-item--cart',type==='cart');
        const image = document.createElement('div');
        image.className = 'shopping-item__image';
        if (item.image) {
            const img = document.createElement('img');
            img.src = item.image;
            img.alt = '';
            img.loading = 'lazy';
            img.addEventListener('error', () => {
                image.replaceChildren();
                const fallbackIcon = document.createElement('i');
                fallbackIcon.className = 'bi bi-box-seam';
                image.append(fallbackIcon);
            }, { once: true });
            image.append(img);
        } else {
            const fallbackIcon = document.createElement('i');
            fallbackIcon.className = 'bi bi-box-seam';
            image.append(fallbackIcon);
        }

        const details = document.createElement('div');
        details.className = 'shopping-item__details';
        const name = document.createElement('strong');
        name.textContent = item.name;
        const classification = document.createElement('span');
        classification.textContent = [item.brand, item.category].filter(Boolean).join(' · ');
        const price = document.createElement('strong');
        price.className = 'shopping-item__price';
        const unitPrice = Number(item.price);
        price.textContent = Number.isFinite(unitPrice) ? `$${unitPrice.toFixed(2)}` : 'Precio no disponible';
        const stock = document.createElement('span');
        stock.className = 'shopping-item__stock';
        const availableStock = Number(item.stock);
        stock.textContent = Number.isFinite(availableStock)
            ? (availableStock > 0 ? `${availableStock} ${availableStock===1?'disponible':'disponibles'}` : 'Agotado')
            : 'Existencias por confirmar';
        details.append(name, classification);
        details.append(price, stock);
        if(item.available===false) { row.classList.add('shopping-item--unavailable'); stock.textContent='No disponible'; }
        let notice=null;
        if(item.issues?.length) {
            notice=document.createElement('div');notice.className='shopping-item__notice';
            const issues=document.createElement('details');issues.className='shopping-item__issues';
            const summary=document.createElement('summary');
            const labels=[];
            if(item.available===false) labels.push('No disponible');
            else if(availableStock===0) labels.push('Agotado');
            else if(type==='cart' && Number(item.quantity)>availableStock) labels.push('Stock insuficiente');
            if(item.price_changed) labels.push('Precio actualizado');
            summary.textContent=labels.join(' · ')||'Revisar cambios';
            const explanation=document.createElement('p');explanation.textContent=item.issues.join(' ');
            issues.append(summary,explanation);notice.append(issues);
        }

        const footer = document.createElement('div');
        footer.className = 'shopping-item__footer';
        if (type === 'cart') {
            const controls = document.createElement('div');
            controls.className = 'shopping-item__controls';
            const decrease=makeButton('Quitar una unidad', 'bi-dash', 'decrease-cart-item', item.id);
            decrease.disabled=item.available===false;
            controls.append(decrease);
            const quantity = document.createElement('span');
            quantity.textContent = String(item.quantity);
            controls.append(quantity);
            const increase = makeButton('Agregar una unidad', 'bi-plus', 'increase-cart-item', item.id);
            increase.disabled = Number.isFinite(availableStock) && Number(item.quantity) >= availableStock;
            controls.append(increase);
            const quantityLine=document.createElement('div');quantityLine.className='shopping-item__quantity-line';
            quantityLine.append(controls);
            const subtotal = document.createElement('span');
            subtotal.className = 'shopping-item__subtotal';
            subtotal.textContent = item.available===false ? 'No incluido en el total' : Number.isFinite(unitPrice)
                ? `Subtotal: $${(unitPrice * item.quantity).toFixed(2)}`
                : 'Subtotal no disponible';
            quantityLine.append(subtotal);details.append(quantityLine);
            const remove = makeButton('Quitar del carrito', 'bi-trash', 'remove-cart-item', item.id, 'shopping-item__remove');
            footer.append(remove);
            if(item.available!==false && availableStock>0 && Number(item.quantity)>availableStock) {
                const adjust=makeButton('Ajustar a las existencias disponibles','bi-arrow-repeat','adjust-cart-item',item.id,'shopping-item__move');adjust.textContent='Usar '+availableStock;adjust.title='Ajustar a '+availableStock+(availableStock===1?' unidad':' unidades');notice?.append(adjust);
            }
        } else {
            const addToCart = document.createElement('button');
            addToCart.type = 'button';
            addToCart.className = 'shopping-item__move';
            addToCart.dataset.moveToCart = '';
            addToCart.dataset.productId = String(item.id);
            addToCart.textContent = item.available===false ? 'No disponible' : availableStock <= 0 ? 'Agotado' : 'Agregar al carrito';
            addToCart.disabled = item.available===false || availableStock <= 0;
            footer.append(addToCart);
            footer.append(makeButton('Quitar de favoritos', 'bi-trash', 'remove-wishlist-item', item.id, 'shopping-item__remove'));
        }
        row.append(image, details, footer);
        if(notice) row.append(notice);
        return row;
    };
    const renderCollection = (container, items, type) => {
        if (!container) return;
        const active = container.contains(document.activeElement) ? document.activeElement : null;
        const action = active ? [...active.attributes].find((attribute) => attribute.name.startsWith('data-') && attribute.name !== 'data-product-id')?.name : null;
        const focusedProduct = active?.dataset.productId;
        const restoreFocus = () => {
            if (!active) return;
            const replacement = [...container.querySelectorAll('button[data-product-id]')].find((button) => button.dataset.productId === focusedProduct && action && button.hasAttribute(action) && !button.disabled);
            (replacement || container.closest('[data-shopping-drawer]')?.querySelector('[data-close-drawer]'))?.focus();
        };
        container.replaceChildren();
        if (items.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'shopping-drawer__empty';
            const icon = document.createElement('i');
            icon.className = type === 'cart' ? 'bi bi-cart3' : 'bi bi-heart';
            const message = document.createElement('p');
            message.textContent = type === 'cart'
                ? 'Tu carrito todavía está vacío.'
                : 'Todavía no guardas productos.';
            empty.append(icon, message);
            container.append(empty);
            restoreFocus();
            return;
        }
        items.forEach((item) => container.append(createShoppingItem(item, type)));
        restoreFocus();
    };
    const renderShopping = () => {
        renderCollection(document.querySelector('[data-cart-items]'), cartItems, 'cart');
        renderCollection(document.querySelector('[data-wishlist-items]'), wishlistItems, 'wishlist');
        const cartCount = document.querySelector('[data-cart-count]');
        const wishlistCount = document.querySelector('[data-wishlist-count]');
        const cartTotal = document.querySelector('[data-cart-total]');
        if (cartCount) cartCount.textContent = String(cartItems.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0));
        if (wishlistCount) wishlistCount.textContent = String(wishlistItems.length);
        if (cartTotal) cartTotal.textContent = `$${cartItems.filter(item=>item.available!==false).reduce((sum, item) => {
            const price = Number(item.price);
            const quantity = Number(item.quantity);
            return Number.isFinite(price) && Number.isFinite(quantity) ? sum + price * quantity : sum;
        }, 0).toFixed(2)}`;
        document.querySelectorAll('[data-shopping-retry]').forEach((button) => { button.disabled = shoppingSource === 'loading'; });
        const blocked=['loading','saving','pending','error','invalid'].includes(shoppingSource);
        document.querySelectorAll('[data-checkout-url]').forEach(button=>{
            button.disabled=blocked || shoppingSource!=='account' || !cartItems.length || cartItems.some(item=>item.available===false || item.quantity>item.stock);
            button.onclick=()=>{if(!button.disabled)window.location.assign(button.dataset.checkoutUrl);};
        });
        document.querySelectorAll('[data-add-cart],[data-add-wishlist],[data-detail-add-cart],[data-detail-add-wishlist],[data-shopping-drawer] button[data-product-id]').forEach(button=>{
            if(blocked) {if(!button.disabled) {button.disabled=true;button.dataset.syncDisabled='1';}}
            else if(button.dataset.syncDisabled) {button.disabled=false;delete button.dataset.syncDisabled;}
        });
        document.querySelectorAll('[data-shopping-save-state]').forEach(note=>{ note.textContent=shoppingSource==='account'?'Guardado en tu cuenta.':shoppingSource==='saving'?'Guardando…':shoppingSource==='loading'?'Consultando tu cuenta…':'Hay cambios sin confirmar. Reintenta antes de continuar.'; });
        const needsReview=cartItems.some(item=>item.issues?.length);
        const review=document.querySelector('[data-shopping-review]');if(review) review.hidden=!needsReview;
        const priceReview=document.querySelector('[data-cart-review]');if(priceReview) {priceReview.hidden=!cartItems.some(item=>item.price_changed);priceReview.disabled=blocked;}
        document.querySelectorAll('[data-product-card]').forEach((card) => {
            const productId = card.querySelector('.product-card')?.dataset.productId ?? card.dataset.productId;
            const favorite = wishlistItems.some((item) => String(item.id) === productId);
            const button = card.querySelector('[data-add-wishlist]');
            const icon = button?.querySelector('i');
            button?.classList.toggle('is-active', favorite);
            if (icon) icon.className = favorite ? 'bi bi-heart-fill' : 'bi bi-heart';
            button?.setAttribute('aria-label', `${favorite ? 'Quitar de favoritos:' : 'Guardar en favoritos:'} ${card.querySelector('.product-card')?.dataset.productName || ''}`);
            button?.setAttribute('aria-pressed', String(favorite));
            if (button) button.title = favorite ? 'Quitar de favoritos' : 'Guardar en favoritos';
        });
        document.querySelectorAll('[data-detail-add-wishlist]').forEach((button) => {
            const favorite = wishlistItems.some((item) => String(item.id) === button.dataset.productId);
            const icon = button.querySelector('i');
            button.classList.toggle('is-active', favorite);
            button.setAttribute('aria-label', favorite ? 'Quitar de favoritos' : 'Agregar a favoritos');
            button.title = favorite ? 'Quitar de favoritos' : 'Agregar a favoritos';
            if (icon) icon.className = favorite ? 'bi bi-heart-fill' : 'bi bi-heart';
        });
    };
    const shoppingClient = shoppingBackdrop ? new ShoppingClient({
        userId: shoppingUserId,
        stateUrl: shoppingBackdrop.dataset.stateUrl,
        updateUrl: shoppingBackdrop.dataset.updateUrl,
        productUrl: shoppingBackdrop.dataset.productUrl,
        csrf: shoppingBackdrop.dataset.csrfToken,
        onChange: (state, status, notice) => {
            cartItems = state.cart; wishlistItems = state.wishlist; shoppingSource = status;
            setShoppingNotice(notice); renderShopping();
        },
    }) : null;
    const persistShopping = (mutation) => shoppingClient ? shoppingClient.mutate(mutation) : Promise.resolve(false);
    const shoppingReady = shoppingClient?.ready || Promise.resolve();
    document.querySelectorAll('[data-shopping-retry]').forEach(button => button.addEventListener('click', () => shoppingClient?.refresh()));
    document.querySelectorAll('[data-cart-review]').forEach(button => button.addEventListener('click', () => persistShopping({ action:'cart_review', prices:Object.fromEntries(cartItems.map(item=>[item.id,item.price])) })));
    const getCardProduct = (card) => {
        const productCard = card.matches('.product-card') ? card : card.querySelector('.product-card');
        if (!productCard) return null;
        return {
            id: productCard.dataset.productId,
            name: productCard.dataset.productName,
            brand: productCard.dataset.productBrand,
            category: productCard.dataset.productCategory,
            description: productCard.dataset.productDescription,
            price: Number(productCard.dataset.productPrice),
            image: productCard.dataset.productImage || '',
            stock: Number(productCard.dataset.productStock),
        };
    };
    const addToCart = (product, quantity = 1) => persistShopping({ action:'cart_add', product_id:product.id, quantity, expected_price:Number(product.price), product }).then(ok => {
        if(ok) announceShopping(`${product.name} se añadió al carrito.`);
    });
    let lastDrawerTrigger = null;
    const backgroundStates = new Map();
    const openDrawer = (name) => {
        const drawer = shoppingDrawers.find((item) => item.dataset.shoppingDrawer === name);
        if (!drawer || !shoppingBackdrop) return;
        if (!document.body.classList.contains('shopping-drawer-open')) {
            lastDrawerTrigger = document.activeElement;
            document.querySelectorAll('body > header, body > main, body > footer').forEach((element) => { backgroundStates.set(element, element.inert); element.inert = true; });
        }
        shoppingDrawers.forEach((item) => {
            const active = item === drawer;
            item.classList.toggle('is-open', active);
            item.inert = !active;
            item.setAttribute('aria-hidden', String(!active));
        });
        shoppingBackdrop.hidden = false;
        requestAnimationFrame(() => shoppingBackdrop.classList.add('is-visible'));
        document.body.classList.add('shopping-drawer-open');
        drawer.querySelector('[data-close-drawer]')?.focus();
    };
    const closeDrawers = () => {
        if (!document.body.classList.contains('shopping-drawer-open')) return;
        backgroundStates.forEach((value, element) => { element.inert = value; });
        backgroundStates.clear();
        if (lastDrawerTrigger?.isConnected && !lastDrawerTrigger.disabled) lastDrawerTrigger.focus();
        shoppingDrawers.forEach((drawer) => {
            drawer.classList.remove('is-open');
            drawer.inert = true;
            drawer.setAttribute('aria-hidden', 'true');
        });
        shoppingBackdrop?.classList.remove('is-visible');
        document.body.classList.remove('shopping-drawer-open');
        window.setTimeout(() => {
            if (shoppingBackdrop && !shoppingBackdrop.classList.contains('is-visible')) {
                shoppingBackdrop.hidden = true;
            }
        }, 250);
    };

    shoppingReady.then(() => {
        renderShopping();
        if (window.location.hash === '#favoritos') openDrawer('wishlist');
    });
    document.querySelectorAll('[data-open-drawer]').forEach((button) => {
        button.addEventListener('click', () => shoppingReady.then(async () => {
            await shoppingClient?.refresh();
            openDrawer(button.dataset.openDrawer);
        }));
    });
    document.querySelectorAll('[data-close-drawer]').forEach((button) => button.addEventListener('click', closeDrawers));
    shoppingBackdrop?.addEventListener('click', closeDrawers);
    document.addEventListener('keydown', (event) => {
        if (!document.body.classList.contains('shopping-drawer-open')) return;
        if (event.key === 'Escape') { closeDrawers(); return; }
        if (event.key === 'Tab') {
            const drawer = shoppingDrawers.find((item) => item.classList.contains('is-open'));
            const controls = [...(drawer?.querySelectorAll('button:not([disabled]), a[href], summary, input:not([disabled]), select:not([disabled]), [tabindex="0"]') || [])].filter((element) => element.getClientRects().length > 0);
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        }
    });
    document.querySelectorAll('[data-product-card]').forEach((card) => {
        card.querySelector('[data-add-cart]')?.addEventListener('click', () => {
            shoppingReady.then(() => {
                const product = getCardProduct(card);
                if (product) addToCart(product);
            });
        });
        card.querySelector('[data-add-wishlist]')?.addEventListener('click', () => {
            shoppingReady.then(() => {
                const product = getCardProduct(card);
                if (!product) return;
                const existing = wishlistItems.some((item) => String(item.id) === String(product.id));
                wishlistItems = existing
                    ? wishlistItems.filter((item) => String(item.id) !== String(product.id))
                    : [...wishlistItems, product];

                renderShopping();
                persistShopping({
                    action: existing ? 'wishlist_remove' : 'wishlist_add',
                    product_id: product.id,
                    product,
                }).then(() => { if (shoppingSource === 'account' || shoppingSource === 'browser') announceShopping(existing ? `${product.name} se quitó de favoritos.` : `${product.name} se guardó en favoritos.`); });
            });
        });
    });
    document.querySelectorAll('[data-detail-add-cart], [data-detail-add-wishlist]').forEach((button) => {
        const product = {
            id: button.dataset.productId,
            name: button.dataset.productName,
            brand: button.dataset.productBrand,
            category: button.dataset.productCategory,
            description: button.dataset.productDescription,
            price: Number(button.dataset.productPrice),
            image: button.dataset.productImage || '',
            stock: Number(button.dataset.productStock || document.querySelector('[data-product-quantity]')?.dataset.maxStock || 0),
        };
        if (button.hasAttribute('data-detail-add-cart')) {
            button.addEventListener('click', () => {
                shoppingReady.then(() => {
                    const quantity = Number(document.querySelector('[data-quantity-value]')?.textContent || 1);
                    addToCart(product, quantity);
                    openDrawer('cart');
                });
            });
        } else {
            button.addEventListener('click', () => {
                shoppingReady.then(() => {
                    const existing = wishlistItems.some((item) => String(item.id) === String(product.id));
                    wishlistItems = existing
                        ? wishlistItems.filter((item) => String(item.id) !== String(product.id))
                        : [...wishlistItems, product];

                    renderShopping();
                    persistShopping({
                        action: existing ? 'wishlist_remove' : 'wishlist_add',
                        product_id: product.id,
                    product,
                    });
                });
            });
        }
    });
    document.querySelectorAll('[data-product-quantity]').forEach((quantityControl) => {
        const value = quantityControl.querySelector('[data-quantity-value]');
        const maximum = Number(quantityControl.dataset.maxStock) || Number.POSITIVE_INFINITY;
        const updateQuantityButtons = () => {
            const quantity = Number(value?.textContent || 1);
            quantityControl.querySelector('[data-quantity-decrease]').disabled = quantity <= 1;
            quantityControl.querySelector('[data-quantity-increase]').disabled = quantity >= maximum;
        };
        quantityControl.querySelector('[data-quantity-decrease]')?.addEventListener('click', () => {
            if (value) value.textContent = String(Math.max(1, Number(value.textContent) - 1));
            updateQuantityButtons();
        });
        quantityControl.querySelector('[data-quantity-increase]')?.addEventListener('click', () => {
            if (value) value.textContent = String(Math.min(maximum, Number(value.textContent) + 1));
            updateQuantityButtons();
        });
        updateQuantityButtons();
    });
    document.querySelectorAll('[data-shopping-drawer]').forEach((drawer) => {
        drawer.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-product-id]');
            if (!button) return;
            shoppingReady.then(() => {
                const id = button.dataset.productId;
                let mutation = null;
                if (button.hasAttribute('data-increase-cart-item')) {
                    const item = cartItems.find((entry) => String(entry.id) === id);
                    if (item && (!Number.isFinite(Number(item.stock)) || item.quantity < Number(item.stock))) {
                        item.quantity += 1;
                        mutation = { action: 'cart_change', product_id: id, delta: button.hasAttribute('data-increase-cart-item') ? 1 : -1 };
                    }
                } else if (button.hasAttribute('data-decrease-cart-item')) {
                    const item = cartItems.find((entry) => String(entry.id) === id);
                    if (item && item.quantity > 1) {
                        item.quantity -= 1;
                        mutation = { action: 'cart_change', product_id: id, delta: button.hasAttribute('data-increase-cart-item') ? 1 : -1 };
                    } else {
                        cartItems = cartItems.filter((entry) => String(entry.id) !== id);
                        mutation = { action: 'cart_remove', product_id: id };
                    }
                } else if (button.hasAttribute('data-adjust-cart-item')) {
                    const item=cartItems.find(entry=>String(entry.id)===id);
                    if(item) mutation={action:'cart_set_quantity',product_id:id,quantity:Number(item.stock)};
                } else if (button.hasAttribute('data-remove-cart-item')) {
                    cartItems = cartItems.filter((entry) => String(entry.id) !== id);
                    mutation = { action: 'cart_remove', product_id: id };
                } else if (button.hasAttribute('data-remove-wishlist-item')) {
                    wishlistItems = wishlistItems.filter((entry) => String(entry.id) !== id);
                    mutation = { action: 'wishlist_remove', product_id: id };
                } else if (button.hasAttribute('data-move-to-cart')) {
                    const product = wishlistItems.find((entry) => String(entry.id) === id);
                    if (product) addToCart(product);
                    return;
                }


                renderShopping();
                if (mutation) persistShopping(mutation);
            });
        });
    });

});

