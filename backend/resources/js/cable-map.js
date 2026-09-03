import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Bangladeshi fallback centre.
const FALLBACK_CENTER = [23.685, 90.3563];
const FALLBACK_ZOOM = 7;

const mapRegistry = new Map(); // element -> { sig, map }

// Livewire morphs its own attributes but must never touch Leaflet's panes.
// Maps are rendered into an inner wire:ignore canvas inside [data-cable-map].
function mapCanvas(el) {
    return el.querySelector('.cable-map-canvas') || el;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function routeIcon() {
    return L.divIcon({
        className: '',
        html: '<span style="display:grid;place-items:center;width:30px;height:30px;border-radius:9999px;background:#465FFF;color:#fff;font-weight:800;font-size:12px;box-shadow:0 4px 10px rgba(70,95,255,.35);border:2px solid #fff;">F</span>',
        iconSize: [30, 30],
        iconAnchor: [15, 15],
        popupAnchor: [0, -18],
    });
}

function splitterIcon(openIssues, used, total) {
    const color = openIssues > 0 ? '#F04438' : (used >= total && total > 0 ? '#F79009' : '#12B76A');
    return L.divIcon({
        className: '',
        html: `<span style="display:grid;place-items:center;width:26px;height:26px;border-radius:9999px;background:${color};color:#fff;font-weight:700;font-size:10px;box-shadow:0 3px 8px rgba(0,0,0,.18);border:2px solid #fff;">${used}</span>`,
        iconSize: [26, 26],
        iconAnchor: [13, 13],
        popupAnchor: [0, -15],
    });
}

function pickerIcon() {
    return L.divIcon({
        className: '',
        html: '<span style="display:block;width:16px;height:16px;border-radius:9999px;background:#465FFF;border:3px solid #fff;box-shadow:0 3px 10px rgba(70,95,255,.4);"></span>',
        iconSize: [16, 16],
        iconAnchor: [8, 8],
    });
}

const tiles = (map) =>
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

function parseJson(attribute) {
    try {
        return JSON.parse(attribute);
    } catch (e) {
        return null;
    }
}

function buildOverviewMap(el, payload) {
    const map = L.map(mapCanvas(el), { scrollWheelZoom: true }).setView(FALLBACK_CENTER, FALLBACK_ZOOM);
    tiles(map);

    const routes = payload.routes || [];
    const splitters = payload.splitters || [];

    const markers = [];
    const routeById = new Map();

    routes.forEach((route) => {
        if (route.lat == null || route.lng == null) return;
        routeById.set(route.id, route);
        const marker = L.marker([route.lat, route.lng], { icon: routeIcon() }).addTo(map);
        const path = route.source || route.destination
            ? `${escapeHtml(route.source || '?')} → ${escapeHtml(route.destination || '?')}`
            : '';
        marker.bindPopup(`
            <div style="min-width:180px;font-family:'Outfit',sans-serif;">
                <p style="margin:0;font-weight:700;color:#1D2939;">${escapeHtml(route.name)}</p>
                ${path ? `<p style="margin:4px 0 0;font-size:12px;color:#667085;">${path}</p>` : ''}
                <p style="margin:4px 0 0;font-size:12px;color:#667085;">${route.cores ? `Cores: ${route.cores}` : ''}${route.km ? `${route.cores ? ' · ' : ''}${route.km} km` : ''}</p>
                ${route.issues > 0 ? `<p style="margin:6px 0 0;font-size:12px;font-weight:700;color:#F04438;">${route.issues} open issue(s)</p>` : ''}
                <div style="margin-top:8px;display:flex;gap:6px;">
                    <a href="#" onclick="event.preventDefault();window.cableMapActions.editRoute(${route.id});return false;" style="font-size:12px;font-weight:600;color:#465FFF;">Edit</a>
                    <a href="#" onclick="event.preventDefault();window.cableMapActions.reportRoute(${route.id});return false;" style="font-size:12px;font-weight:600;color:#F04438;">Report issue</a>
                </div>
            </div>
        `);
        markers.push(marker);
    });

    const splitterById = new Map();
    splitters.forEach((splitter) => {
        if (splitter.lat == null || splitter.lng == null) return;
        splitterById.set(splitter.id, splitter);
        const marker = L.marker([splitter.lat, splitter.lng], {
            icon: splitterIcon(splitter.issues, splitter.used, splitter.total),
        }).addTo(map);
        const extra = splitter.location ? `<p style="margin:4px 0 0;font-size:12px;color:#667085;">${escapeHtml(splitter.location)}</p>` : '';
        marker.bindPopup(`
            <div style="min-width:190px;font-family:'Outfit',sans-serif;">
                <p style="margin:0;font-weight:700;color:#1D2939;">${escapeHtml(splitter.name)}</p>
                ${extra}
                <p style="margin:4px 0 0;font-size:12px;color:#667085;">Ports: <b>${splitter.used}</b>/${splitter.total} used</p>
                ${splitter.route ? `<p style="margin:2px 0 0;font-size:12px;color:#98A2B3;">Fiber: ${escapeHtml(splitter.route)}</p>` : ''}
                ${splitter.issues > 0 ? `<p style="margin:4px 0 0;font-size:12px;font-weight:700;color:#F04438;">${splitter.issues} open issue(s)</p>` : ''}
                <div style="margin-top:8px;display:flex;gap:6px;">
                    <a href="#" onclick="event.preventDefault();window.cableMapActions.editSplitter(${splitter.id});return false;" style="font-size:12px;font-weight:600;color:#465FFF;">Edit & ports</a>
                    <a href="#" onclick="event.preventDefault();window.cableMapActions.reportSplitter(${splitter.id});return false;" style="font-size:12px;font-weight:600;color:#F04438;">Issue</a>
                </div>
            </div>
        `);
        markers.push(marker);
    });

    // Draw a fiber line from every route point to its splitters.
    splitters.forEach((splitter) => {
        const route = routeById.get(splitter.route_id);
        if (!route || splitter.lat == null || splitter.lng == null) return;
        L.polyline(
            [
                [route.lat, route.lng],
                [splitter.lat, splitter.lng],
            ],
            { color: '#465FFF', weight: 2, opacity: 0.45, dashArray: '4 6' },
        ).addTo(map);
    });

    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds(), { padding: [40, 40] });
    }

    return map;
}

