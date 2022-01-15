const ProductFixture = global.ProductFixtureService;
// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

/**
 * Takes a snapshot for percy visual testing
 * @memberOf Cypress.Chainable#
 * @name takeSnapshot
 * @param {String} title - Title of the screenshot
 * @param {String} [selectorToCheck = null] - Unique selector to make sure the module is ready for being snapshot
 * @param {Object} [width = null] - Screen width used for snapshot
 * @function
 */
Cypress.Commands.add('takeSnapshot', (title, selectorToCheck = null, width = { widths: [375, 768, 1920] }) => {
    if (!Cypress.env('usePercy')) {
        return;
    }

    if (selectorToCheck) {
        cy.get(selectorToCheck).should('be.visible');
    }

    if (!width) {
        cy.percySnapshot(title);
        return;
    }
    cy.percySnapshot(title, width);
});

/**
 * Cleans up any previous state by restoring database and clearing caches
 * @memberOf Cypress.Chainable#
 * @name cleanUpPreviousState
 * @function
 */
Cypress.Commands.overwrite('cleanUpPreviousState', (orig) => {
    if (Cypress.env('localUsage')) {
        return cy.exec(`${Cypress.env('shopwareRoot')}/bin/console e2e:restore-db`)
            .its('code').should('eq', 0);
    }

    return orig();
});

/**
 * Patch to update via admin api
 * @memberOf Cypress.Chainable#
 * @name patchViaAdminApi
 * @param {String} [endpoint = null] - Endpoint to patch
 * @param {Object} [data = null] - Data send to API
 * @function
 */
Cypress.Commands.add('patchViaAdminApi', (endpoint, data) => {
    return cy.requestAdminApi(
        'PATCH',
        `/api/${endpoint}?response=true`,
        data
    ).then((responseData) => {
        return responseData;
    });
});

/**
 * Create custom product fixture using Shopware API at the given endpoint
 * @memberOf Cypress.Chainable#
 * @name createCustomProductFixture
 * @function
 * @param {Object} [userData={}] - Options concerning creation
 * @param [String] [templateFixtureName = 'product'] - Specifies the base fixture name
 */
Cypress.Commands.add('createCustomProductFixture', (userData = {}, templateFixtureName = 'product', categoryName = 'Confirm input catalogue') => {
    const fixture = ProductFixture;

    return cy.fixture(templateFixtureName).then((result) => {
        return Cypress._.merge(result, userData);
    }).then((data) => {
        return fixture.setProductFixture(data, categoryName);
    });
});
