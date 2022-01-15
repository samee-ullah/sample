import AccountPageObject from '../../../support/pages/account.page-object';

describe('Account: Visual tests login as customer', () => {
    beforeEach(() => {
        return cy.setToInitialState()
            .then(() => {
                return cy.createCustomerFixtureStorefront();
            })
            .then(() => {
                cy.visit('/');
            }).then(() => {
                cy.get('.js-cookie-configuration-button > .btn').should('be.visible').click();
                cy.get('.offcanvas-cookie > .btn').scrollIntoView().should('be.visible').click();
            });
    });

    it('@visual: check appearance of login with wrong credentials', () => {
        const page = new AccountPageObject();
        cy.visit('/');

        cy.get('.account-menu [type="button"]').click();
        cy.get('.account-menu-dropdown').should('be.visible');
        cy.takeSnapshot('[Account] Menu icon', '.account-menu-dropdown', {widths: [375, 768, 1920]});

        cy.get('.account-menu-login-button').click();
        cy.get(page.elements.loginCard).should('be.visible');
        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('Anything');
        cy.get(`${page.elements.loginSubmit} [type="submit"]`).click();

        cy.get('.alert-danger').should((element) => {
            expect(element).to.contain('Could not find an account that matches the given credentials.');
        });
        cy.takeSnapshot('[Account] Login with invalid credentials', page.elements.loginCard, {widths: [375, 768, 1920]});

        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');

        cy.get(`${page.elements.loginSubmit} [type="submit"]`).click();
        cy.get('.account-welcome h1').should((element) => {
            expect(element).to.contain('Overview');
        });

        cy.takeSnapshot('[Account] Login with valid credentials', '.account', {widths: [375, 768, 1920]});
    });
});
