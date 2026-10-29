(() => {
    const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    if (window.AOS && !reduced) window.AOS.init({ duration: 520, easing: 'ease-out-cubic', once: true, offset: 24 });
    if (window.gsap && !reduced) {
        window.gsap.from('.catalog-product, .customer-order-card, .orders-panel', { opacity: 0, y: 14, duration: .42, stagger: .045, ease: 'power2.out', clearProps: 'all' });
        window.gsap.from('.admin-sidebar__nav a', { opacity: 0, x: -8, duration: .25, stagger: .025, ease: 'power1.out', clearProps: 'all' });
    }
    if (window.Swiper) document.querySelectorAll('[data-swiper]').forEach((node) => new Swiper(node, { slidesPerView: 1, spaceBetween: 16, breakpoints: { 640: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }, navigation: { nextEl: node.querySelector('[data-swiper-next]'), prevEl: node.querySelector('[data-swiper-prev]') } }));
    document.querySelectorAll('[data-lottie]').forEach((node) => {
        if (!window.lottie) return;
        const animation = window.lottie.loadAnimation({ container: node, renderer: 'svg', loop: false, autoplay: true, path: node.dataset.lottie });
        if (reduced) animation.setSpeed(0);
    });
})();
