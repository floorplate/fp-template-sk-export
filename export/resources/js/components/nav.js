import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
gsap.registerPlugin(ScrollTrigger); 

export default (currentUrl) => ({
    mobileNavOpen: false,
    darkNavStyle: false,
    isSticky: false,
    homePage: false,
    init() {
        ScrollTrigger.create({
            trigger: window.body, // the element that, when scrolled, will trigger the animation
            start: "top+=10px", // start the animation when the top of videoHome hits 80% from the top of the viewport
            // end: "bottom top", // end the animation when the bottom of videoHome hits the top of the viewport
            markers: false, // Uncomment to show markers for debugging
            toggleActions: "play none none reverse", // Play animation forwards when
            onEnter: () => {
                this.isSticky = true;
                // console.log('sticky');
            },
            onLeaveBack: () => {
                this.isSticky = false;
                // console.log("not sticky");
            },
        });
    },

    toggleMobileNav() {
        this.mobileNavOpen = !this.mobileNavOpen;
    },
});
