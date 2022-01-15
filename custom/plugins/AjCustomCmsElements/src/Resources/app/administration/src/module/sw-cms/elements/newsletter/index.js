import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'newsletter',
    label: 'sw-cms.elements.newsletterElement.label',
    component: 'sw-cms-el-newsletter',
    configComponent: 'sw-cms-el-config-newsletter',
    previewComponent: 'sw-cms-el-preview-newsletter',
    defaultConfig: {
        isNewsletterActive: {
            source: 'static',
            value: false
        }
    }
});
