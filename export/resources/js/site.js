import Alpine from "alpinejs";
import setupViewTransition from "./components/viewTransition";
import { loadFonts } from "./utils/fontLoader";

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

// Load custom fonts
loadFonts();