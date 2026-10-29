'use strict';

// Adaptación para la firma del footer de las curvas y pétalos de Type Garden
// de Akshat Agarwal, a partir del código facilitado por el usuario.
// El dibujo se limita a este canvas: no captura escritura ni modifica la página.
(() => {
    const clamp = (value) => Math.max(0, Math.min(1, value));
    const ease = (value) => 1 - Math.pow(1 - clamp(value), 3);
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    document.querySelectorAll('[data-rose-signature]').forEach((button) => {
        const canvas = button.querySelector('canvas');
        const text = button.querySelector('span');
        const context = canvas?.getContext('2d');
        if (!context || !text) return;
        let frame = 0;
        let started = 0;
        let active = false;
        let flowers = [];
        let width = 0;
        let height = 0;

        // Curvas suavizadas del dibujo de referencia.
        const path = (points, closed = false) => {
            if (points.length < 2) return;
            const middle = (a, b) => [(a[0] + b[0]) / 2, (a[1] + b[1]) / 2];
            context.beginPath();
            if (closed) {
                const first = middle(points[points.length - 1], points[0]);
                context.moveTo(...first);
                points.forEach((point, index) => context.quadraticCurveTo(...point, ...middle(point, points[(index + 1) % points.length])));
                context.closePath();
            } else {
                context.moveTo(...points[0]);
                for (let index = 1; index < points.length - 1; index++) context.quadraticCurveTo(...points[index], ...middle(points[index], points[index + 1]));
                context.lineTo(...points[points.length - 1]);
            }
        };
        const curve = (flower, progress) => {
            const points = [];
            for (let index = 0; index <= 30; index++) {
                const t = progress * index / 30;
                const u = 1 - t;
                points.push([
                    u * u * u * flower.x + 3 * u * u * t * (flower.x + flower.lean) + 3 * u * t * t * (flower.tipX - flower.lean) + t * t * t * flower.tipX,
                    u * u * u * flower.y + 3 * u * u * t * (flower.y - 12) + 3 * u * t * t * (flower.tipY + 15) + t * t * t * flower.tipY,
                ]);
            }
            return points;
        };
        const leaf = (x, y, angle, length) => {
            if (length < .2) return;
            context.save();
            context.translate(x, y);
            context.rotate(angle);
            context.beginPath();
            context.moveTo(0, 0);
            context.quadraticCurveTo(length * .35, -length * .48, length, 0);
            context.quadraticCurveTo(length * .45, length * .25, 0, 0);
            context.fillStyle = '#66736b';
            context.fill();
            context.restore();
        };
        const rose = (flower, age) => {
            const petals = [
                [0, -.36, .56, .54, 0], [-.5, -.06, .6, .6, -.35],
                [.52, -.06, .6, .6, .35], [-.42, .3, .62, .48, .2],
                [.47, .3, .62, .48, -.2], [.05, .42, .56, .38, 0],
            ];
            context.save();
            context.translate(flower.tipX, flower.tipY);
            context.rotate(flower.lean * .025);
            petals.forEach((petal, index) => {
                const growth = ease((age - index * 65) / 470);
                if (!growth) return;
                const angle = petal[4] + (1 - growth) * (petal[0] < 0 ? .5 : -.5);
                const points = Array.from({ length: 26 }, (_, step) => {
                    const theta = step / 26 * Math.PI * 2;
                    const ripple = 1 + .08 * Math.sin(3 * theta + flower.phase + index) + .04 * Math.sin(5 * theta + flower.phase);
                    const x = Math.cos(theta) * petal[2] * ripple;
                    const y = Math.sin(theta) * petal[3] * ripple;
                    return [(petal[0] + x * Math.cos(angle) - y * Math.sin(angle)) * flower.radius * growth,
                        (.5 + (petal[1] + x * Math.sin(angle) + y * Math.cos(angle) - .5) * growth) * flower.radius];
                });
                path(points, true);
                context.fillStyle = ['#28232e', '#302935', '#3b303f', '#251f2a', '#332837', '#211b25'][index];
                context.fill();
            });
            const spiral = ease((age - 420) / 380);
            if (spiral) {
                const points = Array.from({ length: 54 }, (_, index) => {
                    const u = index / 53 * spiral;
                    const theta = flower.phase + u * Math.PI * 2.6;
                    const radius = flower.radius * (.08 + .57 * u);
                    return [Math.cos(theta) * radius * 1.05, Math.sin(theta) * radius * .72 - .12 * flower.radius];
                });
                path(points);
                context.strokeStyle = '#b3a1b3';
                context.lineWidth = .65;
                context.stroke();
            }
            context.restore();
        };
        const draw = (elapsed) => {
            context.clearRect(0, 0, width, height);
            context.lineCap = 'round';
            context.lineJoin = 'round';
            flowers.forEach((flower, index) => {
                const age = elapsed - index * 75;
                const growth = ease(age / 650);
                if (!growth) return;
                path(curve(flower, growth));
                context.strokeStyle = '#718076';
                context.lineWidth = .9;
                context.stroke();
                [.4, .7].forEach((position, leafIndex) => {
                    const point = curve(flower, position).at(-1);
                    leaf(point[0], point[1], leafIndex ? -2.4 : -.7, 9 * ease((growth - position) * 4));
                });
                rose(flower, age - 480);
            });
        };
        const resize = () => {
            const bounds = button.getBoundingClientRect();
            width = bounds.width;
            height = 50;
            const ratio = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.round(width * ratio);
            canvas.height = Math.round(height * ratio);
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            const style = getComputedStyle(text);
            context.font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
            const label = text.textContent;
            const left = (width - context.measureText(label).width) / 2;
            flowers = [0, 6, 11, 16].map((index, order) => {
                const x = left + context.measureText(label.slice(0, index)).width + context.measureText(label[index]).width / 2;
                return { x, y: height - 10, tipX: x + Math.sin(order * 2.1) * 5, tipY: 15 + (order % 2) * 7,
                    lean: Math.sin(order * 3 + 2) * 8, radius: order % 2 ? 3.5 : 4.5, phase: order * 1.7 };
            });
            draw(active ? 3000 : 0);
        };
        const stop = () => { cancelAnimationFrame(frame); frame = 0; };
        const play = () => {
            if (document.hidden || frame) return;
            active = true;
            if (reducedMotion.matches) { draw(3000); return; }
            started = performance.now();
            const tick = (time) => {
                const elapsed = time - started;
                draw(elapsed);
                if (elapsed < 2200 && active && !document.hidden) frame = requestAnimationFrame(tick);
                else { frame = 0; draw(active ? 3000 : 0); }
            };
            frame = requestAnimationFrame(tick);
        };
        resize();
        const reset = () => { stop(); active = false; draw(0); };
        button.addEventListener('pointerenter', play);
        button.addEventListener('pointerleave', () => { if (!button.matches(':focus-visible')) reset(); });
        button.addEventListener('focus', () => { if (button.matches(':focus-visible')) play(); });
        button.addEventListener('blur', () => { if (!button.matches(':hover')) reset(); });
        document.addEventListener('visibilitychange', () => { if (document.hidden) reset(); });
        reducedMotion.addEventListener('change', () => { stop(); draw(active ? 3000 : 0); });
        if ('ResizeObserver' in window) new ResizeObserver(resize).observe(button);
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(([entry]) => {
                if (!entry.isIntersecting) reset();
            }, { threshold: .5 }).observe(button);
        }
        document.fonts?.ready.then(resize);
    });
})();
