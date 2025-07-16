export default function setupViewTransition() {
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("a").forEach((anchor) => {
            anchor.addEventListener("click", (event) => {
                const link = event.currentTarget;

                // Return early if the link points to a different origin or has target="_blank"
                if (
                    link.origin !== window.location.origin ||
                    link.target === "_blank"
                ) {
                    console.log('return early');
                    return;
                } else{
                    console.log('special')
                }

                event.preventDefault();

                document.documentElement.style.setProperty(
                    "--view-transition",
                    "fade"
                );

                window.location.href = link.href;
            });
        });
    });
}
