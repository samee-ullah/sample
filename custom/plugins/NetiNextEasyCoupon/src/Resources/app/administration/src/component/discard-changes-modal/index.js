import template from './template.html.twig';

Shopware.Component.register('neti-easy-coupon-discard-changes-modal', {
    template,

    props: {
        value: {
            required: true,
            type: Boolean
        }
    },

    methods: {
        onConfirm() {
            this.onClose();

            this.$nextTick(() => {
                this.$emit('confirm');
            });
        },
        onClose() {
            this.$emit('input', false);
        }
    }
});