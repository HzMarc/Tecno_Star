(() => {
    'use strict';
    const dialog = document.querySelector('[data-catalog-modal]');
    const body = dialog?.querySelector('[data-modal-body]');
    const title = dialog?.querySelector('#catalog-modal-title');
    const toasts = document.querySelector('[data-catalog-toasts]');
    if (!dialog || !body || !toasts) return;

    let opener = null;
    let busy = false;
    let loading = false;
    const toastTimers = new WeakMap();
    const dismissToast = (toast) => { clearTimeout(toastTimers.get(toast)); toast.remove(); };
    const scheduleToast = (toast) => {
        clearTimeout(toastTimers.get(toast));
        toastTimers.set(toast, setTimeout(() => dismissToast(toast), 6500));
    };
    const activateToast = (toast) => {
        scheduleToast(toast);
        toast.addEventListener('mouseenter', () => clearTimeout(toastTimers.get(toast)));
        toast.addEventListener('mouseleave', () => scheduleToast(toast));
        toast.addEventListener('focusin', () => clearTimeout(toastTimers.get(toast)));
        toast.addEventListener('focusout', () => scheduleToast(toast));
    };
    toasts.querySelectorAll('.catalog-toast').forEach(activateToast);
    const notify = (message, error = false) => {
        const toast = document.createElement('div');
        toast.className = `catalog-toast${error ? ' catalog-toast--error' : ''}`;
        toast.setAttribute('role', error ? 'alert' : 'status');
        const icon = document.createElement('i');
        icon.className = `bi ${error ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill'}`;
        icon.setAttribute('aria-hidden', 'true');
        const text = document.createElement('span'); text.textContent = message;
        const close = document.createElement('button'); close.type = 'button';
        close.dataset.dismissToast = ''; close.setAttribute('aria-label', 'Cerrar notificación'); close.textContent = '×';
        toast.append(icon, text, close); toasts.append(toast); activateToast(toast);
    };
    const show = (content, label, trigger, compact = false) => {
        body.replaceChildren(content);
        title.textContent = label;
        dialog.classList.toggle('catalog-modal--compact', compact);
        if (!dialog.open) {
            opener = trigger;
            dialog.append(toasts);
            dialog.showModal();
            document.body.classList.add('catalog-modal-open');
        }
        // El scroll comienza en el título; el primer campo queda listo para escribir.
        dialog.scrollTop = 0;
        body.querySelector('input:not([type="hidden"]):not([type="file"]),select,textarea')?.focus({ preventScroll: true });
    };
    const closeModal = () => { if (!busy) dialog.close(); };
    dialog.addEventListener('cancel', (event) => { if (busy) event.preventDefault(); });
    dialog.addEventListener('close', () => {
        document.body.append(toasts);
        document.body.classList.remove('catalog-modal-open'); body.replaceChildren();
        if (opener?.isConnected) opener.focus();
        else document.querySelector('[data-product-modal], [data-entity-modal]')?.focus();
    });
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) closeModal();
    });

    document.addEventListener('click', async (event) => {
        const dismiss = event.target.closest('[data-dismiss-toast]');
        if (dismiss) { dismissToast(dismiss.closest('.catalog-toast')); return; }
        const cancel = event.target.closest('[data-close-modal], .catalog-modal .catalog-save a');
        if (cancel) { event.preventDefault(); closeModal(); return; }
        const productLink = event.target.closest('[data-product-modal]');
        if (productLink && !event.ctrlKey && !event.metaKey && !event.shiftKey && event.button === 0) {
            event.preventDefault();
            if (loading) return;
            loading = true; productLink.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(productLink.href, { credentials: 'same-origin', signal: AbortSignal.timeout(15000) });
                const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                const editor = page.querySelector('[data-product-editor]');
                if (!response.ok || !editor) throw new Error('No se pudo abrir el producto. Recarga la página y revisa tu sesión.');
                show(editor, editor.dataset.editorTitle, productLink);
            } catch (error) { notify(error.message || 'No se pudo abrir el formulario.', true); }
            finally { loading = false; productLink.removeAttribute('aria-busy'); }
            return;
        }
        const entityButton = event.target.closest('[data-entity-modal]');
        if (entityButton) {
            const template = document.querySelector('template[data-entity-editor]');
            if (!template) return;
            const content = template.content.cloneNode(true);
            content.querySelector('[name="id"]').value = entityButton.dataset.id || '';
            content.querySelector('[name="name"]').value = entityButton.dataset.name || '';
            content.querySelector('[name="estado"]').value = entityButton.dataset.state || 'activo';
            content.querySelector('.catalog-errors')?.remove();
            show(content, entityButton.dataset.editorTitle, entityButton, true);
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!form.matches('.catalog-modal form, form[data-catalog-state]')) return;
        event.preventDefault();
        if (busy || !form.reportValidity()) return;
        busy = true;
        // Se construye antes de deshabilitar controles para conservar todos sus valores.
        const data = new FormData(form);
        const buttons = [...form.querySelectorAll('button')];
        buttons.forEach((button) => { button.disabled = true; });
        dialog.querySelector('[data-close-modal]').disabled = true;
        form.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin', signal: AbortSignal.timeout(30000) });
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (response.status === 422 && dialog.open) {
                const product = page.querySelector('[data-product-editor]');
                const entity = page.querySelector('template[data-entity-editor]');
                if (product) show(product, product.dataset.editorTitle, opener);
                else if (entity) show(entity.content.cloneNode(true), title.textContent, opener, true);
                else throw new Error('Revisa los datos del formulario.');
                body.querySelector('.catalog-errors')?.setAttribute('tabindex', '-1');
                body.querySelector('.catalog-errors')?.focus();
                return;
            }
            if (!response.ok) {
                const errorMessage = response.status === 413 ? 'La imagen es demasiado grande. Usa un archivo de hasta 4 MiB.'
                    : response.status === 403 ? 'Tu sesión o permisos cambiaron. Recarga la página antes de continuar.'
                    : page.querySelector('main p')?.textContent?.trim() || 'No se pudo guardar. Revisa los datos e intenta nuevamente.';
                throw new Error(errorMessage);
            }
            const main = page.querySelector('main.admin-main');
            const message = page.querySelector('[data-catalog-notice] span')?.textContent?.trim();
            if (!main || !message) throw new Error('No se pudo confirmar el resultado. Recarga el catálogo antes de intentar otra vez.');
            document.dispatchEvent(new Event('admin:before-content-replace'));
            document.querySelector('main.admin-main').replaceWith(main);
            document.dispatchEvent(new Event('admin:content-replaced'));
            const resultUrl = new URL(response.url);
            history.replaceState(null, '', resultUrl.pathname + resultUrl.search);
            busy = false;
            if (dialog.open) dialog.close();
            notify(message);
        } catch (error) {
            notify(error.name === 'TimeoutError' || error.name === 'TypeError'
                ? 'No se pudo confirmar el guardado. Revisa el catálogo antes de reenviar para evitar duplicados.'
                : error.message || 'No se pudo guardar.', true);
        } finally {
            busy = false; buttons.forEach((button) => { button.disabled = false; });
            dialog.querySelector('[data-close-modal]').disabled = false; form.removeAttribute('aria-busy');
        }
    });

    // Permite abrir enlaces directos y mantiene el modal al volver con errores de validación.
    const initialEditor = document.querySelector('main [data-product-editor]');
    if (initialEditor) show(initialEditor, initialEditor.dataset.editorTitle, null);
    else {
        const template = document.querySelector('template[data-entity-editor]');
        if (template?.content.querySelector('.catalog-errors')) {
            const content = template.content.cloneNode(true);
            show(content, content.querySelector('[data-editor-title]').dataset.editorTitle, null, true);
        }
    }
})();
