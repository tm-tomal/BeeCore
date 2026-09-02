import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Livewire boots its own Alpine instance on components, so we only start
// Alpine manually on standalone pages (auth, static views) that lack one.
if (!document.querySelector('[wire\\:snapshot], [wire\\:id]')) {
    Alpine.start();
}

// ---- ApexCharts helper used by dashboard blades (lazy-loaded) ----
const mountedCharts = [];
let apexModulePromise = null;

const themeMode = () => (localStorage.getItem('beecore_theme') === 'dark' ? 'dark' : 'light');

async function loadApexCharts() {
    if (apexModulePromise === null) {
        apexModulePromise = import('apexcharts');
    }
    const module = await apexModulePromise;
    return module.default;
}

async function renderApex(root = document) {
    const elements = [...root.querySelectorAll('[data-apex]:not([data-apex-rendered])')];
    if (elements.length === 0) {
        return;
    }

    const Apex = await loadApexCharts();
    window.ApexCharts = Apex;

    const mode = themeMode();
    const base = {
        theme: { mode },
        chart: {
            fontFamily: 'Outfit, sans-serif',
            toolbar: { show: false },
            zoom: { enabled: false },
            background: 'transparent',
            foreColor: mode === 'dark' ? '#98A2B3' : '#64748B',
            animations: {
                enabled: true,
                speed: 550,
                animateGradually: { enabled: true, delay: 90 },
                dynamicAnimation: { enabled: true, speed: 400 },
            },
        },
        grid: {
            borderColor: mode === 'dark' ? 'rgba(255,255,255,0.08)' : '#EEF2F6',
            strokeDashArray: 4,
            padding: { left: 8, right: 8 },
        },
        dataLabels: { enabled: false },
        legend: {
            labels: { colors: mode === 'dark' ? '#98A2B3' : '#475467' },
            markers: { size: 4 },
        },
    };

    elements.forEach((el) => {
        let cfg = {};
        try {
            cfg = JSON.parse(el.dataset.apex);
        } catch (e) {
            return;
        }
        if (cfg.series === undefined) {
            return;
        }
        const merged = {
            ...base,
            ...cfg,
            chart: { ...(base.chart || {}), ...(cfg.chart || {}) },
            grid: cfg.grid === false ? { show: false } : { ...(base.grid || {}), ...(cfg.grid || {}) },
        };
        if (cfg.legend === false) {
            merged.legend = { show: false };
        }
        const chart = new Apex(el, merged);
        chart.render();
        mountedCharts.push({ el, chart });
        el.dataset.apexRendered = '1';
        el._apexSignature = el.dataset.apex;
    });
}

// Re-render only charts whose data changed after a Livewire commit (range switch, filters).
function refreshCharts() {
    const candidates = [...document.querySelectorAll('[data-apex]')];
    candidates.forEach((el) => {
        if (!el._apexSignature) {
            return;
        }
        if (el.dataset.apex === el._apexSignature) {
            return;
        }
        const index = mountedCharts.findIndex((item) => item.el === el);
        if (index !== -1) {
            mountedCharts[index].chart.destroy();
            mountedCharts.splice(index, 1);
        }
        delete el.dataset.apexRendered;
        delete el._apexSignature;
    });
    renderApex(document).catch(() => {});
}

window.renderApex = renderApex;

window.addEventListener('beecore:theme', () => {
    const mode = themeMode();
    mountedCharts.forEach(({ chart }) => chart.updateOptions({ theme: { mode } }));
});

// Smooth count-up for metric values annotated with data-count.
function animateNumbers(root = document) {
    const items = root.querySelectorAll('[data-count]');
    if (items.length === 0) {
        return;
    }
    items.forEach((el) => {
        const target = parseFloat(el.dataset.count);
        if (!Number.isFinite(target)) {
            return;
        }
        const decimals = parseInt(el.dataset.decimals || '0', 10);
        const duration = 700;
        const startedAt = performance.now();
        const format = (value) =>
            value.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        const step = (now) => {
            const progress = Math.min(1, (now - startedAt) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = format(target * eased);
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };
        requestAnimationFrame(step);
    });
}

function registerLivewireHooks() {
    if (!window.Livewire || typeof window.Livewire.hook !== 'function') {
        return;
    }
    window.Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            refreshCharts();
            animateNumbers(document);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderApex(document).catch(() => {});
    animateNumbers(document);
    registerLivewireHooks();
});
