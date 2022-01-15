describe('Error: maintenance page', { tags: ['@workflow'] }, () => {
    beforeEach(() => {
        cy.loginViaApi()
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
                // Enable Maintenance mode
                cy.updateViaAdminApi('sales-channel', salesChannel.id, {
                    data: {
                        maintenance: true
                    }
                });
            })
            .then(() => {
                cy.visit('/', { failOnStatusCode: false });
            });
    });

    it('@workflow: enable maintenance mode', () => {
        cy.get('.maintenance-heading').contains('Maintenance mode');
        cy.get('.container-maintenance p').contains('We are currently updating this site. Please check back later.');
        cy.get('.container-maintenance img')
            .first()
            .should('have.attr', 'src')
            .and('match', /maintenance/);

        cy.get('.header-main').should('be.visible');
        cy.get('.footer-maintenance').should('be.visible');

        cy.loginViaApi()
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
                // Enable Maintenance mode
                cy.updateViaAdminApi('sales-channel', salesChannel.id, {
                    data: {
                        maintenance: false
                    }
                });
            })
            .then(() => {
                cy.visit('/', { failOnStatusCode: true });
            });
    });
});
