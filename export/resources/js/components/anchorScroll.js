/**
 * Keeps a deep link pointed at its section while the page finishes laying out.
 *
 * Arriving at /amenities#river-club, the browser resolves the fragment long
 * before the page settles: Alpine has not started, so the carousels still have
 * every slide stacked vertically and the document is thousands of pixels taller
 * than it ends up. That early scroll offset is stale the moment Splide collapses
 * its slides and the map sizes itself, which strands the visitor near the bottom
 * of the page instead of at the section they asked for. Chrome shows this
 * plainly; Safari re-resolves the fragment later and gets away with it.
 *
 * Settling is asynchronous and there is no single event that marks the end of
 * it, so rather than guess a delay this realigns on every document height
 * change until the height holds still. It stops early the moment the visitor
 * scrolls, and only ever moves the page when the target is not already where
 * scroll-margin-top says it belongs — so it is a no-op once things are correct.
 */
export default function setupAnchorScroll() {
    if (!window.location.hash || window.location.hash === '#') {
        return;
    }

    const findTarget = () => {
        try {
            return document.querySelector(window.location.hash);
        } catch {
            return null; // Fragments like "#1" are not valid selectors.
        }
    };

    const align = () => {
        const target = findTarget();

        if (!target) {
            return;
        }

        const offset = parseFloat(getComputedStyle(target).scrollMarginTop) || 0;

        if (Math.abs(target.getBoundingClientRect().top - offset) > 4) {
            target.scrollIntoView({ behavior: 'instant', block: 'start' });
        }
    };

    let settleTimer;
    let hardStop;

    const stop = () => {
        observer.disconnect();
        clearTimeout(settleTimer);
        clearTimeout(hardStop);
    };

    const observer = new ResizeObserver(() => {
        align();
        clearTimeout(settleTimer);
        settleTimer = setTimeout(stop, 400);
    });

    // Never fight the visitor: any deliberate input hands scrolling back to them.
    ['wheel', 'touchstart', 'keydown'].forEach((event) => {
        window.addEventListener(event, stop, { once: true, passive: true });
    });

    hardStop = setTimeout(stop, 5000);

    observer.observe(document.documentElement);

    align();
}
