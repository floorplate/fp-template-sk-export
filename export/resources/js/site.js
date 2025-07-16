import Alpine from "alpinejs";
import setupViewTransition from "./components/viewTransition";
import WebFont from "webfontloader";

// import.meta.glob(["../images/**", "../fonts/**"]);
setupViewTransition(); 

import nav from "./components/nav";
import imageGrid from "./components/imageGrid";
import amenityCarousel from "./components/amenityCarousel";
import anchorNav from "./components/anchorNav";
import map from "./components/map";
Alpine.data("nav", nav);
Alpine.data("imageGrid", imageGrid);
Alpine.data("amenityCarousel", amenityCarousel);
Alpine.data("anchorNav", anchorNav);
Alpine.data("map", map);

window.Alpine = Alpine;
Alpine.start();

if (window.headerFont || window.paragraphFont) {
    const googleFamilies = [];

    if (window.headerFont) {
        console.log("Loading custom header font:", window.headerFont);
        googleFamilies.push(
            `${window.headerFont}:100,200,300,400,500,600,700,800,900`
        );
    }

    if (window.paragraphFont) {
        console.log("Loading custom paragraph font:", window.paragraphFont);
        googleFamilies.push(
            `${window.paragraphFont}:100,200,300,400,500,600,700,800,900`
        );
    }

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