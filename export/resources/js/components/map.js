import { Map, TileLayer, Marker, DivIcon, FeatureGroup, LatLng } from 'leaflet';
import 'leaflet/dist/leaflet.css';

var map = null;
var markers = {};

export default (building_name, latitude, longitude, optional_map_icon, building_pin_color, points_of_interest, map_style) => ({
    categoryVisibility: {},
    async init() {
        map = new Map('map', {
            center: new LatLng(latitude, longitude),
            zoom: 13,
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

        // Map style selection based on CMS choice
        const styleMap = {
            'light': cartoLight,
            'light_no_labels': cartoPositron,
            'topographic': cartoVoyager,
            'topographic_no_labels': cartoVoyagerNoLabels,
            'warm_topographic': esriWorldTopo,
            'satellite': esriWorldImagery,
            'dark': cartoDarkMatter,
            'dark_no_labels': cartoDarkNoLabels
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
                        idx + 1
                    );

                    poiMarker.category = categoryName;
                    
                    if (startsOn) {
                        poiMarker.addTo(map);
                    }

                    markers[categoryName].push(poiMarker);
                });
            });
        }

        this.frameAllAnnotations();
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
        const iconHtml = this.getBuildingIconHtml(customIcon, pinColor);
        
        const icon = new DivIcon({
            className: 'custom-building-marker',
            html: iconHtml,
            iconSize: [40, 40],
            iconAnchor: [20, 20],
            popupAnchor: [0, -20]
        });

        const marker = new Marker(latLng, { icon });
        
        const popupContent = `
            <div class="bg-white px-fm-xs py-fm-2xs rounded shadow" style="max-width: 300px; min-width: 250px;">
                <p class="title" style="font-weight: bold; margin-bottom: 4px;">${title}</p>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        
        return marker;
    },

    getBuildingIconHtml(customIcon, pinColor = '#000000') {
        if (customIcon && customIcon !== '' && customIcon !== 'undefined') {
            return `
                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: ${pinColor}; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    <img src="${customIcon}" style="width: 24px; height: 24px; object-fit: contain;" />
                </div>
            `;
        } else {
            return `
                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: ${pinColor}; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    <svg viewBox="0 0 24 24" fill="white" style="width: 60%; height: 60%;">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </div>
            `;
        }
    },

    createPOIMarker(latLng, title, color, subtitle, index) {
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
            <div class="bg-white px-fm-xs py-fm-xs rounded shadow" style="max-width: 300px; min-width: 250px;">
                <p style="font-weight: bold; max-width: 90%; margin-bottom: 12px;" class="title">${title}</p>
        `;

        if (address) {
            popupContent += `<p class="address small text-gray-600" style="max-width: 90%;">${address}</p>`;
        }

        if (description) {
            popupContent += `<p class="description">${description}</p>`;
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
