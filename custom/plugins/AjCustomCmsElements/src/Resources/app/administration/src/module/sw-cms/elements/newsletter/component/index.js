import template from './sw-cms-el-newsletter.html.twig';
import './sw-cms-el-newsletter.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-newsletter', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        isNewsletterActive () {
            return this.element.config.isNewsletterActive.value;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('newsletter');
            // this.element.config.isNewsletterActive.value = true;
        }
    }
});