function refreshOverviewMaps() {
    document.querySelectorAll('[data-cable-map]').forEach((el) => {
        const payload = parseJson(el.dataset.payload);
        if (!payload) return;

        const entry = mapRegistry.get(el);
        if (entry && entry.sig === el.dataset.sig && entry.map) {
            return; // unchanged, keep the existing map instance
        }
        if (entry?.map) {
            entry.map.remove();
            mapRegistry.delete(el);
        }

        const map = buildOverviewMap(el, payload);
        mapRegistry.set(el, { map, sig: el.dataset.sig });
    });
}

// Drop maps whose host element left the DOM (Livewire view switches, etc.).
function purgeDetachedMaps() {
    mapRegistry.forEach((entry, el) => {
        if (document.body.contains(el)) {
            return;
        }
        try {
            entry.map.remove();
        } catch (e) {
            /* noop */
        }
        mapRegistry.delete(el);
    });
}

function initLocationPickers(root = document) {
    root.querySelectorAll('[data-location-picker]').forEach((el) => {
        if (el.dataset.pickerReady === '1') return;

        const latInput = document.getElementById(el.dataset.targetLat);
        const lngInput = document.getElementById(el.dataset.targetLng);
        if (!latInput || !lngInput) return;

        const map = L.map(el).setView(FALLBACK_CENTER, 13);
        tiles(map);
        mapRegistry.set(el, { map, sig: null });

        // The readout + clear button live in the same form block (not inside the
        // picker div itself), so resolve them from the picker's parent scope.
        const scope = el.parentElement || el;
        const readout = scope.querySelector('[data-coord-readout]');
        let marker = null;

        const setValue = (lat, lng, center = true) => {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
            latInput.dispatchEvent(new Event('input', { bubbles: true }));
            lngInput.dispatchEvent(new Event('input', { bubbles: true }));

            if (!marker) {
                marker = L.marker([lat, lng], { icon: pickerIcon() }).addTo(map);
            } else {
                marker.setLatLng([lat, lng]);
            }
            if (center) map.setView([lat, lng], Math.max(map.getZoom(), 15));

            if (readout) {
                readout.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                readout.classList.remove('text-gray-400');
            }
        };

        const clearControl = scope.querySelector('[data-clear-location]');
        if (clearControl) {
            clearControl.addEventListener('click', (event) => {
                event.preventDefault();
                latInput.value = '';
                lngInput.value = '';
                latInput.dispatchEvent(new Event('input', { bubbles: true }));
                lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
                if (readout) {
                    readout.textContent = '—';
                    readout.classList.add('text-gray-400');
                }
                map.setView(FALLBACK_CENTER, 13);
            });
        }

        map.on('click', (event) => {
            const { lat, lng } = event.latlng;
            setValue(lat, lng);
        });

        const initialLat = parseFloat(el.dataset.initialLat);
        const initialLng = parseFloat(el.dataset.initialLng);
        if (Number.isFinite(initialLat) && Number.isFinite(initialLng)) {
            setValue(initialLat, initialLng);
        }

        el.dataset.pickerReady = '1';
    });
}

export function refreshCableMaps() {
    purgeDetachedMaps();
    refreshOverviewMaps();
    initLocationPickers(document);
}

// Livewire actions reachable from Leaflet popups.
window.cableMapActions = {
    editRoute(id) {
        window.Livewire?.first()?.call('editRoute', id);
    },
    reportRoute(id) {
        window.Livewire?.first()?.call('createIssue', 'route', id);
    },
    editSplitter(id) {
        window.Livewire?.first()?.call('editSplitter', id);
    },
    reportSplitter(id) {
        window.Livewire?.first()?.call('createIssue', 'splitter', null, id);
    },
};
