'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const toastElement = document.querySelector('#auth-toast');
    const toastMessage = document.querySelector('[data-toast-message]');
    const showToast = (message, type = 'error') => {
        if (!toastElement || !toastMessage || typeof bootstrap === 'undefined') {
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

    const catalogSearch = document.querySelector('[data-catalog-search]') ||
        document.querySelector('[data-catalog-search-nav]');
    const catalogFilter = document.querySelector('[data-catalog-filter]');
    const catalogSort = document.querySelector('[data-catalog-sort]');
    const catalogGrid = document.querySelector('[data-catalog-grid]');
    const productCards = document.querySelectorAll('[data-product-card]');
    const catalogEmpty = document.querySelector('[data-catalog-empty]');
    const filterCatalog = () => {
        if (!catalogSearch || !catalogFilter || !catalogEmpty) return;
        const query = catalogSearch.value.trim().toLowerCase();
        const category = catalogFilter.value;
        const sort = catalogSort?.value || '';
        let visibleProducts = 0;

        productCards.forEach((card) => {
            const matchesSearch = !query || card.dataset.search.includes(query);
            const matchesCategory = !category || card.dataset.category === category;
            const visible = matchesSearch && matchesCategory;
            card.classList.toggle('d-none', !visible);
            if (visible) visibleProducts += 1;
        });

        if (catalogGrid && sort) {
            const sortedCards = [...productCards].sort((firstCard, secondCard) => {
                const firstPrice = Number(firstCard.dataset.price);
                const secondPrice = Number(secondCard.dataset.price);
                return sort === 'price-desc'
                    ? secondPrice - firstPrice
                    : firstPrice - secondPrice;
            });
            sortedCards.forEach((card) => catalogGrid.appendChild(card));
        }

        catalogEmpty.classList.toggle('d-none', visibleProducts !== 0);
    };

    catalogSearch?.addEventListener('input', filterCatalog);
    catalogFilter?.addEventListener('change', filterCatalog);
    catalogSort?.addEventListener('change', filterCatalog);

    const catalogSearchNav = document.querySelector('[data-catalog-search-nav]');
    catalogSearchNav?.addEventListener('input', () => {
        if (!catalogSearch) return;
        catalogSearch.value = catalogSearchNav.value;
        filterCatalog();
    });

});
