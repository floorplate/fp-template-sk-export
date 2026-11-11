/**
 * Sends the clicks that matter to Fathom as events, named for what was
 * clicked and where, e.g.
 *
 *   Button: Schedule a Tour (Hero)
 *   Call: (404) 555-0100 (Footer)
 *   Email: leasing@example.com (Contact)
 *   Download: Brochure (Text Columns)
 *   Directions: Get Directions (Map)
 *   Social: Instagram (Footer)
 *   Outbound: Apply Now - rentcafe.com (Hero)
 *   Nav: Floor Plans
 *   Form: Contact Us
 *
 * Fathom records the page each event happened on, so the page isn't in the
 * name; the section is. Plain links inside text aren't events: page views
 * already show where people went.
 *
 * On when Globals → Integrations has a Fathom Site ID, unless "Track clicks as
 * events" is off (the layout puts that on the script tag as data-fp-events).
 * Any element can name its own event with data-track="…", or opt out with
 * data-track="off".
 */

const MAX_LABEL = 60;
const MAX_NAME = 120;

/** Friendly names for the kit's blocks (resources/fieldsets/content_blocks.yaml). */
const BLOCK_LABELS = {
    amenity_carousel: "Amenities",
    anchor_nav: "Section Nav",
    availabilities: "Availabilities",
    contact_sections: "Contact",
    dual_column_text_blocks: "Text Columns",
    dual_image_columns: "Image Columns",
    expander_rows: "Expanders",
    form: "Form",
    full_screen_media: "Full Screen Media",
    intro_text: "Intro",
    large_media_hero: "Hero",
    map: "Map",
    masonry_images: "Gallery",
    stats: "Stats",
    text_block: "Text",
};

const DOWNLOAD = /\.(pdf|docx?|xlsx?|pptx?|csv|zip|dwg|dxf|jpe?g|png|gif|webp|svg|mp4|mov|ics|vcf)$/i;

/** Map hosts that are only ever maps. Google, Bing and goo.gl need a /maps path (isMap). */
const MAPS = [/^maps\.app\.goo\.gl$/i, /^maps\.apple\.com$/i, /(^|\.)waze\.com$/i];

const SOCIAL = {
    "facebook.com": "Facebook",
    "fb.com": "Facebook",
    "instagram.com": "Instagram",
    "linkedin.com": "LinkedIn",
    "x.com": "X",
    "twitter.com": "X",
    "youtube.com": "YouTube",
    "youtu.be": "YouTube",
    "vimeo.com": "Vimeo",
    "tiktok.com": "TikTok",
    "pinterest.com": "Pinterest",
    "threads.net": "Threads",
};

export default function setupFathomEvents() {
    const script = document.querySelector("script[data-site][src*='usefathom.com']");

    if (!script || script.dataset.fpEvents === "false") {
        return;
    }

    const onClick = (event) => {
        // Primary clicks and middle clicks (open in a new tab) only.
        if (event.type === "auxclick" && event.button !== 1) return;

        const target = event.target instanceof Element ? event.target : null;
        const name = target && eventNameFor(target);

        if (name) track(name);
    };

    // Capture phase, so a component that stops propagation can't hide a click.
    document.addEventListener("click", onClick, { capture: true });
    document.addEventListener("auxclick", onClick, { capture: true });

    document.addEventListener("fp:form-submitted", (event) => {
        const form = clean(event.detail?.form || "") || "Form";
        track(`Form: ${form}`);
    });
}

function track(name) {
    try {
        window.fathom?.trackEvent?.(name.slice(0, MAX_NAME));
    } catch {
        // Analytics must never break the page.
    }
}

/** The event for a click, or null when it isn't one worth recording. */
export function eventNameFor(target) {
    const explicit = target.closest("[data-track]");

    if (explicit) {
        const value = clean(explicit.dataset.track);
        return value && value.toLowerCase() !== "off" ? value : null;
    }

    const link = target.closest("a[href]");
    if (!link) return null;

    const [kind, label] = classify(link) ?? [];
    if (!kind) return null;

    const section = sectionOf(link);

    // Nav and Footer links say where they were in their kind already.
    if (kind === section) return `${kind}: ${label}`;

    return section ? `${kind}: ${label} (${section})` : `${kind}: ${label}`;
}

