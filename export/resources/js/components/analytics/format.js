/**
 * Number, date and label helpers for the analytics widget. Everything is
 * formatted in the viewer's locale; dates are Fathom's bucket keys, which are
 * already in the site's Fathom timezone, so they're parsed as local wall time
 * and never shifted.
 */

const compactFormatter = new Intl.NumberFormat(undefined, { notation: "compact", maximumFractionDigits: 1 });
const fullFormatter = new Intl.NumberFormat();

export const METRICS = {
    visitors: {
        label: "Visitors",
        help: "People who visited, each counted once per period.",
        format: (value) => formatCount(value),
        better: "up",
    },
    pageviews: {
        label: "Page views",
        help: "Every page loaded, including repeat views by the same person.",
        format: (value) => formatCount(value),
        better: "up",
    },
    avgDuration: {
        label: "Avg time on site",
        help: "How long a visit lasts on average, from the first page to the last.",
        format: (value) => formatDuration(value),
        better: "up",
    },
    bounceRate: {
        label: "Bounce rate",
        help: "Visits that saw one page and left. Lower usually means people are exploring.",
        format: (value) => `${Math.round(value)}%`,
        better: "down",
    },
};

export function formatCount(value) {
    const number = Number(value) || 0;

    return Math.abs(number) >= 10000 ? compactFormatter.format(number) : fullFormatter.format(Math.round(number));
}

export function formatFull(value) {
    return fullFormatter.format(Math.round(Number(value) || 0));
}

export function formatDuration(seconds) {
    const total = Math.round(Number(seconds) || 0);
    const minutes = Math.floor(total / 60);
    const rest = total % 60;

    if (minutes >= 60) return `${Math.floor(minutes / 60)}h ${minutes % 60}m`;

    return minutes ? `${minutes}m ${String(rest).padStart(2, "0")}s` : `${rest}s`;
}

/**
 * Change against the previous period, as { text, direction, good }, or null
 * when there's nothing meaningful to compare with.
 */
export function delta(metric, current, previous) {
    const now = Number(current) || 0;
    const before = Number(previous) || 0;

    if (!before && !now) return null;
    if (!before) return { text: "New", direction: "up", good: METRICS[metric].better === "up", title: "Nothing in the previous period" };

    // Rates are compared in points, everything else as a percentage.
    const change = metric === "bounceRate" ? now - before : ((now - before) / before) * 100;
    const rounded = Math.round(change);

    if (rounded === 0) return { text: "0%", direction: "flat", good: null };

    const direction = rounded > 0 ? "up" : "down";
    const unit = metric === "bounceRate" ? " pts" : "%";

    return {
        text: `${Math.abs(rounded) > 999 ? "999+" : Math.abs(rounded)}${unit}`,
        direction,
        good: direction === METRICS[metric].better,
    };
}

/** "2026-11-10" | "2026-11-10 14:00:00" | "2026-11" → Date in local wall time. */
export function parseBucket(key) {
    const [date, time = "00:00:00"] = String(key).split(" ");
    const [year, month, day = "01"] = date.split("-");
    const [hour] = time.split(":");

    return new Date(Number(year), Number(month) - 1, Number(day), Number(hour));
}

export function bucketLabel(key, grouping, { long = false } = {}) {
    const date = parseBucket(key);

    if (grouping === "hour") {
        return date.toLocaleTimeString(undefined, { hour: "numeric" }) + (long ? `, ${date.toLocaleDateString(undefined, { weekday: "short", month: "short", day: "numeric" })}` : "");
    }

    if (grouping === "month") {
        return date.toLocaleDateString(undefined, { month: long ? "long" : "short", year: long ? "numeric" : undefined });
    }

    return date.toLocaleDateString(undefined, long ? { weekday: "short", month: "short", day: "numeric", year: "numeric" } : { month: "short", day: "numeric" });
}

let regionNames;

export function countryName(code) {
    if (!code || code.length !== 2) return code || "Unknown";

    try {
        regionNames ??= new Intl.DisplayNames(undefined, { type: "region" });
        return regionNames.of(code.toUpperCase()) || code;
    } catch {
        return code;
    }
}

export function countryFlag(code) {
    if (!code || !/^[A-Za-z]{2}$/.test(code)) return "🌐";

    return String.fromCodePoint(...[...code.toUpperCase()].map((letter) => 0x1f1a5 + letter.charCodeAt(0)));
}

/** Splits a tracked event name ("Button: Schedule a Tour (Hero)") into its parts. */
export function parseEvent(name) {
    const match = /^([A-Za-z]+):\s*(.+?)(?:\s+\(([^()]+)\))?$/.exec(name);

    if (!match) return { kind: "Event", label: name, section: null };

    return { kind: match[1], label: match[2], section: match[3] ?? null };
}

export const EVENT_KINDS = {
    Button: { icon: "cursor-click", label: "Button" },
    Call: { icon: "phone-telephone-call", label: "Phone call" },
    Email: { icon: "mail", label: "Email" },
    Download: { icon: "download", label: "Download" },
    Directions: { icon: "travel-map", label: "Directions" },
    Social: { icon: "share-link", label: "Social" },
    Outbound: { icon: "external-link", label: "Outbound link" },
    Nav: { icon: "link", label: "Navigation" },
    Footer: { icon: "link", label: "Footer link" },
    Form: { icon: "forms", label: "Form sent" },
    Event: { icon: "target-aim", label: "Event" },
};

export function timeAgo(iso) {
    if (!iso) return "";

    const seconds = Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 1000));

    if (seconds < 60) return "just now";
    if (seconds < 3600) return `${Math.round(seconds / 60)} min ago`;
    if (seconds < 86400) return `${Math.round(seconds / 3600)} h ago`;

    return `${Math.round(seconds / 86400)} d ago`;
}

/** Clean y-axis ticks from zero: 3–5 steps of 1, 2, 2.5 or 5 × 10ⁿ. */
export function niceTicks(max, count = 4) {
    if (!(max > 0)) return [0, 1];

    const rough = max / count;
    const power = 10 ** Math.floor(Math.log10(rough));
    const step = [1, 2, 2.5, 5, 10].map((m) => m * power).find((s) => s >= rough) ?? 10 * power;
    const ticks = [];

    for (let value = 0; value <= max + step * 0.001; value += step) ticks.push(value);
    if (ticks.at(-1) < max) ticks.push(ticks.at(-1) + step);

    return ticks;
}
