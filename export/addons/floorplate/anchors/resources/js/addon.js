import AnchorLinkFieldtype from './components/fieldtypes/AnchorLinkFieldtype.vue';
import SectionAnchorFieldtype from './components/fieldtypes/SectionAnchorFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('anchor_link-fieldtype', AnchorLinkFieldtype);
    Statamic.$components.register('section_anchor-fieldtype', SectionAnchorFieldtype);
});