/** @returns {[string, string] | null} [kind, label] */
function classify(link) {
    const href = link.getAttribute("href").trim();
    const text = labelOf(link);

    if (/^tel:/i.test(href)) {
        const number = safeDecode(href.slice(4)).trim();
        // The visible number reads better than the dialled one ("Call us" doesn't).
        return ["Call", (text.replace(/\D/g, "").length >= 7 ? text : number) || "Phone"];
    }

    if (/^mailto:/i.test(href)) {
        return ["Email", safeDecode(href.slice(7).split("?")[0]).trim() || text || "Email"];
    }

    let url;
    try {
        url = new URL(href, window.location.href);
    } catch {
        return null;
    }

    if (!/^https?:$/.test(url.protocol)) return null;

    if (link.hasAttribute("download") || DOWNLOAD.test(url.pathname)) {
        return ["Download", text || fileName(url)];
    }

    const host = url.hostname.replace(/^www\./i, "");

    if (url.origin !== window.location.origin) {
        if (isMap(url, host)) return ["Directions", text || "Map"];

        const network = Object.keys(SOCIAL).find((domain) => host === domain || host.endsWith(`.${domain}`));
        if (network) return ["Social", SOCIAL[network]];

        return ["Outbound", text && text.toLowerCase() !== host.toLowerCase() ? `${text} - ${host}` : host];
    }

    if (isButton(link)) return ["Button", text || pathLabel(url)];

    const section = sectionOf(link);
    if (section === "Nav" || section === "Footer") return [section, text || pathLabel(url)];

    return null;
}

function isButton(link) {
    return Boolean(
        link.closest(".button, button, [role='button']") ||
            link.querySelector(".button-text") ||
            link.matches(".button-text, [class*='btn']")
    );
}

function isMap(url, host) {
    if (/(^|\.)google\.[a-z.]+$/i.test(host)) return url.pathname.startsWith("/maps");
    if (/(^|\.)bing\.com$/i.test(host)) return url.pathname.startsWith("/maps");
    if (/^goo\.gl$/i.test(host)) return url.pathname.startsWith("/maps");

    return MAPS.some((pattern) => pattern.test(host));
}

function sectionOf(element) {
    const marked = element.closest("[data-fp-section]");
    if (marked) return marked.dataset.fpSection;

    const block = element.closest("[data-fp-block]");
    if (!block) return null;

    const handle = block.dataset.fpBlock;
    return BLOCK_LABELS[handle] ?? titleCase(handle);
}

function labelOf(element) {
    const text = element.innerText || element.textContent || "";
    const image = element.querySelector("img[alt]")?.getAttribute("alt") || "";

    return clean(text) || clean(element.getAttribute("aria-label") || "") || clean(image) || clean(element.title || "");
}

function clean(value) {
    const text = String(value ?? "")
        .replace(/[\p{Extended_Pictographic}‍️]/gu, "")
        .replace(/[\u0000-\u001f\u007f]/g, " ")
        .replace(/\s+/g, " ")
        .trim();

    return text.length > MAX_LABEL ? `${text.slice(0, MAX_LABEL - 3).trimEnd()}...` : text;
}

function fileName(url) {
    return clean(safeDecode(url.pathname.split("/").pop() || "")) || "File";
}

function pathLabel(url) {
    return url.pathname === "/" ? "Home" : clean(url.pathname);
}

function titleCase(handle) {
    return String(handle)
        .split(/[_-]+/)
        .filter(Boolean)
        .map((word) => word[0].toUpperCase() + word.slice(1))
        .join(" ");
}

function safeDecode(value) {
    try {
        return decodeURIComponent(value);
    } catch {
        return value;
    }
}
