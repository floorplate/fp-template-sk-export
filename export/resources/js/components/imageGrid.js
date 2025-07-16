export default () => ({
    activeImage: null,
    activeText: null,
    activeImageIndex: 0,
    itemTotal: null,
    showLightbox: false,
    init() {
        this.itemTotal = this.$refs.gridContainer.children.length -1;

        document.addEventListener("keydown", (event) => {
            switch (
                event.code // You can also use event.key or event.keyCode
            ) {
                case "ArrowRight":

                    if (this.showLightbox) {
                       this.getNext();
                    }
                    break;
                case "ArrowLeft":
                    if (this.showLightbox) {
                        this.getPrevious();
                    }
                    break;
                case "Escape":
                    if (this.showLightbox) {
                        this.showLightbox = false;
                    }
                   
                    break;
            }
        });

    },
    viewInLightbox(index) {
        this.activeImageIndex = index;
        var imageContainer = this.$refs.gridContainer.children[index];
        this.activeImage = imageContainer.querySelector("img").src;
        this.activeText = imageContainer.querySelector("img").alt;
        this.showLightbox = true;
    },

    getNext() {
        if (this.activeImageIndex+1 < this.itemTotal) {
            this.viewInLightbox(this.activeImageIndex + 1);
        } else {
            this.viewInLightbox(0);
        }
    },
    getPrevious() {
        if (this.activeImageIndex - 1 > (-1)) {
            this.viewInLightbox(this.activeImageIndex - 1);
        } else {
            this.viewInLightbox(this.itemTotal);
        }
    }
});
