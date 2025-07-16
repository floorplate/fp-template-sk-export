import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export default () => ({
  sections: [],
  activeIndex: 0,
  mobileAnchorOpen: false,
  initAnchors() {
    const elements = [...document.querySelectorAll('[id][data-anchor-title]')];
    this.sections = elements.map((el, idx) => ({
      id: el.id,
      title: el.getAttribute('data-anchor-title')
    }));
    this.sections.forEach((section, idx) => {
      const el = document.getElementById(section.id);
      if (el) {
        ScrollTrigger.create({
          trigger: el,
          start: 'top center',
          onEnter: () => { this.activeIndex = idx; },
          onEnterBack: () => { this.activeIndex = idx; }
        });
      }
    });
  },
  goToSection(id) {
    const el = document.getElementById(id);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
      this.activeIndex = this.sections.findIndex(s => s.id === id);
    }
  },
  goToSectionMobile(id) {
    this.goToSection(id);
    this.mobileAnchorOpen = false;
  }
});
