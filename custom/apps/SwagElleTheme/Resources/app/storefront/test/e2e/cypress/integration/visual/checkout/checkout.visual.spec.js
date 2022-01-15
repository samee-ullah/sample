import CheckoutPageObject from "../../../support/pages/checkout.page-object";
import AccountPageObject from '../../../support/pages/account.page-object';

let product = {};

describe('Checkout: as a guest', () => {
    beforeEach(() => {
        return cy.createProductFixture()
            .then((result) => {
                product = result;
                cy.visit('/');
                cy.get('.cookie-permission-container .btn-primary').contains('Configure').click();
                cy.get('.offcanvas .btn-primary').contains('Save').click();
            });
    });

    it('@checkout: Run checkout with the same address', () => {
        const page = new CheckoutPageObject();
        const accountPage = new AccountPageObject();

        // Shopping cart
        cy.get('.header-cart-btn').should('be.visible').click();
        cy.takeSnapshot('[Checkout] Empty cart', '.offcanvas',{widths: [375, 768, 1920]});
        cy.get('.offcanvas .offcanvas-close').should('be.visible').click();

        // Product detail
        cy.get('.search-toggle-btn').should('be.visible').click();
        cy.get('.header-search-input').should('be.visible');
        cy.get('.header-search-input').type('dummy test');
        cy.get('.header-search-btn').should('be.visible').click();
        cy.takeSnapshot('[Checkout] No products found', '.container-main', {widths: [375, 768, 1920]});

        cy.get('.search-toggle-btn').should('be.visible').click();
        cy.get('.header-search-input').type(product.name);
        cy.get('.search-suggest-product-name').contains(product.name);
        cy.get('.search-suggest-product-price').contains(product.price[0].gross);
        cy.takeSnapshot('[Checkout] Search product result', '.header-search', {widths: [375, 768, 1920]});

        cy.get('.search-suggest-product-name').first().click();

        cy.get('.product-detail-buy .btn-buy').click();

        // Offcanvas
        cy.get(`${page.elements.offCanvasCart}.is-open`).should('be.visible');
        cy.get(`${page.elements.cartItem}-label`).contains(product.name);
        cy.takeSnapshot('[Checkout] Offcanvas', '.offcanvas', {widths: [375, 768, 1920]});

        // Checkout
        cy.get('.offcanvas-cart-actions .begin-checkout-btn').click();

        cy.get(accountPage.elements.registerCard).should('be.visible');

        cy.get('.login-collapse-toggle').should('be.visible').click();
        cy.takeSnapshot('[Checkout] Login/Register form', '.page-checkout-address-login', {widths: [375, 768, 1920]});

        cy.get('select[name="salutationId"]').select('Mr.');
        cy.get('input[name="firstName"]').type('John');
        cy.get('input[name="lastName"]').type('Doe');

        cy.get(`${accountPage.elements.registerForm} input[name="email"]`).type('john-doe-for-testing@example.com');
        cy.get('.register-guest-control.custom-checkbox label').scrollIntoView();
        cy.get('.register-guest-control.custom-checkbox label').click(1, 1);

        cy.get('input[name="billingAddress[street]"]').type('123 Main St');
        cy.get('input[name="billingAddress[zipcode]"]').type('9876');
        cy.get('input[name="billingAddress[city]"]').type('Anytown');

        cy.get('select[name="billingAddress[countryId]"]').select('USA');
        cy.get('select[name="billingAddress[countryStateId]"]').should('be.visible');
        cy.get('select[name="billingAddress[countryStateId]"]').select('Ohio');

        cy.takeSnapshot('[Checkout] Shipping information', '.checkout-container', {widths: [375, 768, 1920]});

        cy.get(`${accountPage.elements.registerSubmit} [type="submit"]`).click();

        // Checkout
        cy.get('.confirm-tos .card-title').contains('Terms and conditions and cancellation policy');
        cy.get('.confirm-tos .custom-checkbox label').scrollIntoView();
        cy.get('.confirm-tos .custom-checkbox label').click(1, 1);
        cy.get('.confirm-address').contains('John Doe');
        cy.get(`${page.elements.cartItem}-details-container ${page.elements.cartItem}-label`).contains(product.name);
        cy.get(`${page.elements.cartItem}-total-price`).contains(product.price[0].gross);
        cy.takeSnapshot('[Checkout] Complete order', '.checkout-container', {widths: [375, 768, 1920]});

        // Finish checkout
        cy.get('#confirmFormSubmit').scrollIntoView();
        cy.get('#confirmFormSubmit').click();
        cy.get('.finish-header').contains('Thank you for your order at Demostore!');
        cy.get('.checkout-aside-summary-total').contains(product.price[0].gross);
        cy.get('.col-5.checkout-aside-summary-value').contains('10.51');
        cy.takeSnapshot('[Checkout] Finish checkout', '.checkout-container', {widths: [375, 768, 1920]});
    });

    it('@checkout: Run checkout with the different shipping address', () => {
        const page = new CheckoutPageObject();
        const accountPage = new AccountPageObject();

        // Product detail
        cy.get('.search-toggle-btn').should('be.visible').click();
        cy.get('.header-search-input').should('be.visible');
        cy.get('.header-search-input').type(product.name);
        cy.get('.search-suggest-product-name').contains(product.name);
        cy.get('.search-suggest-product-price').contains(product.price[0].gross);
        cy.get('.search-suggest-product-name').first().click();
        cy.get('.product-detail-buy .btn-buy').click();

        // Offcanvas
        cy.get(`${page.elements.offCanvasCart}.is-open`).should('be.visible');
        cy.get(`${page.elements.cartItem}-label`).contains(product.name);

        // Checkout
        cy.get('.offcanvas-cart-actions .btn-primary').click();

        cy.get(accountPage.elements.registerCard).should('be.visible');

        const salutationIdSelector = `select[name*="salutationId"]`;
        const firstNameSelector = `input[name*="firstName"]`;
        const lastNameSelector = `input[name*="lastName"]`;
        const billingAddressStreetSelector = `input[name*="[street]"]`;
        const billingAddressZipcodeSelector = `input[name*="[zipcode]"]`;
        const billingAddressCitySelector = `input[name*="[city]"]`;
        const billingAddressCountryIdSelector = `select[name*="[countryId]"]`;
        const billingAddressCountryStateIdSelector = `select[name*="[countryStateId]"]`;

        cy.get(`.register-personal ${salutationIdSelector}`).select('Mr.');
        cy.get(`.register-personal ${firstNameSelector}`).type('John');
        cy.get(`.register-personal ${lastNameSelector}`).type('Doe');

        cy.get('.register-guest-control.custom-checkbox label').scrollIntoView();
        cy.get('.register-guest-control.custom-checkbox label').click(1, 1);
        cy.get(`${accountPage.elements.registerForm} input[name="email"]`).type('john-doe-for-testing@example.com');
        cy.get(`${accountPage.elements.registerForm} input[name="password"]`).should('not.be.visible');

        cy.get(`.register-billing ${billingAddressStreetSelector}`).type('123 Main St');
        cy.get(`.register-billing ${billingAddressZipcodeSelector}`).type('1234');
        cy.get(`.register-billing ${billingAddressCitySelector}`).type('Anytown');
        cy.get(`.register-billing ${billingAddressCountryIdSelector}`).select('Germany');
        cy.get(`.register-billing ${billingAddressCountryStateIdSelector}`).select('Berlin');

        cy.get('.register-different-shipping label[for="differentShippingAddress"]').click();

        cy.get(`.register-shipping ${salutationIdSelector}`).select('Mr.');
        cy.get(`.register-shipping ${firstNameSelector}`).type('John');
        cy.get(`.register-shipping ${lastNameSelector}`).type('Doe');
        cy.get(`.register-shipping ${billingAddressStreetSelector}`).type('987 Main St');
        cy.get(`.register-shipping ${billingAddressZipcodeSelector}`).type('9876');
        cy.get(`.register-shipping ${billingAddressCitySelector}`).type('Anytown');
        cy.get(`.register-shipping ${billingAddressCountryIdSelector}`).select('Germany');
        cy.get(`.register-shipping ${billingAddressCountryStateIdSelector}`).select('Berlin');
        cy.takeSnapshot('[Checkout] Shipping information with the alternative address', '.checkout-container', {widths: [375, 768, 1920]});

        cy.get(`${accountPage.elements.registerSubmit} [type="submit"]`).click();


        // Checkout
        cy.get('.confirm-tos .card-title').contains('Terms and conditions and cancellation policy');
        cy.get('.confirm-tos .custom-checkbox label').scrollIntoView();
        cy.get('.confirm-tos .custom-checkbox label').click(1, 1);
        cy.get('.confirm-address').contains('John Doe');
        cy.get(`${page.elements.cartItem}-details-container ${page.elements.cartItem}-label`).contains(product.name);
        cy.get(`${page.elements.cartItem}-total-price`).contains(product.price[0].gross);

        // Finish checkout
        cy.get('#confirmFormSubmit').scrollIntoView();
        cy.get('#confirmFormSubmit').click();
        cy.get('.finish-header').contains('Thank you for your order at Demostore!');
        cy.get('.checkout-aside-summary-total').contains(product.price[0].gross);
        cy.get('.col-5.checkout-aside-summary-value').contains('10.51');
    });
});
