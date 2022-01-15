import AccountPageObject from '../../../support/pages/account.page-object';

describe('Account: Visual tests login as customer', { tags: ['@workflow', '@login'] }, () => {
    beforeEach(() => {
        return cy.setToInitialState()
            .then(() => {
                return cy.createCustomerFixtureStorefront()
            })
            .then(() => {
                cy.visit('/');
            })
    });

    it('@workflow @login: check appearance of login with wrong credentials', () => {
        const page = new AccountPageObject();

        cy.get('.account-menu [type="button"]').click();
        cy.get('.account-menu-dropdown').should('be.visible');

        cy.get('.account-menu-login-button').click();
        cy.get(page.elements.loginCard).should('be.visible');
        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('Anything');
        cy.get(`${page.elements.loginSubmit} [type="submit"]`).click();

        cy.get('.alert-danger').should((element) => {
            expect(element).to.contain('Could not find an account that matches the given credentials.');
        });

        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');

        cy.get(`${page.elements.loginSubmit} [type="submit"]`).click();
        cy.get('.account-welcome h1').should((element) => {
            expect(element).to.contain('Overview');
        });
    });
});
