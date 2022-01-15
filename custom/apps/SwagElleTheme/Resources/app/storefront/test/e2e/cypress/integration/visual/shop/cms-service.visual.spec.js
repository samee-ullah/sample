describe('Shop page: CMS service page', () => {
    beforeEach(() => {
        cy.setToInitialState()
            .then(() => {
                cy.loginViaApi()
            })
            .then(() => {
                cy.createDefaultFixture('category',{}, 'footer-category-first');
            })
            .then(() => {
                cy.createDefaultFixture('category',{}, 'footer-category-second');
            })
            .then(() => {
                cy.createDefaultFixture('category',{}, 'footer-category-third');
            })
            .then(() => {
                cy.searchViaAdminApi({
                    endpoint: 'sales-channel',
                    data: {
                        field: 'name',
                        value: 'Storefront'
                    }
                });
            })
            .then((salesChannel) => {
                // This ID of the fixture is set by purpose, thus being predictable
                cy.fixture('footer-category-first').then((category) => {
                    cy.updateViaAdminApi('sales-channel', salesChannel.id, {
                        data: {
                            footerCategoryId: category.id,
                            maintenanceIpWhitelist: []
                        }
                    });
                })
            });
    });

    function assignToFooterLink() {
        cy.server();

        cy.route({
            url: `${Cypress.env('apiPath')}/category/*`,
            method: 'patch'
        }).as('saveCategory');

        cy.get('.sw-category-tree__inner .sw-tree-item__element').contains('Footer').get('.sw-tree-item__toggle').click();
        cy.get('a[href="#/sw/category/index/24c3c853a8354db89d04ce3a06dc5bbc"]').contains('Information').parents('.sw-tree-item__children').find('.sw-tree-item__toggle').click();
        cy.get('a[href="#/sw/category/index/8c287c93aae24001976d8aef2ade2f65"]').contains('Shipping and payment').click();
        cy.get('.sw-category-detail__tab-cms').click();
        cy.get('.sw-card.sw-category-layout-card').scrollIntoView();
        cy.get('.sw-category-detail-layout__change-layout-action').click();
        cy.get('.sw-modal__dialog').should('be.visible');

        cy.get('.sw-cms-layout-modal__content-item--0 .sw-field--checkbox').click();
        cy.get('.sw-modal .sw-button--primary').click();
        cy.get('.sw-card.sw-category-layout-card .sw-category-layout-card__desc-headline').contains('Shipping and payment');

        // Save layout
        cy.get('.sw-category-detail__save-action').click();
        cy.wait('@saveCategory').then((response) => {
            expect(response).to.have.property('status', 204);
        });
    }

    function createServicePage() {
        let salesChannel;

        return cy.searchViaAdminApi({
            endpoint: 'sales-channel',
            data: {
                field: 'name',
                type: 'equals',
                value: 'Storefront'
            }
        }).then((data) => {
            salesChannel = data.id;
            cy.createDefaultFixture('cms-page', {}, 'cms-service-page')
        }).then(() => {
            cy.openInitialPage(`${Cypress.env('admin')}#/sw/category/index`);
            assignToFooterLink();
        });
    }

    it('@visual @shopPage: assign service page to footer category', () => {
        createServicePage();
        cy.visit('/');
        cy.get('.js-cookie-configuration-button .btn-primary').contains('Configure').click({force: true});
        cy.get('.offcanvas .btn-primary').contains('Save').click();

        cy.get('.footer-link-item').should('be.visible');
        cy.get('.footer-link-item').contains('Shipping and payment');
        cy.get('a.footer-link').should('have.attr', 'href') // yields the "href" attribute
            .and('include', '/Information/Shipping-and-payment');

        cy.takeSnapshot('[CMS] Footer link should be visible', '.footer-main');

        cy.get('.footer-link').contains('Shipping and payment').click();

        cy.get('.cms-element-text').should('be.visible');
        cy.get('.cms-element-text').contains('Shipping and payment');
        cy.get('.breadcrumb-link.is-active').should('have.attr', 'href') // yields the "href" attribute
            .and('include', '/Information/Shipping-and-payment');

        cy.takeSnapshot('[CMS] Service and information page', '.cms-page');
    });
});
