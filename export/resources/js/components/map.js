var map = null;

export default (building_name, latitude, longitude, points_of_interest) => ({
    categoryVisibility: {},
    async init() {
        const token = import.meta.env.VITE_APPLE_MAP_KEY;
        mapkit.init({
            authorizationCallback: (done) => done(token),
        });
        map = new mapkit.Map("map", {
            showsPointsOfInterest: false,
            isZoomEnabled: false,
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
            "#000"
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
        index = null
    ) {
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
});
