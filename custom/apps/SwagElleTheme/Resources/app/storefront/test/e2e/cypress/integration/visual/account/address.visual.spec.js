import AccountPageObject from '../../../support/pages/account.page-object';

describe('Account: Address page', () => {
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

    it('@visual: update address page', () => {
        const page = new AccountPageObject();

        cy.visit('/account/login');
        cy.get(page.elements.loginCard).should('be.visible');

        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get(`${page.elements.loginSubmit} [type="submit"]`).click();

        cy.get('.account-content .account-aside-item[title="Addresses"]')
            .should('be.visible')
            .click();

        cy.get('.account-welcome h1').should((element) => {
            expect(element).to.contain('Addresses');
        });

        cy.takeSnapshot('[Account] Address page', '.account-content', {widths: [375, 768, 1920]});

        cy.get('a[href="/account/address/create"]').click();
        cy.get('.account-address-form').should('be.visible');

        cy.get('#addresspersonalSalutation').select('Mr.');
        cy.get('#addresspersonalFirstName').typeAndCheckStorefront('P.  ');
        cy.get('#addresspersonalLastName').typeAndCheckStorefront('Sherman');
        cy.get('#addressAddressStreet').typeAndCheckStorefront('42 Wallaby Way');
        cy.get('#addressAddressZipcode').typeAndCheckStorefront('2000');
        cy.get('#addressAddressCity').typeAndCheckStorefront('Sydney');
        cy.get('#addressAddressCountry').select('Australia');
        cy.get('.address-form-submit').scrollIntoView();
        cy.takeSnapshot('[Address] Fill in Create new address form', '.account-address-form', {widths: [375, 768, 1920]});

        cy.get('.address-form-submit').click();
        cy.get('.alert-success .alert-content').contains('Address has been saved.');
        cy.takeSnapshot('[Address] Address Created', '.account-address', {widths: [375, 768, 1920]});

        cy.get('.address-card .address-action-set-default').contains('Set as default shipping').click();
        cy.get('.alert-success .alert-content').contains('Default address has been changed.');
        cy.takeSnapshot('[Address] Address - Set default shipping address', '.account-address', {widths: [375, 768, 1920]});

        cy.get('.address-card .address-action-set-default').contains('Set as default billing').click();
        cy.get('.alert-success .alert-content').contains('Default address has been changed.');
        cy.takeSnapshot('[Address] Address - Set default billing address', '.account-address', {widths: [375, 768, 1920]});

        cy.get('.address-card:first-child()').contains('Edit').click();
        cy.get('#addresscompany').typeAndCheckStorefront('Company ABD');
        cy.get('#addressdepartment').typeAndCheckStorefront('Department ABF');
        cy.get('.address-form-submit').scrollIntoView();
        cy.takeSnapshot('[Address] Fill in edit address form', '.account-address', {widths: [375, 768, 1920]});

        cy.get('.address-form-submit').click();
        cy.get('.alert-success .alert-content').contains('Address has been saved.');
        cy.takeSnapshot('[Address] Change address form', '.account-address', {widths: [375, 768, 1920]});

        cy.get('.address-card').contains('Delete').click();
        cy.get('.alert-success .alert-content').contains('Address has been deleted.');
        cy.takeSnapshot('[Address] Delete address', '.account-address', {widths: [375, 768, 1920]});
    });
});
