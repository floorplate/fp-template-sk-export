var map = null;

export default (building_name, latitude, longitude, optional_map_icon, points_of_interest) => ({
    categoryVisibility: {},
    async init() {
        const token = import.meta.env.VITE_APPLE_MAP_KEY;
        mapkit.init({
            authorizationCallback: (done) => done(token),
        });
        map = new mapkit.Map("map", {
            showsPointsOfInterest: false,
            isZoomEnabled: true,
            showsZoomControl: false,
            // isScrollEnabled: false,
            isRotationEnabled: false,
            isPitchEnabled: false,
        });
        map.center = this.createCoordinate(latitude, longitude);
        map.region = new mapkit.CoordinateRegion(
            map.center,
            new mapkit.CoordinateSpan(0.02, 0.02)
        );

        // Add a pin (marker) at the coordinate for the building
        const coordinate = this.createCoordinate(latitude, longitude);
        const building = this.createAnnotation(
            coordinate,
            building_name,
            "#000",
            "",
            null,
            optional_map_icon
        );
        map.addAnnotation(building);

        if (points_of_interest) {
            const isDesktop = window.matchMedia('(min-width: 1024px)').matches; // Tailwind lg breakpoint by default
            points_of_interest.forEach((category) => {
                const categoryName = category.category_name;
                // Choose desktop or mobile starting toggle based on current viewport
                const startsOnRaw = isDesktop ? category.desktop_toggle_starts_on : category.mobile_toggle_starts_on;
                const startsOn = startsOnRaw === true || startsOnRaw === 'true' || startsOnRaw === 1 || startsOnRaw === '1';
                this.categoryVisibility[categoryName] = !!startsOn;
                const pinColor = category.pin_color;
                const poiList = category.locations;
                poiList.forEach((point, idx) => {
                    const poiCoordinate = this.createCoordinate(
                        point.latitude,
                        point.longitude
                    );
                    const subtitleParts = [];
                    if (point.address) subtitleParts.push(point.address);
                    if (point.description) subtitleParts.push(point.description);
                    const subtitle = subtitleParts.join(" • ");

                    const poiAnnotation = this.createAnnotation(
                        poiCoordinate,
                        point.name,
                        pinColor,
                        subtitle,
                        idx + 1 // index starting from 1
                    );
                    // Initialize visibility to match the category's starting state
                    poiAnnotation.enabled = !!startsOn;
                    poiAnnotation.visible = !!startsOn;
                    poiAnnotation.category = categoryName;
                    map.addAnnotation(poiAnnotation);
                });
            });
        }

        this.frameAllAnnotations();
    },

    createCoordinate(lat, lon) {
        return new mapkit.Coordinate(lat, lon);
    },

    toggleCategory(category_name) {
        // Check if the map is initialized and has annotations
        if (!map || !map.annotations) {
            return;
        }

        this.categoryVisibility[category_name] =
            !this.categoryVisibility[category_name];

        // Loop through all annotations and toggle visibility based on category
        map.annotations.forEach((annotation) => {
            if (annotation.category === category_name) {
                // Toggle custom enabled flag
                annotation.enabled = !annotation.enabled;

                // Use visibility instead of removing/adding
                annotation.visible = annotation.enabled;
            }
        });
    },
    createAnnotation(
        coordinate,
        title,
        color = "#000",
        subtitle = "",
        index = null,
        customIcon = null
    ) {
        // For building markers, always use the larger custom annotation
        if (index === null) {
            const annotation = new mapkit.Annotation(coordinate, (coordinate, options) => {
                const div = document.createElement("div");
                div.style.width = "40px";
                div.style.height = "40px";
                div.style.borderRadius = "50%";
                div.style.backgroundColor = color;
                div.style.display = "flex";
                div.style.alignItems = "center";
                div.style.justifyContent = "center";
                div.style.overflow = "hidden";
                div.style.border = "2px solid white";
                div.style.boxShadow = "0 2px 4px rgba(0,0,0,0.3)";
                
                // If we have a custom icon, display it
                if (customIcon && customIcon !== '' && customIcon !== 'undefined') {
                    const img = document.createElement("img");
                    img.src = customIcon;
                    img.style.maxWidth = "60%";
                    img.style.maxHeight = "60%";
                    img.style.objectFit = "contain";
                    img.style.width = "auto";
                    img.style.height = "auto";
                    div.appendChild(img);
                } else {
                    // Default pin icon as SVG
                    const svgNS = "http://www.w3.org/2000/svg";
                    const svg = document.createElementNS(svgNS, "svg");
                    svg.setAttribute("viewBox", "0 0 24 24");
                    svg.setAttribute("fill", "white");
                    svg.style.width = "60%";
                    svg.style.height = "60%";
                    
                    const path = document.createElementNS(svgNS, "path");
                    path.setAttribute("d", "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z");
                    
                    svg.appendChild(path);
                    div.appendChild(svg);
                }
                
                return div;
            }, {
                title,
                subtitle,
                enabled: true,
                draggable: false,
                callout: {
                    enabled: true,
                    calloutElementForAnnotation: (annotation) => {
                        const container = document.createElement("div");
                        container.classList.add("bg-white", "px-fm-xs", "py-fm-2xs", "rounded", "shadow");
                        container.style.maxWidth = "300px";

                        const titleEl = document.createElement("p");
                        titleEl.style.fontWeight = "bold";
                        titleEl.style.marginBottom = "4px";
                        titleEl.textContent = annotation.title;

                        // Split address and description into separate divs
                        const addressEl = document.createElement("p");
                        addressEl.classList.add(
                            "small",
                            "text-gray-600",
                            "mb-1"
                        );
                        addressEl.style.whiteSpace = "normal";
                        addressEl.style.lineHeight = "1.2";
                        addressEl.textContent = annotation.subtitle?.split(" • ")[0] ?? "";

                        const descriptionEl = document.createElement("p");
                        descriptionEl.style.whiteSpace = "normal";
                        descriptionEl.style.lineHeight = "1.2";
                        descriptionEl.textContent = annotation.subtitle?.split(" • ")[1] ?? "";

                        container.appendChild(titleEl);
                        if (addressEl.textContent) container.appendChild(addressEl);
                        if (descriptionEl.textContent) container.appendChild(descriptionEl);

                        return container;
                    }
                }
            });
            return annotation;
        }
        
        // Default marker annotation for numbered POIs
        return new mapkit.MarkerAnnotation(coordinate, {
            title,
            subtitle,
            color,
            enabled: true,
            draggable: false,
            glyphText: index !== null ? `${index}` : undefined,
            callout: {
                enabled: true,
                calloutElementForAnnotation: (annotation) => {
                    const container = document.createElement("div");
                    container.classList.add("bg-white", "px-fm-xs", "py-fm-2xs", "rounded", "shadow");
                    container.style.maxWidth = "300px";

                    const titleEl = document.createElement("p");
                    titleEl.style.fontWeight = "bold";
                    titleEl.style.marginBottom = "4px";
                    titleEl.textContent = annotation.title;

                    // Split address and description into separate divs
                    const addressEl = document.createElement("p");
                    addressEl.classList.add(
                        "small",
                        "text-gray-600",
                        "mb-1"
                    );
                    addressEl.style.whiteSpace = "normal";
                    addressEl.style.lineHeight = "1.2";
                    addressEl.textContent = annotation.subtitle?.split(" • ")[0] ?? "";

                    const descriptionEl = document.createElement("p");
                    descriptionEl.style.whiteSpace = "normal";
                    descriptionEl.style.lineHeight = "1.2";
                    descriptionEl.textContent = annotation.subtitle?.split(" • ")[1] ?? "";

                    container.appendChild(titleEl);
                    if (addressEl.textContent) container.appendChild(addressEl);
                    if (descriptionEl.textContent) container.appendChild(descriptionEl);

                    return container;
                }
            }
        });
    },

    frameAllAnnotations() {
        if (!map || !map.annotations || map.annotations.length === 0) return;

        const coordinates = map.annotations
            .filter((a) => a.visible !== false) // filter only visible ones
            .map((a) => a.coordinate);

        const latitudes = coordinates.map((c) => c.latitude);
        const longitudes = coordinates.map((c) => c.longitude);

        const minLat = Math.min(...latitudes);
        const maxLat = Math.max(...latitudes);
        const minLon = Math.min(...longitudes);
        const maxLon = Math.max(...longitudes);

        const center = new mapkit.Coordinate(
            (minLat + maxLat) / 2,
            (minLon + maxLon) / 2
        );
        const span = new mapkit.CoordinateSpan(
            (maxLat - minLat) * 1.2, // Add some padding
            (maxLon - minLon) * 1.2
        );

        map.region = new mapkit.CoordinateRegion(center, span);
    },

    zoomIn() {
        if (!map) return;
        const currentRegion = map.region;
        const newSpan = new mapkit.CoordinateSpan(
            currentRegion.span.latitudeDelta * 0.5,
            currentRegion.span.longitudeDelta * 0.5
        );
        map.setRegionAnimated(
            new mapkit.CoordinateRegion(currentRegion.center, newSpan)
        );
    },

    zoomOut() {
        if (!map) return;
        const currentRegion = map.region;
        const newSpan = new mapkit.CoordinateSpan(
            currentRegion.span.latitudeDelta * 2,
            currentRegion.span.longitudeDelta * 2
        );
        map.setRegionAnimated(
            new mapkit.CoordinateRegion(currentRegion.center, newSpan)
        );
    },
});
