import Alpine from 'alpinejs';
import L from 'leaflet';

import { refreshCableMaps } from './cable-map.js';

window.Alpine = Alpine;
window.L = L;

// ---- Reusable single-location map (customer address picker / profile map) ----
// Blade renders a <div data-address-map> (kept inside wire:ignore) and, when
// editable, two hidden inputs referenced by data-lat-input / data-lng-input.
// Clicking or dragging the marker writes the coordinates into those inputs and
// dispatches an "input" event so Livewire stores them on the model.
const beePinIcon = () =>
    L.divIcon({
        className: '',
        html: '<span style="display:block;width:18px;height:18px;border-radius:9999px;background:#465FFF;border:3px solid #fff;box-shadow:0 3px 10px rgba(70,95,255,.45);"></span>',
        iconSize: [18, 18],
        iconAnchor: [9, 9],
    });

const beeTileLayer = (map) =>
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

function initBeeLocationMap(container) {
    if (container.dataset.beeMapReady === '1') {
        return;
    }

    const editable = container.dataset.editable === '1';
    const latInput = container.dataset.latInput ? document.querySelector(container.dataset.latInput) : null;
    const lngInput = container.dataset.lngInput ? document.querySelector(container.dataset.lngInput) : null;

    let lat = parseFloat(container.dataset.lat || '');
    let lng = parseFloat(container.dataset.lng || '');
    let hasCoords = Number.isFinite(lat) && Number.isFinite(lng);

    if (!hasCoords && latInput && lngInput && latInput.value !== '' && lngInput.value !== '') {
        lat = parseFloat(latInput.value);
        lng = parseFloat(lngInput.value);
        hasCoords = Number.isFinite(lat) && Number.isFinite(lng);
    }

    if (!hasCoords) {
        lat = 23.685;
        lng = 90.3563;
    }

    const map = L.map(container, { scrollWheelZoom: false }).setView([lat, lng], hasCoords ? 15 : 7);
    beeTileLayer(map);

    let marker = null;

    const placeMarker = (newLat, newLng, { fly = false } = {}) => {
        if (!Number.isFinite(newLat) || !Number.isFinite(newLng)) {
            return;
        }
        if (marker) {
            marker.setLatLng([newLat, newLng]);
        } else {
            marker = L.marker([newLat, newLng], { icon: beePinIcon(), draggable: editable }).addTo(map);
            marker.on('dragend', () => {
                const { lat: mkLat, lng: mkLng } = marker.getLatLng();
                syncInputs(mkLat, mkLng);
            });
        }
        if (fly) {
            map.flyTo([newLat, newLng], 15);
        }
    };

    const syncInputs = (newLat, newLng) => {
        if (latInput) {
            latInput.value = newLat.toFixed(6);
            latInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (lngInput) {
            lngInput.value = newLng.toFixed(6);
            lngInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };

    if (editable) {
        map.on('click', (event) => {
            placeMarker(event.latlng.lat, event.latlng.lng, { fly: true });
            syncInputs(event.latlng.lat, event.latlng.lng);
        });
    }

    if (hasCoords) {
        placeMarker(lat, lng);
    }

    container.dataset.beeMapReady = '1';
    container._beeMap = map;
}

function refreshBeeLocationMaps() {
    document.querySelectorAll('[data-address-map]').forEach(initBeeLocationMap);
}

window.refreshBeeLocationMaps = refreshBeeLocationMaps;

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

// Copy a BeeCore online payment link (bKash) with clipboard fallback for http.
window.copyBeePayLink = async (url, trigger) => {
    try {
        await navigator.clipboard.writeText(url);
    } catch (e) {
        const textarea = document.createElement('textarea');
        textarea.value = url;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
        } catch (_) {
            // Clipboard unavailable (older browsers) — user can copy manually.
        }
        textarea.remove();
    }

    if (trigger) {
        const previousTitle = trigger.getAttribute('title');
        trigger.setAttribute('title', 'Payment link copied');
        trigger.classList.add('text-pink-600', 'border-pink-400', 'dark:text-pink-400');
        window.setTimeout(() => {
            trigger.setAttribute('title', previousTitle || '');
            trigger.classList.remove('text-pink-600', 'border-pink-400', 'dark:text-pink-400');
        }, 1400);
    }
};

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
            refreshCableMaps();
            refreshBeeLocationMaps();
        });
    });
}

// Hard navigation fallback for hosted bKash checkout. Livewire components raise
// "bee-pay-open" after the order is placed; we force a full page load so the
// browser reliably lands on the BeeCore payment page (no SPA redirect issues).
function registerBeePayRedirect() {
    if (window.Livewire && typeof window.Livewire.on === 'function') {
        window.Livewire.on('bee-pay-open', (url) => {
            if (typeof url === 'string' && (url.startsWith('/') || /^https?:\/\//.test(url))) {
                window.location.assign(url);
            }
        });
    }
}

if (window.Livewire && typeof window.Livewire.on === 'function') {
    registerBeePayRedirect();
} else {
    document.addEventListener('livewire:init', registerBeePayRedirect);
}

document.addEventListener('DOMContentLoaded', () => {
    renderApex(document).catch(() => {});
    animateNumbers(document);
    registerLivewireHooks();
    refreshCableMaps();
    refreshBeeLocationMaps();
});
