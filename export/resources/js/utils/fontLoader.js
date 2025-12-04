import WebFont from "webfontloader";

export function loadFonts() {
    if (window.headerFont || window.paragraphFont || window.navFont || window.buttonFont || window.h1FontOverride || window.h2FontOverride || window.h3FontOverride || window.h4FontOverride || window.h5FontOverride || window.h6FontOverride) {
        const googleFamilies = [];
        const loadedFonts = new Set(); // Track loaded fonts to avoid duplicates

        if (window.headerFont) {
            console.log("Loading custom header font:", window.headerFont);
            googleFamilies.push(
                `${window.headerFont}:100,200,300,400,500,600,700,800,900`
            );
            loadedFonts.add(window.headerFont);
        }

        if (window.paragraphFont) {
            console.log("Loading custom paragraph font:", window.paragraphFont);
            googleFamilies.push(
                `${window.paragraphFont}:100,200,300,400,500,600,700,800,900`
            );
            loadedFonts.add(window.paragraphFont);
        }

        if (window.navFont && !loadedFonts.has(window.navFont)) {
            console.log("Loading custom nav font:", window.navFont);
            googleFamilies.push(
                `${window.navFont}:100,200,300,400,500,600,700,800,900`
            );
            loadedFonts.add(window.navFont);
        }

        if (window.buttonFont && !loadedFonts.has(window.buttonFont)) {
            console.log("Loading custom button font:", window.buttonFont);
            googleFamilies.push(
                `${window.buttonFont}:100,200,300,400,500,600,700,800,900`
            );
            loadedFonts.add(window.buttonFont);
        }

        // Load override fonts if they're different from header font
        const overrideFonts = [
            { name: 'h1', font: window.h1FontOverride },
            { name: 'h2', font: window.h2FontOverride },
            { name: 'h3', font: window.h3FontOverride },
            { name: 'h4', font: window.h4FontOverride },
            { name: 'h5', font: window.h5FontOverride },
            { name: 'h6', font: window.h6FontOverride }
        ];

        overrideFonts.forEach(({ name, font }) => {
            if (font && font !== window.headerFont && !loadedFonts.has(font)) {
                console.log(`Loading custom ${name} override font:`, font);
                googleFamilies.push(
                    `${font}:100,200,300,400,500,600,700,800,900`
                );
                loadedFonts.add(font);
            }
        });

        WebFont.load({
            google: {
                families: googleFamilies,
            },
            active: () => {
                console.log("Fonts loaded.");
            },
            inactive: () => {
                console.warn("Font loading failed.");
            },
        });
    }
}
