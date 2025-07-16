import Splide from "@splidejs/splide";

export default (imageSide) => ({
    splide: null,
    imageSide: imageSide || "left",
    activeIndex: 0,
    init() {

        console.log("Amenity Carousel initialized with image side:", this.imageSide);
        const isImageLeft = this.imageSide === 'left';

        this.splide = new Splide(this.$refs.amenityCarousel, {
            type: "loop",
            autoWidth: true,
            focus: isImageLeft ? 'right' : 'left',
            direction: isImageLeft ? 'rtl' : 'ltr',
            paginationDirection: isImageLeft ? 'rtl' : 'ltr',
            pagination: false,
            arrows:false
        }).mount({});
        this.splide.on('move', (newIndex) => {
            this.activeIndex = newIndex;
        });
    },
    next() {
        this.splide.go('>');
    },
    previous() {
        this.splide.go('<');
    },
});
