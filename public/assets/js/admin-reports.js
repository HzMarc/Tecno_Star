(() => {
    'use strict';
    const form = document.querySelector('[data-report-form]');
    if (!form) return;

    const master = form.querySelector('[data-report-select-all]');
    const sections = [...form.querySelectorAll('[name="sections[]"]')];
    const countLabel = form.querySelector('[data-report-count]');
    const error = form.querySelector('[data-report-error]');
    const update = () => {
        const checked = sections.filter(item => item.checked).length;
        master.checked = checked === sections.length;
        master.indeterminate = checked > 0 && checked < sections.length;
        countLabel.textContent = checked === sections.length
            ? 'Reporte general · todas las secciones'
            : `${checked} ${checked === 1 ? 'sección seleccionada' : 'secciones seleccionadas'}`;
        error.hidden = checked > 0;
    };

    master?.addEventListener('change', () => {
        sections.forEach(item => { item.checked = master.checked; });
        update();
    });
    sections.forEach(item => item.addEventListener('change', update));
    form.addEventListener('submit', event => {
        if (!sections.some(item => item.checked)) {
            event.preventDefault();
            update();
            form.querySelector('.reports-section-card input')?.focus();
        }
    });
    update();
})();
