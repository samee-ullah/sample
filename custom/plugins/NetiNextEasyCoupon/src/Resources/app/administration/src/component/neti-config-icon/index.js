import template from './neti-config-icon.twig';
import './neti-config-icon.scss';

Shopware.Locale.extend('en-GB', {
    'neti-config-icon': {
        'tooltipText': 'Open plugin configuration'
    }
});

Shopware.Locale.extend('de-DE', {
    'neti-config-icon': {
        'tooltipText': 'Plugin Konfiguration öffnen'
    }
});

Shopware.Component.register('neti-config-icon', {
    template,

    props: {
        pluginName: {
            type: String,
            required: true
        }
    },

    methods: {
        onConfigOpen() {
            const me = this;

            me.$router.push({
                name: 'sw.extension.config',
                params: {
                    namespace: me.pluginName
                }
            });
        },
    }
});