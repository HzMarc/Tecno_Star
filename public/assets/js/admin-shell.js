(() => {
    'use strict';
    const sidebar = document.querySelector('#admin-navigation');
    const toggle = document.querySelector('[data-admin-nav-toggle]');
    const backdrop = document.querySelector('[data-admin-nav-backdrop]');
    const account = document.querySelector('[data-admin-account]');
    if (!sidebar || !toggle || !backdrop) return;
    const mobile = matchMedia('(max-width: 991.98px)');
    const storageKey = 'tecno-star-admin-sidebar-collapsed';
    let collapsed = false;
    let open = false;
    try { collapsed = localStorage.getItem(storageKey) === '1'; } catch (_) { /* La navegación funciona sin almacenamiento. */ }

    const render = () => {
        const isMobile = mobile.matches;
        document.body.classList.toggle('admin-sidebar-collapsed', collapsed);
        document.body.classList.toggle('admin-mobile-nav-open', isMobile && open);
        sidebar.inert = isMobile && !open;
        if (isMobile && open) {
            sidebar.setAttribute('role', 'dialog'); sidebar.setAttribute('aria-modal', 'true');
        } else {
            sidebar.removeAttribute('role'); sidebar.removeAttribute('aria-modal');
        }
        if (isMobile && !open) sidebar.setAttribute('aria-hidden', 'true');
        else sidebar.removeAttribute('aria-hidden');
        backdrop.hidden = !isMobile || !open;
        toggle.inert = isMobile && open;
        toggle.setAttribute('aria-expanded', String(isMobile ? open : !collapsed));
        const label = isMobile ? 'Abrir menú lateral' : collapsed ? 'Expandir menú lateral' : 'Contraer menú lateral';
        toggle.setAttribute('aria-label', label); toggle.title = label;
        const icon = toggle.querySelector('i');
        icon.className = `bi ${isMobile ? 'bi-list' : collapsed ? 'bi-layout-sidebar' : 'bi-layout-sidebar-inset'}`;
        const main = document.querySelector('main.admin-main');
        if (main) main.inert = isMobile && open;
        if (account) account.inert = isMobile && open;
    };
    const closeMobile = (restore = true) => {
        open = false; render();
        if (restore) toggle.focus();
    };
    toggle.addEventListener('click', () => {
        if (mobile.matches) {
            open = true;
            if (account) account.open = false;
            render(); sidebar.querySelector('[data-admin-nav-close]')?.focus();
        } else {
            collapsed = !collapsed;
            try { localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (_) {}
            render();
        }
    });
    sidebar.querySelector('[data-admin-nav-close]')?.addEventListener('click', () => closeMobile());
    backdrop.addEventListener('click', () => closeMobile());
    sidebar.addEventListener('click', (event) => {
        if (mobile.matches && event.target.closest('a[href]')) closeMobile(false);
    });
    mobile.addEventListener('change', () => {
        open = false; render();
        if (sidebar.inert && sidebar.contains(document.activeElement)) toggle.focus();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobile.matches && open) {
            event.preventDefault(); closeMobile();
        } else if (event.key === 'Escape' && account?.open) {
            account.open = false; account.querySelector('summary').focus();
        }
        if (event.key !== 'Tab' || !mobile.matches || !open) return;
        const targets = [...sidebar.querySelectorAll('a[href],button:not(:disabled)')].filter((element) => element.getClientRects().length);
        const first = targets[0]; const last = targets[targets.length - 1];
        if (event.shiftKey && (document.activeElement === first || !sidebar.contains(document.activeElement))) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && (document.activeElement === last || !sidebar.contains(document.activeElement))) { event.preventDefault(); first?.focus(); }
    });
    document.addEventListener('click', (event) => {
        if (account?.open && !account.contains(event.target)) account.open = false;
    });
    render();
})();
