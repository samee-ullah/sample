import template from './sas-blog-detail-override.html.twig';

import slugify from '@slugify';

const Criteria = Shopware.Data.Criteria;
const { Component} = Shopware;

Component.override('sas-blog-detail', {
    template,

    inject: ['repositoryFactory', 'systemConfigApiService', 'customFieldDataProviderService'],

    data() {
        return {
            blog: null,
            maximumMetaTitleCharacter: 160,
            maximumMetaDescriptionCharacter: 160,
            configOptions: {},
            isLoading: true,
            processSuccess: false,
            fileAccept: 'image/*',
            moduleData: this.$route.meta.$module,
            isProVersion: true,
            customFieldSets: null,
        };
    },

    watch: {
        'blog.slug': function (value) {
            if (typeof value !== 'undefined') {
                this.blog.slug = slugify(value, {
                    lower: true
                });
            }
        },
        'blog.title': function (value) {
            console.log(value)
            // if (typeof value !== 'undefined' && this.blog.slug.length < 1) {
            //     this.blog.slug = slugify(value, {
            //         lower: true
            //     });
            // }
        },
    },

    computed: {
        showCustomFields() {
            return this.blog && this.customFieldSets && this.customFieldSets.length > 0;
        },
    },

    methods: {
        async createdComponent() {
            if (this.isCreateMode) {
                if (Shopware.Context.api.languageId !== Shopware.Context.api.systemLanguageId) {
                    Shopware.State.commit('context/setApiLanguageId', Shopware.Context.api.languageId)
                }

                if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                    Shopware.State.commit('context/resetLanguageToDefault');
                }
            }

            await Promise.all([
                this.getPluginConfig(),
                this.getBlog()
            ]);

            this.loadCustomFieldSets();

            this.isLoading = false;
        },

        loadCustomFieldSets() {
            this.customFieldDataProviderService.getCustomFieldSets('blog').then((sets) => {
                this.customFieldSets = sets;
            });
        },

        async getBlog() {
            if(!this.blogId) {
                this.blog = this.repository.create(Shopware.Context.api);

                return;
            }

            const criteria = new Criteria();
            criteria.addAssociation('blogCategories');

            return this.repository
                .get(this.blogId, Shopware.Context.api, criteria)
                .then((entity) => {
                    this.blog = entity;

                    this.blog.slug = entity.translated.slug;
                    console.log(entity);
                    console.log(this.blog);

                    return Promise.resolve();
                });
        },
    }
});
