import { Map, TileLayer, Marker, DivIcon, FeatureGroup, LatLng } from 'leaflet';
import 'leaflet/dist/leaflet.css';

var map = null;
var markers = {};

function escapeHtmlAttr(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

/** Statamic link field JSON: `{ url }` or a plain URL string. */
function resolvePoiLinkUrl(point) {
    const link = point?.view_more_link;
    if (!link) {
        return null;
    }
    if (typeof link === 'string' && link.trim() !== '') {
        return link.trim();
    }
    if (typeof link === 'object' && link.url != null && String(link.url).trim() !== '') {
        return String(link.url).trim();
    }

    return null;
}

function safePoiHref(url) {
    const href = String(url ?? '').trim();
    if (!href || /^javascript:/i.test(href)) {
        return null;
    }

    return href;
}

function externalLinkAttrs(url) {
    try {
        const u = new URL(url, window.location.origin);
        if (u.origin !== window.location.origin) {
            return ' target="_blank" rel="noopener noreferrer"';
        }
    } catch {
        //
    }

    return '';
}

/** Ensures property pin stacks above POIs (Leaflet uses latLng y-position + this offset for marker z-index). */
const BUILDING_MARKER_Z_INDEX_OFFSET = 100_000;

/** POI pins stay fixed at 32px; building pin uses a larger base and grows with zoom so it stays prominent vs map detail. */
function getBuildingPixelSizeForZoom(zoom) {
    const refZoom = 13;
    const baseSize = 52;
    const perLevel = 3;
    const scaled = baseSize + Math.max(0, zoom - refZoom) * perLevel;

    return Math.round(Math.min(72, Math.max(48, scaled)));
}

const JAWG_ACCESS_TOKEN = 'RKO1r4kc8hB99YzyQSF1gmZd79CkMWcLcWpTr3gnotXAzjHwlbKkmsuVJJIB6gnT';

const JAWG_ATTRIBUTION = '<a href="https://jawg.io" title="Tiles Courtesy of Jawg Maps" target="_blank">&copy; <b>Jawg</b>Maps</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

/**
 * Optional starting zoom from the CMS. Blank (the default) means the map frames
 * every visible pin instead. Arrives as a string because the field is optional
 * and an empty Antlers tag would otherwise break the x-data expression.
 */
function parseDefaultZoom(value) {
    const zoom = parseInt(value, 10);

    return Number.isFinite(zoom) && zoom >= 1 && zoom <= 20 ? zoom : null;
}

export default (building_name, latitude, longitude, optional_map_icon, building_pin_color, points_of_interest, map_style, default_zoom) => ({
    categoryVisibility: {},
    buildingMarker: null,
    buildingPinOpts: null,
    async init() {
        const startingZoom = parseDefaultZoom(default_zoom);

        map = new Map('map', {
            center: new LatLng(latitude, longitude),
            zoom: startingZoom ?? 13,
            zoomControl: false,
            scrollWheelZoom: false,
            dragging: true,
            touchZoom: true,
            doubleClickZoom: true,
            boxZoom: true,
            keyboard: true,
            tap: true
        });

        // LIGHT STYLES
        const cartoLight = new TileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap © CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        });

        const cartoPositron = new TileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap © CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        });

        // LIGHT BLUE STYLES
        const cartoVoyager = new TileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap © CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        });

        const cartoVoyagerNoLabels = new TileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap © CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        });

        // ADDITIONAL COLOR VARIATIONS
        const esriWorldTopo = new TileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© Esri',
            maxZoom: 19
        });

        const esriWorldImagery = new TileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© Esri',
            maxZoom: 19
        });

        // DARK STYLES
        const cartoDarkMatter = new TileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap © CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        });

        const cartoDarkNoLabels = new TileLayer('https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap © CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        });

        // JAWG STYLES
        // Every Jawg style shares one token and attribution, so build them from
        // the style slug rather than repeating the config per layer.
        const jawgLayer = (style) => new TileLayer(
            `https://tile.jawg.io/${style}/{z}/{x}/{y}{r}.png?access-token={accessToken}`,
            {
                attribution: JAWG_ATTRIBUTION,
                minZoom: 0,
                maxZoom: 22,
                accessToken: JAWG_ACCESS_TOKEN,
            }
        );

        const jawgStreets = jawgLayer('jawg-streets');
        const jawgSunny = jawgLayer('jawg-sunny');

        // Map style selection based on CMS choice
        const styleMap = {
            'light': cartoLight,
            'light_no_labels': cartoPositron,
            'topographic': cartoVoyager,
            'topographic_no_labels': cartoVoyagerNoLabels,
            'warm_topographic': esriWorldTopo,
            'satellite': esriWorldImagery,
            'dark': cartoDarkMatter,
            'dark_no_labels': cartoDarkNoLabels,
            'jawg_streets': jawgStreets,
            'jawg_sunny': jawgSunny
        };

        // Add selected map style or default to Warm Topographic
        const selectedStyle = styleMap[map_style] || esriWorldTopo;
        selectedStyle.addTo(map);

        // Add the building marker
        const buildingMarker = this.createBuildingMarker(
            new LatLng(latitude, longitude),
            building_name,
            optional_map_icon,
            building_pin_color
        );
        buildingMarker.addTo(map);
        markers['building'] = buildingMarker;
        this.buildingMarker = buildingMarker;
        this.buildingPinOpts = {
            title: building_name,
            customIcon: optional_map_icon,
            pinColor: building_pin_color,
        };

        map.on('zoomend', () => {
            this.refreshBuildingMarkerIcon();
        });

        if (points_of_interest) {
            const isDesktop = window.matchMedia('(min-width: 1024px)').matches;
            points_of_interest.forEach((category) => {
                const categoryName = category.category_name;
                const startsOnRaw = isDesktop ? category.desktop_toggle_starts_on : category.mobile_toggle_starts_on;
                const startsOn = startsOnRaw === true || startsOnRaw === 'true' || startsOnRaw === 1 || startsOnRaw === '1';
                this.categoryVisibility[categoryName] = !!startsOn;
                const pinColor = category.pin_color;
                const poiList = category.locations;

                if (!markers[categoryName]) {
                    markers[categoryName] = [];
                }

                poiList.forEach((point, idx) => {
                    const subtitleParts = [];
                    if (point.address) subtitleParts.push(point.address);
                    if (point.description) subtitleParts.push(point.description);
                    const subtitle = subtitleParts.join(" • ");

                    const poiMarker = this.createPOIMarker(
                        new LatLng(point.latitude, point.longitude),
                        point.name,
                        pinColor,
                        subtitle,
                        idx + 1,
                        resolvePoiLinkUrl(point)
                    );

                    poiMarker.category = categoryName;
                    
                    if (startsOn) {
                        poiMarker.addTo(map);
                    }

                    markers[categoryName].push(poiMarker);
                });
            });
        }

        // An explicit zoom means the editor picked the framing themselves; the
        // recenter control still frames every pin on demand.
        if (startingZoom === null) {
            this.frameAllAnnotations();
        }

        this.refreshBuildingMarkerIcon();
    },

    refreshBuildingMarkerIcon() {
        if (!map || !this.buildingMarker || !this.buildingPinOpts) {
            return;
        }

        const { title, customIcon, pinColor } = this.buildingPinOpts;
        const size = getBuildingPixelSizeForZoom(map.getZoom());
        const half = Math.round(size / 2);
        const iconHtml = this.getBuildingIconHtml(customIcon, pinColor, size, title);

        const icon = new DivIcon({
            className: 'custom-building-marker',
            html: iconHtml,
            iconSize: [size, size],
            iconAnchor: [half, half],
            popupAnchor: [0, -half],
        });

        this.buildingMarker.setIcon(icon);
    },

    toggleCategory(category_name) {
        if (!map || !markers[category_name]) {
            return;
        }

        this.categoryVisibility[category_name] = !this.categoryVisibility[category_name];

        markers[category_name].forEach((marker) => {
            if (this.categoryVisibility[category_name]) {
                marker.addTo(map);
            } else {
                map.removeLayer(marker);
            }
        });
    },

    createBuildingMarker(latLng, title, customIcon, pinColor) {
        const size = map ? getBuildingPixelSizeForZoom(map.getZoom()) : 52;
        const half = Math.round(size / 2);
        const iconHtml = this.getBuildingIconHtml(customIcon, pinColor, size, title);

        const icon = new DivIcon({
            className: 'custom-building-marker',
            html: iconHtml,
            iconSize: [size, size],
            iconAnchor: [half, half],
            popupAnchor: [0, -half],
        });

        const marker = new Marker(latLng, {
            icon,
            zIndexOffset: BUILDING_MARKER_Z_INDEX_OFFSET,
        });

        const popupContent = `
            <div class="bg-white px-fm-xs py-fm-2xs rounded shadow" style="max-width: 300px; min-width: 250px;">
                <p class="title" style="font-weight: bold; margin-bottom: 4px;">${title}</p>
            </div>
        `;

        marker.bindPopup(popupContent);

        return marker;
    },

    getBuildingIconHtml(customIcon, pinColor = '#000000', sizePx = 52, titleForAlt = 'Property') {
        const logoPx = Math.round(sizePx * 0.52);
        const alt = escapeHtmlAttr(titleForAlt || 'Property');

        if (customIcon && customIcon !== '' && customIcon !== 'undefined') {
            return `
                <div style="width: ${sizePx}px; height: ${sizePx}px; border-radius: 50%; background-color: ${pinColor}; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    <img src="${customIcon}" alt="${alt}" style="width: ${logoPx}px; height: ${logoPx}px; object-fit: contain;" />
                </div>
            `;
        }

        return `
            <div style="width: ${sizePx}px; height: ${sizePx}px; border-radius: 50%; background-color: ${pinColor}; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                <svg viewBox="0 0 24 24" fill="white" style="width: 60%; height: 60%;">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            </div>
        `;
    },

    createPOIMarker(latLng, title, color, subtitle, index, viewMoreUrl) {
        const iconHtml = `
            <div style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: ${color}; color: white; border-radius: 50%; font-weight: bold; font-size: 14px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                ${index}
            </div>
        `;

        const icon = new DivIcon({
            className: 'custom-poi-marker',
            html: iconHtml,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        });

        const marker = new Marker(latLng, { icon });

        const subtitleParts = subtitle.split(' • ');
        const address = subtitleParts[0] || '';
        const description = subtitleParts[1] || '';

        let popupContent = `
            <div class="poi-popup bg-white flex flex-col gap-fm-xs px-fm-xs py-fm-xs rounded shadow" style="max-width: 300px; min-width: 250px;">
                <p class="title m-0 font-bold max-w-[90%]">${title}</p>
        `;

        if (address) {
            popupContent += `<p class="address small text-gray-600 m-0 max-w-[90%]">${address}</p>`;
        }

        if (description) {
            popupContent += `<p class="description m-0">${description}</p>`;
        }

        const href = safePoiHref(viewMoreUrl);
        if (href) {
            const attrs = externalLinkAttrs(href);
            popupContent += `
                <p class="poi-view-more m-0">
                    <a href="${escapeHtmlAttr(href)}" class="underline font-semibold text-dark-text-color"${attrs}>View More</a>
                </p>
            `;
        }

        popupContent += `</div>`;

        marker.bindPopup(popupContent);

        return marker;
    },

    frameAllAnnotations() {
        if (!map) return;

        const allMarkers = [markers['building']];
        
        Object.keys(markers).forEach((key) => {
            if (key !== 'building' && this.categoryVisibility[key]) {
                allMarkers.push(...markers[key]);
            }
        });

        const validMarkers = allMarkers.filter(m => m && map.hasLayer(m));
        
        if (validMarkers.length === 0) return;

        const group = new FeatureGroup(validMarkers);
        map.fitBounds(group.getBounds().pad(0.2));
    },

    recenterMap() {
        if (!map) return;
        this.frameAllAnnotations();
    },

    zoomIn() {
        if (!map) return;
        map.zoomIn();
    },

    zoomOut() {
        if (!map) return;
        map.zoomOut();
    },
});
