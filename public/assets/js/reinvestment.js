(() => {
    const table=document.querySelector('[data-table-kind="reinvestment"]');
    if(!table)return;
    document.addEventListener('commerce:summary',event=>{
        document.querySelectorAll('[data-reinvestment]').forEach(node=>{node.textContent='$'+Number(event.detail[node.dataset.reinvestment]||0).toFixed(2);});
        const warning=document.querySelector('[data-reinvestment-missing]'),missing=Number(event.detail.missing||0);warning.hidden=!missing;warning.textContent=missing+' ventas anteriores pagadas requieren revisar su reinversión. No se han reconstruido automáticamente.';
    });
})();
