import AccountPageObject from '../../../support/pages/account.page-object';

describe('Account: Payment page', { tags: ['@workflow', '@payment'] }, () => {
    beforeEach(() => {
        return cy.setToInitialState()
            .then(() => {
                return cy.createCustomerFixtureStorefront()
            })
            .then(() => {
                cy.visit('/account/login');
            })
    });

    it('@workflow @payment: change payment workflow', () => {
        const page = new AccountPageObject();

        cy.get(page.elements.loginCard).should('be.visible');

        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get(`${page.elements.loginSubmit} [type="submit"]`).click();

        cy.get('.account-content .account-aside-item[title="Payment methods"]')
            .should('be.visible')
            .click();

        cy.get('.account-welcome h1').should((element) => {
            expect(element).to.contain('Payment methods');
        });

        cy.get('.payment-method:nth-child(2) input[name="paymentMethodId"]').should('not.be.visible')
            .check({ force: true })
            .should('be.checked');
        cy.get('.account-payment-card [type="submit"]').click();
        cy.get('.alert-success .alert-content').contains('Payment method has been changed.');
    });
});
