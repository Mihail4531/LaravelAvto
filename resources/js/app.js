import './bootstrap';

/**
 * Scroll-reveal: элементы с [data-reveal] проявляются при попадании
 * во вьюпорт. Уверенно, с весом — без отскоков.
 */
function initReveal() {
    const items = document.querySelectorAll('[data-reveal]:not(.is-visible)');
    if (!items.length) return;

    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );

    items.forEach((el) => observer.observe(el));
}

/**
 * Одометр: числа с [data-odometer] «накручиваются» до целевого значения,
 * как пробег на приборной панели, когда попадают во вьюпорт.
 * Формат совпадает с number_format(n, dec, '.', ' ').
 */
function fmtNum(value, decimals, group = true) {
    const fixed = Math.max(0, value).toFixed(decimals);
    const [int, frac] = fixed.split('.');
    const grouped = group ? int.replace(/\B(?=(\d{3})+(?!\d))/g, ' ') : int;
    return frac ? grouped + '.' + frac : grouped;
}

function initOdometer() {
    const items = document.querySelectorAll('[data-odometer]:not(.is-counted)');
    if (!items.length) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const run = (el) => {
        el.classList.add('is-counted');
        const target = parseFloat(el.dataset.odometer);
        if (isNaN(target)) return;
        const decimals = parseInt(el.dataset.decimals || '0', 10);
        const suffix = el.dataset.suffix || '';
        const group = el.dataset.group !== '0';
        const final = fmtNum(target, decimals, group) + suffix;

        // Датчик: дугу и стрелку этого же датчика ведём ТЕМ ЖЕ прогрессом,
        // что и число, — иначе полоска «убегает» вперёд от цифр.
        const gauge = el.closest('.stat-gauge');
        const arc = gauge && gauge.querySelector('.stat-arc');
        const needle = gauge && gauge.querySelector('.stat-needle');
        let len = 0, off = 0, a0 = 0, span = 0, frac = 0;
        if (arc) {
            const cs = getComputedStyle(arc);
            len = parseFloat(cs.getPropertyValue('--len')) || 0;
            off = parseFloat(cs.getPropertyValue('--off')) || 0;
            frac = len ? 1 - off / len : 0;
        }
        if (needle) {
            const ns = getComputedStyle(needle);
            a0 = parseFloat(ns.getPropertyValue('--a0')) || 0;
            span = parseFloat(ns.getPropertyValue('--span')) || 0;
        }
        // k — eased-прогресс 0..1, общий для числа, дуги и стрелки
        const setGauge = (k) => {
            if (arc) arc.style.strokeDashoffset = (len + (off - len) * k).toFixed(2);
            if (needle) needle.style.setProperty('--ang', (a0 + span * frac * k).toFixed(2));
        };

        if (reduce) { el.textContent = final; setGauge(1); return; }

        const dur = 5600;
        const start = performance.now();
        // easeOutCubic — счёт идёт заметно дольше и равномернее, чем у expo
        const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);
        const frame = (now) => {
            const p = Math.min(1, (now - start) / dur);
            const e = easeOutCubic(p);
            setGauge(e);
            if (p < 1) {
                el.textContent = fmtNum(target * e, decimals, group) + suffix;
                requestAnimationFrame(frame);
            } else {
                el.textContent = final;
            }
        };
        requestAnimationFrame(frame);
    };

    if (!('IntersectionObserver' in window)) { items.forEach(run); return; }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) { run(entry.target); obs.unobserve(entry.target); }
            });
        },
        { threshold: 0.4 }
    );
    items.forEach((el) => observer.observe(el));
}

function initMotion() {
    initReveal();
    initOdometer();
}

document.addEventListener('DOMContentLoaded', initMotion);
// Livewire может перерисовывать DOM — переинициализируем reveal/одометр.
document.addEventListener('livewire:navigated', initMotion);
document.addEventListener('livewire:update', initMotion);
