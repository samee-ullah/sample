import template from './sas-blog-author-detail.html.twig';

const {Component} = Shopware;

Component.override('sas-blog-author-detail', {
    template,
    inject: [
        'repositoryFactory',
        'customFieldDataProviderService'
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            blogAuthor: null,
            blogAuthorCustomFieldSets: null,
            processSuccess: false,
            availableTags: null,
            fileAccept: 'image/*',
            customFieldSets: null,
        };
    },

    computed: {
        showCustomFields() {
            return this.blogAuthor && this.customFieldSets && this.customFieldSets.length > 0;
        },
    },

    methods: {
        createdComponent() {
            this.isLoading = true;

            this.blogAuthorRepository.get(
                this.$route.params.id,
                Shopware.Context.api,
                this.defaultCriteria
            ).then((blogAuthor) => {
                this.blogAuthor = blogAuthor;

                // Custom Code
                this.loadCustomFieldSets()

                this.isLoading = false;
            });
        },

        loadCustomFieldSets() {
            this.customFieldDataProviderService.getCustomFieldSets('blog_author').then((sets) => {
                this.customFieldSets = sets;
            });
        }
    }
});
