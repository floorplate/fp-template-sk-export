import axios from "axios";
import FontPicker from "./components/fieldtypes/FontPicker.vue";

Statamic.booting(() => {
    Statamic.$components.register("font_picker-fieldtype", FontPicker);
    axios
        .get("/api/globals/site_theme")
        .then((response) => {
            const variables = response.data.data;

            const cssVars = Object.entries(variables)
                .map(([key, value]) => `--${key.replace(/_/g, "-")}: ${value};`)
                .join(" ");

            const styleTag = document.createElement("style");
            styleTag.textContent = `:root { ${cssVars} }`;
            document.head.appendChild(styleTag);

            // console.log("Injected CSS vars into CP head:", cssVars);
        })
        .catch((error) => {
            // console.error("Failed to load site_theme globals:", error);
        });
});