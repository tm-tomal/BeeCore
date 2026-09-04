import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

import { refreshCableMaps } from './cable-map.js';

// GoogleMutant (Google tiles for Leaflet) expects a global `L`, so it is
// imported lazily below — only after `window.L` is assigned.
window.L = L;

// ---- Reusable single-location map (customer address picker / profile map) ----
// Blade renders a <div data-address-map> (kept inside wire:ignore) and, when
// editable, two hidden inputs referenced by data-lat-input / data-lng-input.
// Picking a point (click, drag, "my location", search) writes coordinates into
// those inputs and dispatches an "input" event so Livewire stores them.
//
// The picker also works like a real map picker: pan the map left/right and as
// soon as it stops, the pin drops at the new map centre automatically.
const beeThemeMode = () => (localStorage.getItem('beecore_theme') === 'dark' ? 'dark' : 'light');

// Google Maps (rich data) powers the basemap. A public browser key is used —
// the Maps JavaScript API is loaded lazily only when a BeeCore map is on page.
const GOOGLE_MAPS_KEY = 'AIzaSyBofrHFIF3UDYAgl2UCl_DeosPwH1mQbnI';

// "Dark mode" is done with Google styled-map rules (same roadmap, dark paint).
const beeGoogleDarkStyles = [
    { elementType: 'geometry', stylers: [{ color: '#1d2028' }] },
    { elementType: 'labels.text.stroke', stylers: [{ color: '#1d2028' }] },
    { elementType: 'labels.text.fill', stylers: [{ color: '#eceff4' }] },
    { featureType: 'administrative', elementType: 'geometry', stylers: [{ color: '#2c3039' }] },
    { featureType: 'administrative.country', elementType: 'labels.text.fill', stylers: [{ color: '#a5abb8' }] },
    { featureType: 'poi', elementType: 'geometry', stylers: [{ color: '#262b34' }] },
    { featureType: 'poi', elementType: 'labels.text.fill', stylers: [{ color: '#838b99' }] },
    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#3a414d' }] },
    { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#c3c9d4' }] },
    { featureType: 'transit', elementType: 'geometry', stylers: [{ color: '#2f353f' }] },
    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#10131a' }] },
    { featureType: 'water', elementType: 'labels.text.fill', stylers: [{ color: '#565e6e' }] },
];

// Maps currently on screen; used to hot-swap the tile theme when the app theme changes.
const beeMapThemeables = [];

let googleReadyPromise = null;
const ensureGoogleMaps = () => {
    if (googleReadyPromise) return googleReadyPromise;

    googleReadyPromise = new Promise((resolve) => {
        if (window.google && window.google.maps) {
            resolve();
            return;
        }
        const callback = '__beeGoogleMapsReady';
        window[callback] = () => resolve();
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_KEY}&v=weekly&loading=async&callback=${callback}`;
        script.async = true;
        script.defer = true;
        script.onerror = () => {
            window[callback] = undefined;
            resolve();
        };
        document.head.appendChild(script);
    });

    return googleReadyPromise;
};

const beeTileLayer = (map) => {
    const layer = new L.GridLayer.GoogleMutant({
        type: 'roadmap',
        maxZoom: 21,
        styles: beeThemeMode() === 'dark' ? beeGoogleDarkStyles : [],
        // Keep Google's own floating controls off — Leaflet supplies the zoom.
        disableDefaultUI: true,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
    });
    return layer.addTo(map);
};

// If Google ever fails to load (wrong key / URL restriction / no billing),
// the map still works on plain OpenStreetMap instead of staying blank.
const beeFallbackLayer = (map) =>
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

const beePinIcon = () =>
    L.divIcon({
        className: 'bee-pin-icon',
        html: `<svg width="26" height="39" viewBox="0 0 26 39" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;filter:drop-shadow(0 6px 10px rgba(15,23,42,.35));">
                <path d="M13 2C6.7 2 1.5 7.2 1.5 13.5 1.5 22.8 13 37 13 37s11.5-14.2 11.5-23.5C24.5 7.2 19.3 2 13 2Z" fill="#465FFF"/>
                <path d="M13 2C6.7 2 1.5 7.2 1.5 13.5 1.5 22.8 13 37 13 37s11.5-14.2 11.5-23.5C24.5 7.2 19.3 2 13 2Z" fill="url(#beePinGrad)"/>
                <circle cx="13" cy="12.8" r="5.4" fill="#fff"/>
                <circle cx="13" cy="12.8" r="2.3" fill="#465FFF"/>
                <defs><linearGradient id="beePinGrad" x1="13" y1="2" x2="13" y2="37" gradientUnits="userSpaceOnUse"><stop stop-color="#5B6CFF"/><stop offset="1" stop-color="#3143E0"/></linearGradient></defs>
              </svg>`,
        iconSize: [26, 39],
        iconAnchor: [13, 38],
        popupAnchor: [0, -34],
    });

// Nominatim (OpenStreetMap) helpers — free geocoding, no key needed.
// We restrict every lookup to Bangladesh so we never jump to a world view.
const beeBdFallback = { lat: 23.7808, lng: 90.3934 };

const beeJson = async (url) => {
    try {
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) return [];
        return await response.json();
    } catch (error) {
        return [];
    }
};

const beeSearchAddress = (query) =>
    beeJson(
        `https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=6&countrycodes=bd&q=${encodeURIComponent(query)}`,
    );

const beeReverseAddress = (lat, lng) =>
    beeJson(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&zoom=18&lat=${lat}&lng=${lng}`);

const beeDistrictOf = (address = {}) =>
    address.state_district || address.county || address.city || address.town || address.village || address.state || '';

const beeAreaOf = (address = {}) => address.suburb || address.neighbourhood || address.quarter || address.city_district || '';

const beeSetField = (selector, value) => {
    if (!selector) return;
    const element = document.querySelector(selector);
    if (!element) return;
    const clean = String(value ?? '').trim();
    if (clean !== '') {
        element.value = clean;
    }
    // Livewire wire:model listens for the input event on text fields.
    element.dispatchEvent(new Event('input', { bubbles: true }));
};

// Custom BeeCore pin rendered with the original Google Maps API.
const beeGooglePinUrl = () => {
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="39" viewBox="0 0 26 39" fill="none">
        <defs><linearGradient id="beePinGrad" x1="13" y1="2" x2="13" y2="37" gradientUnits="userSpaceOnUse">
            <stop stop-color="#5B6CFF"/><stop offset="1" stop-color="#3143E0"/>
        </linearGradient></defs>
        <path d="M13 2C6.7 2 1.5 7.2 1.5 13.5 1.5 22.8 13 37 13 37s11.5-14.2 11.5-23.5C24.5 7.2 19.3 2 13 2Z" fill="url(#beePinGrad)"/>
        <path d="M13 2C6.7 2 1.5 7.2 1.5 13.5 1.5 22.8 13 37 13 37s11.5-14.2 11.5-23.5C24.5 7.2 19.3 2 13 2Z" stroke="#FFFFFF" stroke-opacity="0.35" stroke-width="1"/>
        <circle cx="13" cy="12.8" r="5.4" fill="#fff"/>
        <circle cx="13" cy="12.8" r="2.3" fill="#465FFF"/>
    </svg>`;
    return `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`;
};

function initBeeLocationMap(container) {
    if (container.dataset.beeMapReady === '1') {
        return;
    }

    const editable = container.dataset.editable === '1';
    const latInput = container.dataset.latInput ? document.querySelector(container.dataset.latInput) : null;
    const lngInput = container.dataset.lngInput ? document.querySelector(container.dataset.lngInput) : null;
    const fieldSel = {
        house: container.dataset.houseInput || '',
        street: container.dataset.streetInput || '',
        area: container.dataset.areaInput || '',
        city: container.dataset.cityInput || '',
        postcode: container.dataset.postcodeInput || '',
    };

    let lat = parseFloat(container.dataset.lat || '');
    let lng = parseFloat(container.dataset.lng || '');
    let hasCoords = Number.isFinite(lat) && Number.isFinite(lng);

    if (!hasCoords && latInput && lngInput && latInput.value !== '' && lngInput.value !== '') {
        lat = parseFloat(latInput.value);
        lng = parseFloat(lngInput.value);
        hasCoords = Number.isFinite(lat) && Number.isFinite(lng);
    }

    if (!hasCoords) {
        // Bangladesh-focused default: start on Dhaka (already zoomed in), not the whole world.
        lat = beeBdFallback.lat;
        lng = beeBdFallback.lng;
    }

    const requestedZoom = parseInt(container.dataset.defaultZoom || '0', 10);
    const zoom = hasCoords ? 16 : Number.isFinite(requestedZoom) && requestedZoom > 0 ? requestedZoom : 13;

    // Original Google Maps (native JS API + real Google UI). The Leaflet
    // fallback code below is legacy/unused.
    initBeeGoogleMap(container, { editable, latInput, lngInput, fieldSel, lat, lng, hasCoords, zoom });
    return;

    const map = L.map(container, {
        // Native-app-style motion: smooth inertia when you fling the map,
        // fractional zoom steps, and animated tile fade/zoom.
        inertia: true,
        inertiaDeceleration: 3200,
        inertiaMaxSpeed: Infinity,
        easeLinearity: 0.2,
        worldCopyJump: true,
        zoomAnimation: true,
        zoomAnimationThreshold: 4,
        fadeAnimation: true,
        markerZoomAnimation: true,
        scrollWheelZoom: true,
        touchZoom: true,
        doubleClickZoom: true,
        zoomControl: true,
        maxZoom: 21,
    }).setView([lat, lng], zoom);

    // Google tiles load asynchronously (Maps JS API) and follow the app theme.
    let tileLayer = null;
    let themePending = false;

    const attachTiles = () => {
        if (!(window.google && window.google.maps)) {
            themePending = true; // attach right after the API finishes loading
            return;
        }
        themePending = false;
        if (tileLayer) {
            tileLayer.remove();
        }
        tileLayer = beeTileLayer(map);
    };

    const themeable = { apply: attachTiles };
    beeMapThemeables.push(themeable);
    map.on('remove', () => {
        const index = beeMapThemeables.indexOf(themeable);
        if (index > -1) beeMapThemeables.splice(index, 1);
    });

    ensureGoogleMaps()
        .then(() => {
            if (!container._leaflet_id) return; // map removed while Google was loading
            if (window.google && window.google.maps) {
                attachTiles();
            } else {
                tileLayer = beeFallbackLayer(map);
            }
        })
        .catch(() => {
            if (!container._leaflet_id) return;
            tileLayer = beeFallbackLayer(map);
        });

    let marker = null;
    let deepTimer = null;

    const scheduleReverseFill = (deep = false) => {
        if (deepTimer) {
            clearTimeout(deepTimer);
        }
        deepTimer = setTimeout(() => beeReverseFill(deep), 450);
    };

    // Reverse geocode the dropped pin so the district/city auto-detects
    // (no more typing it by hand or keeping a country-wide map open).
    const beeReverseFill = async (deep = false) => {
        const current = marker ? marker.getLatLng() : null;
        if (!current) return;
        const results = await beeReverseAddress(current.lat, current.lng);
        const place = results && results[0];
        if (!place || !place.address) return;
        const address = place.address;
        beeSetField(fieldSel.city, beeDistrictOf(address));
        beeSetField(fieldSel.area, beeAreaOf(address));
        beeSetField(fieldSel.postcode, address.postcode);
        if (deep) {
            beeSetField(fieldSel.house, address.house_number || '');
            beeSetField(fieldSel.street, address.road || address.pedestrian || address.footway || '');
        }
    };

    const placeMarker = (newLat, newLng, { fly = false } = {}) => {
        if (!Number.isFinite(newLat) || !Number.isFinite(newLng)) {
            return;
        }
        if (marker) {
            marker.setLatLng([newLat, newLng]);
        } else {
            marker = L.marker([newLat, newLng], { icon: beePinIcon(), draggable: editable }).addTo(map);
            if (editable) {
                marker.on('dragend', () => {
                    const { lat: markerLat, lng: markerLng } = marker.getLatLng();
                    syncInputs(markerLat, markerLng);
                    scheduleReverseFill(false);
                });
            }
        }
        if (fly) {
            map.flyTo([newLat, newLng], 16);
        } else {
            map.setView([newLat, newLng]);
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
        let panArmed = false;

        // Click any spot → pin there.
        map.on('click', (event) => {
            panArmed = false;
            placeMarker(event.latlng.lat, event.latlng.lng, { fly: true });
            syncInputs(event.latlng.lat, event.latlng.lng);
            scheduleReverseFill(false);
        });

        // Pan the map left/right/up/down → the moment it stops the pin drops
        // at the map centre (auto-place, nothing else to click).
        map.on('movestart', () => {
            panArmed = true;
        });
        map.on('moveend', () => {
            if (!panArmed) return;
            panArmed = false;
            const center = map.getCenter();
            placeMarker(center.lat, center.lng, { fly: false });
            syncInputs(center.lat, center.lng);
            scheduleReverseFill(false);
        });
    }

    if (hasCoords) {
        placeMarker(lat, lng);
    }

    // ---- Address autofill (search + pin) ----
    const shell = container.parentElement;
    const searchEl = shell ? shell.querySelector('[data-map-search]') : null;
    const resultsEl = shell ? shell.querySelector('[data-map-results]') : null;
    const locateBtn = shell ? shell.querySelector('[data-map-locate]') : null;

    if (searchEl && resultsEl) {
        let hits = [];
        let searchTimer = null;
        let clickedInside = false;

        const renderHits = () => {
            if (hits.length === 0) {
                resultsEl.innerHTML = '';
                resultsEl.classList.add('hidden');
                return;
            }
            resultsEl.innerHTML = hits
                .map(
                    (hit, index) => `
                    <button type="button" data-idx="${index}" class="flex w-full items-start gap-2 rounded-lg px-3 py-2 text-left text-theme-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                        <svg class="mt-0.5 size-4 shrink-0 stroke-current text-gray-400" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>${hit.display_name || ''}</span>
                    </button>`,
                )
                .join('');
            resultsEl.classList.remove('hidden');
        };

        const pickHit = (hit) => {
            const hitLat = parseFloat(hit.lat);
            const hitLng = parseFloat(hit.lon);
            if (Number.isFinite(hitLat) && Number.isFinite(hitLng)) {
                placeMarker(hitLat, hitLng, { fly: true });
                syncInputs(hitLat, hitLng);
            }
            const address = hit.address || {};
            beeSetField(fieldSel.house, address.house_number || '');
            beeSetField(fieldSel.street, address.road || address.pedestrian || address.footway || '');
            beeSetField(fieldSel.area, beeAreaOf(address));
            beeSetField(fieldSel.city, beeDistrictOf(address));
            beeSetField(fieldSel.postcode, address.postcode || '');
            searchEl.value = '';
            resultsEl.innerHTML = '';
            resultsEl.classList.add('hidden');
        };

        searchEl.addEventListener('input', () => {
            if (searchTimer) {
                clearTimeout(searchTimer);
            }
            const query = searchEl.value.trim();
            if (query.length < 3) {
                resultsEl.innerHTML = '';
                resultsEl.classList.add('hidden');
                return;
            }
            searchTimer = setTimeout(async () => {
                const found = await beeSearchAddress(query);
                hits = Array.isArray(found) ? found : [];
                renderHits();
            }, 450);
        });

        searchEl.addEventListener('focus', () => {
            if (hits.length > 0) {
                resultsEl.classList.remove('hidden');
            }
        });
        searchEl.addEventListener('blur', () => {
            setTimeout(() => {
                if (!clickedInside) {
                    resultsEl.classList.add('hidden');
                }
                clickedInside = false;
            }, 150);
        });

        resultsEl.addEventListener('mousedown', () => {
            clickedInside = true;
        });
        resultsEl.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-idx]');
            if (!button) return;
            pickHit(hits[parseInt(button.dataset.idx, 10)] || null);
        });
    }

    if (locateBtn && navigator.geolocation) {
        locateBtn.addEventListener('click', () => {
            locateBtn.disabled = true;
            locateBtn.style.opacity = '0.6';
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const { latitude, longitude } = position.coords;
                    placeMarker(latitude, longitude, { fly: true });
                    syncInputs(latitude, longitude);
                    scheduleReverseFill(false);
                    locateBtn.disabled = false;
                    locateBtn.style.opacity = '';
                },
                () => {
                    locateBtn.disabled = false;
                    locateBtn.style.opacity = '';
                },
                { enableHighAccuracy: true, timeout: 8000 },
            );
        });
    }

    container.dataset.beeMapReady = '1';
    container._beeMap = map;
}

function initBeeGoogleMap(container, opts) {
    const { editable, latInput, lngInput, fieldSel, lat, lng, hasCoords, zoom } = opts;

    if (container.dataset.beeMapReady === '1' || container.dataset.beeMapPending === '1') {
        return;
    }
    container.dataset.beeMapPending = '1';

    ensureGoogleMaps().then(() => {
        if (container.dataset.beeMapReady === '1') return;
        const g = window.google && window.google.maps;
        if (!g) {
            console.warn('BeeCore map: Google Maps API could not be loaded (key / billing / referrer).');
            return;
        }

        // ---- Real Google map, original UI included ----
        const map = new g.Map(container, {
            center: { lat, lng },
            zoom,
            mapTypeId: 'roadmap',
            fullscreenControl: true,
            mapTypeControl: false,
            streetViewControl: false,
            clickableIcons: false,
            styles: beeThemeMode() === 'dark' ? beeGoogleDarkStyles : [],
        });

        // Follow app theme (light/dark) without reloading the page.
        beeMapThemeables.push({
            apply: () => map.setOptions({ styles: beeThemeMode() === 'dark' ? beeGoogleDarkStyles : [] }),
        });

        container.dataset.beeMapReady = '1';
        delete container.dataset.beeMapPending;
        container._beeMap = map;

        let marker = null;
        let currentLat = null;
        let currentLng = null;
        let deepTimer = null;

        const scheduleReverseFill = (deep = false) => {
            if (deepTimer) clearTimeout(deepTimer);
            deepTimer = setTimeout(() => beeReverseFill(deep), 450);
        };

        const beeReverseFill = async (deep = false) => {
            if (!Number.isFinite(currentLat) || !Number.isFinite(currentLng)) return;
            const results = await beeReverseAddress(currentLat, currentLng);
            const place = results && results[0];
            if (!place || !place.address) return;
            const address = place.address;
            beeSetField(fieldSel.city, beeDistrictOf(address));
            beeSetField(fieldSel.area, beeAreaOf(address));
            beeSetField(fieldSel.postcode, address.postcode);
            if (deep) {
                beeSetField(fieldSel.house, address.house_number || '');
                beeSetField(fieldSel.street, address.road || address.pedestrian || address.footway || '');
            }
        };

        const ensureMarker = () => {
            if (marker) return marker;
            marker = new g.Marker({
                position: { lat: currentLat, lng: currentLng },
                map,
                draggable: editable,
                optimized: false,
                icon: {
                    url: beeGooglePinUrl(),
                    scaledSize: new g.Size(26, 39),
                    anchor: new g.Point(13, 38),
                },
            });
            if (editable) {
                g.event.addListener(marker, 'dragend', () => {
                    const p = marker.getPosition();
                    currentLat = p.lat();
                    currentLng = p.lng();
                    syncInputs(currentLat, currentLng);
                    scheduleReverseFill(false);
                });
            }
            return marker;
        };

        const setPosition = (mlat, mlng) => {
            if (!Number.isFinite(mlat) || !Number.isFinite(mlng)) return;
            currentLat = mlat;
            currentLng = mlng;
            ensureMarker().setPosition({ lat: mlat, lng: mlng });
        };

        const syncInputs = (mlat, mlng) => {
            if (latInput) {
                latInput.value = mlat.toFixed(6);
                latInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (lngInput) {
                lngInput.value = mlng.toFixed(6);
                lngInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        const placeMarker = (mlat, mlng, { fly = false } = {}) => {
            setPosition(mlat, mlng);
            if (fly) {
                map.panTo({ lat: mlat, lng: mlng });
            }
        };

        if (editable) {
            let panArmed = false;

            // Click a spot → pin there.
            g.event.addListener(map, 'click', (e) => {
                panArmed = false;
                setPosition(e.latLng.lat(), e.latLng.lng());
                syncInputs(e.latLng.lat(), e.latLng.lng());
                scheduleReverseFill(false);
            });

            // Pan the map → as soon as it stops, the pin drops at the centre.
            g.event.addListener(map, 'dragstart', () => {
                panArmed = true;
            });
            g.event.addListener(map, 'idle', () => {
                if (!panArmed) return;
                panArmed = false;
                const center = map.getCenter();
                setPosition(center.lat(), center.lng());
                syncInputs(center.lat(), center.lng());
                scheduleReverseFill(false);
            });
        }

        if (hasCoords) {
            setPosition(lat, lng);
        }

        // ---- Address search + autofill (Nominatim, free) ----
        const shell = container.parentElement;
        const searchEl = shell ? shell.querySelector('[data-map-search]') : null;
        const resultsEl = shell ? shell.querySelector('[data-map-results]') : null;
        const locateBtn = shell ? shell.querySelector('[data-map-locate]') : null;

        if (searchEl && resultsEl) {
            let hits = [];
            let searchTimer = null;
            let clickedInside = false;

            const renderHits = () => {
                if (hits.length === 0) {
                    resultsEl.innerHTML = '';
                    resultsEl.classList.add('hidden');
                    return;
                }
                resultsEl.innerHTML = hits
                    .map(
                        (hit, index) => `
                        <button type="button" data-idx="${index}" class="flex w-full items-start gap-2 rounded-lg px-3 py-2 text-left text-theme-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                            <svg class="mt-0.5 size-4 shrink-0 stroke-current text-gray-400" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>${hit.display_name || ''}</span>
                        </button>`,
                    )
                    .join('');
                resultsEl.classList.remove('hidden');
            };

            const pickHit = (hit) => {
                const hitLat = parseFloat(hit.lat);
                const hitLng = parseFloat(hit.lon);
                if (Number.isFinite(hitLat) && Number.isFinite(hitLng)) {
                    placeMarker(hitLat, hitLng, { fly: true });
                    syncInputs(hitLat, hitLng);
                }
                const address = hit.address || {};
                beeSetField(fieldSel.house, address.house_number || '');
                beeSetField(fieldSel.street, address.road || address.pedestrian || address.footway || '');
                beeSetField(fieldSel.area, beeAreaOf(address));
                beeSetField(fieldSel.city, beeDistrictOf(address));
                beeSetField(fieldSel.postcode, address.postcode || '');
                searchEl.value = '';
                resultsEl.innerHTML = '';
                resultsEl.classList.add('hidden');
            };

            searchEl.addEventListener('input', () => {
                if (searchTimer) clearTimeout(searchTimer);
                const query = searchEl.value.trim();
                if (query.length < 3) {
                    resultsEl.innerHTML = '';
                    resultsEl.classList.add('hidden');
                    return;
                }
                searchTimer = setTimeout(async () => {
                    const found = await beeSearchAddress(query);
                    hits = Array.isArray(found) ? found : [];
                    renderHits();
                }, 450);
            });

            searchEl.addEventListener('focus', () => {
                if (hits.length > 0) resultsEl.classList.remove('hidden');
            });
            searchEl.addEventListener('blur', () => {
                setTimeout(() => {
                    if (!clickedInside) resultsEl.classList.add('hidden');
                    clickedInside = false;
                }, 150);
            });

            resultsEl.addEventListener('mousedown', () => {
                clickedInside = true;
            });
            resultsEl.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-idx]');
                if (!button) return;
                pickHit(hits[parseInt(button.dataset.idx, 10)] || null);
            });
        }

        if (locateBtn && navigator.geolocation) {
            locateBtn.addEventListener('click', () => {
                locateBtn.disabled = true;
                locateBtn.style.opacity = '0.6';
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude, longitude } = position.coords;
                        placeMarker(latitude, longitude, { fly: true });
                        syncInputs(latitude, longitude);
                        scheduleReverseFill(false);
                        locateBtn.disabled = false;
                        locateBtn.style.opacity = '';
                    },
                    () => {
                        locateBtn.disabled = false;
                        locateBtn.style.opacity = '';
                    },
                    { enableHighAccuracy: true, timeout: 8000 },
                );
            });
        }
    });
}

function refreshBeeLocationMaps() {
    document.querySelectorAll('[data-address-map]').forEach(initBeeLocationMap);
}

window.refreshBeeLocationMaps = refreshBeeLocationMaps;

// Swap open map tile themes when the app light/dark theme changes.
window.addEventListener('beecore:theme', () => {
    beeMapThemeables.forEach((entry) => entry.apply());
});

// ---- Bee Searchable Select (auto-upgrade every native <select>) ----
// Turns each plain <select> into a custom, searchable dropdown. The native
// element stays in the DOM (visually hidden) so Livewire bindings
// (wire:model / wire:change) and normal form serialization keep working.
// A single body-fixed flyout is reused so dropdowns never get clipped by
// overflow/scroll containers (tables, filters).
const BeeSelectUi = (() => {
    let flyout = null;
    let listEl = null;
    let searchEl = null;
    let openSelect = null;
    let cachedOptions = [];
    let query = '';

    const labelFor = (select) => {
        const opt = select.selectedOptions && select.selectedOptions[0];
        return opt ? opt.textContent.replace(/\s+/g, ' ').trim() : '';
    };

    const isUpgradable = (select) => {
        if (!(select instanceof HTMLSelectElement)) return false;
        if (select.multiple || select.hidden) return false;
        if (select.closest('[data-bee-search-select]')) return false; // already the Blade component
        if (select.closest('.bee-select-wrap')) return false; // ours
        return true;
    };

    const ensureFlyout = () => {
        if (flyout) return;
        flyout = document.createElement('div');
        flyout.setAttribute('data-bee-flyout', '1');
        flyout.setAttribute('role', 'listbox');
        flyout.style.cssText =
            'position:fixed;z-index:120;display:none;min-width:220px;border-radius:12px;' +
            'border:1px solid var(--bee-border,#E4E7EC);background:var(--bee-bg,#fff);' +
            'color:var(--bee-text,#1D2939);color-scheme:light dark;' +
            'box-shadow:0 12px 32px -8px rgba(16,24,40,.18);overflow:hidden;';
        flyout.innerHTML = `
            <div class="bee-flyout-search" style="padding:8px;border-bottom:1px solid var(--bee-border,#E4E7EC);">
                <div style="position:relative;">
                    <span style="position:absolute;inset:0 auto 0 0;display:flex;align-items:center;padding-left:10px;color:var(--bee-muted,#98A2B3);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="search" placeholder="Search..."
                        style="width:100%;height:36px;border:1px solid var(--bee-border,#E4E7EC);border-radius:8px;padding:0 10px 0 32px;font-size:13px;outline:none;background:transparent;color:inherit;">
                </div>
            </div>
            <ul style="list-style:none;margin:0;padding:6px;max-height:240px;overflow-y:auto;"></ul>
            <p data-bee-empty style="display:none;margin:0;padding:20px 12px;text-align:center;font-size:13px;color:var(--bee-muted,#98A2B3);">No matching options.</p>`;
        searchEl = flyout.querySelector('input');
        listEl = flyout.querySelector('ul');
        document.body.appendChild(flyout);

        searchEl.addEventListener('input', () => {
            query = searchEl.value.trim().toLowerCase();
            renderList();
        });

        flyout.addEventListener('mousedown', (event) => event.preventDefault()); // keep text selection off / no native select focus steal

        listEl.addEventListener('click', (event) => {
            const button = event.target.closest('[data-option]');
            if (button) {
                choose(button.dataset.option);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
            }
        });
    };

    const renderList = () => {
        if (!openSelect) return;
        const rows = query
            ? cachedOptions.filter((o) => o.label.toLowerCase().includes(query) || o.value.toLowerCase().includes(query))
            : cachedOptions;
        listEl.innerHTML = '';
        const currentValue = String(openSelect.value);

        rows.forEach((option) => {
            const li = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.option = option.value;
            button.style.cssText =
                'display:flex;width:100%;align-items:center;justify-content:space-between;gap:8px;' +
                'border:0;background:transparent;padding:8px 10px;border-radius:8px;font-size:13px;text-align:left;cursor:pointer;';
            button.textContent = option.label;
            if (option.value === currentValue) {
                button.style.background = 'var(--bee-hover-accent, rgba(70,95,255,.08))';
                button.style.color = '#465FFF';
                button.style.fontWeight = '600';
            }
            button.addEventListener('mouseenter', () => {
                if (option.value !== currentValue) {
                    button.style.background = 'var(--bee-hover, rgba(0,0,0,.04))';
                }
            });
            button.addEventListener('mouseleave', () => {
                if (option.value !== currentValue) {
                    button.style.background = 'transparent';
                }
            });
            li.appendChild(button);
            listEl.appendChild(li);
        });

        listEl.style.display = rows.length ? '' : 'none';
        flyout.querySelector('[data-bee-empty]').style.display = rows.length ? 'none' : '';
    };

    const optionsOf = (select) => {
        const values = [];
        [...select.options].forEach((opt) => {
            values.push({ value: opt.value, label: opt.textContent.replace(/\s+/g, ' ').trim() });
        });
        return values;
    };

    const placeAndShow = (wrapper) => {
        ensureFlyout();
        const rect = wrapper.getBoundingClientRect();
        const flyoutWidth = Math.max(220, rect.width);
        const maxLeft = window.innerWidth - flyoutWidth - 8;
        flyout.style.width = `${flyoutWidth}px`;
        flyout.style.left = `${Math.max(8, Math.min(rect.left, maxLeft))}px`;
        flyout.style.top = `${Math.min(window.innerHeight - 12, rect.bottom + 6)}px`;
        flyout.style.display = 'block';

        const flyoutRect = flyout.getBoundingClientRect();
        if (flyoutRect.bottom > window.innerHeight - 8) {
            flyout.style.top = `${Math.max(8, rect.top - flyoutRect.height - 6)}px`;
        }
        searchEl.focus();
    };

    const open = (select, wrapper) => {
        close();
        ensureFlyout();
        openSelect = select;
        cachedOptions = optionsOf(select);
        query = '';
        searchEl.value = '';
        renderList();
        placeAndShow(wrapper);
        select.setAttribute('aria-expanded', 'true');
    };

    const choose = (value) => {
        if (!openSelect) return;
        const select = openSelect;
        select.value = value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        syncTrigger(select);
        close();
    };

    const close = () => {
        if (openSelect) {
            openSelect.removeAttribute('aria-expanded');
            if (openSelect._beeUi && openSelect._beeUi.chevron) {
                openSelect._beeUi.chevron.style.transform = '';
            }
        }
        openSelect = null;
        if (flyout) {
            flyout.style.display = 'none';
        }
    };

    const syncTrigger = (select) => {
        const ui = select._beeUi;
        if (!ui) return;
        const label = labelFor(select);
        ui.label.textContent = label;
        ui.label.classList.toggle('bee-empty', !label);
        ui.label.style.color = label ? 'var(--bee-text,#1D2939)' : 'var(--bee-muted,#98A2B3)';
    };

    const toggle = (select, wrapper, force) => {
        const willOpen = typeof force === 'boolean' ? force : !openSelect || openSelect !== select;
        if (willOpen) {
            open(select, wrapper);
        } else {
            close();
        }
    };

    const hideLegacyChevrons = (container) => {
        if (!container) return;
        [...container.querySelectorAll(':scope > svg')].forEach((svg) => {
            const className = svg.getAttribute('class') || '';
            if (className.includes('pointer-events-none') && className.includes('absolute')) {
                svg.style.display = 'none';
            }
        });
    };

    const upgrade = (select) => {
        if (!isUpgradable(select)) return;

        const originalParent = select.parentElement;
        const originalWidth = select.offsetWidth || 120;
        const height = select.offsetHeight || 40;
        const className = select.className || '';
        const isWide = className.includes('w-full');

        // Wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'bee-select-wrap relative';
        wrapper.style.cssText = isWide ? '' : `width:${originalWidth}px;`;
        originalParent.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        select.classList.add('sr-only');
        select.setAttribute('aria-haspopup', 'listbox');
        select.tabIndex = -1;
        select._beeUi = { wrapper };

        // Trigger
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.style.cssText =
            `display:flex;width:100%;align-items:center;justify-content:space-between;gap:8px;` +
            `border-radius:8px;border:1px solid var(--bee-border,#E4E7EC);background:transparent;padding:0 12px;` +
            `font-size:13px;color:var(--bee-text,#1D2939);text-align:left;` +
            `box-shadow:0 1px 2px rgba(16,24,40,.04);transition:border-color .15s,background .15s;`;
        trigger.style.minHeight = `${height}px`;
        wrapper.appendChild(trigger);

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            if (select.disabled) return;
            toggle(select, wrapper);
        });

        document.addEventListener('mousedown', (event) => {
            if (!openSelect) return;
            if (flyout && flyout.contains(event.target)) return;
            const activeWrapper = openSelect._beeUi && openSelect._beeUi.wrapper;
            if (activeWrapper && activeWrapper.contains(event.target)) return;
            close();
        });

        window.addEventListener(
            'scroll',
            () => {
                if (openSelect) close();
            },
            { capture: true, passive: true },
        );
        window.addEventListener('resize', () => close());

        const label = document.createElement('span');
        label.className = 'bee-trigger-label';
        label.style.cssText = 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--bee-muted,#98A2B3);';
        trigger.appendChild(label);

        const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        chevron.setAttribute('width', '15');
        chevron.setAttribute('height', '15');
        chevron.setAttribute('viewBox', '0 0 24 24');
        chevron.setAttribute('fill', 'none');
        chevron.setAttribute('stroke', 'currentColor');
        chevron.setAttribute('stroke-width', '2');
        chevron.setAttribute('stroke-linecap', 'round');
        chevron.setAttribute('stroke-linejoin', 'round');
        chevron.style.cssText = 'flex-shrink:0;color:#98A2B3;transition:transform .15s;';
        chevron.innerHTML = '<polyline points="6 9 12 15 18 9"/>';
        trigger.appendChild(chevron);

        trigger.addEventListener('click', () => {
            chevron.style.transform = openSelect === select ? 'rotate(180deg)' : '';
        });

        hideLegacyChevrons(originalParent);

        const ui = select._beeUi;
        ui.label = label;
        ui.trigger = trigger;
        ui.chevron = chevron;

        if (select.disabled) {
            trigger.disabled = true;
            trigger.style.opacity = '0.5';
            trigger.style.cursor = 'not-allowed';
        }

        syncTrigger(select);
    };

    const refresh = (root = document) => {
        root.querySelectorAll('select').forEach((select) => {
            if (select._beeUi && select._beeUi.wrapper && select._beeUi.wrapper.isConnected) {
                syncTrigger(select);
                return;
            }
            if (!isUpgradable(select)) return;
            upgrade(select);
        });
    };

    return { refresh, close, upgrade };
})();

window.BeeSelectUi = BeeSelectUi;

// Livewire ships and boots its own Alpine instance, so statically importing
// Alpine here would create a second copy — and two running Alpine instances
// break Livewire UI (and charts). Standalone pages (auth, static views)
// without Livewire lazy-load a single Alpine instance instead.
if (!document.querySelector('[wire\\:snapshot], [wire\\:id]')) {
    import('alpinejs').then(({ default: Alpine }) => {
        window.Alpine = Alpine;
        Alpine.start();
    });
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
            BeeSelectUi.close();
            refreshCharts();
            animateNumbers(document);
            refreshCableMaps();
            refreshBeeLocationMaps();
            BeeSelectUi.refresh(document);
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
    BeeSelectUi.refresh(document);
});
