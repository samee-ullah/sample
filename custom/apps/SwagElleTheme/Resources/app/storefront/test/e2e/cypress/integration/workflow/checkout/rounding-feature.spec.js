import CheckoutPageObject from "../../../support/pages/checkout.page-object";

let product = {};
let roundedNum = '0.50';

describe('Checkout: Use rounding feature', () => {

    beforeEach(() => {
        return cy.setToInitialState()
            .then(() => {
                cy.createProductFixture()
                    .then((result) => {
                        product = result;
                    });
            })
    });

    it(`@checkout: Run checkout with value of rounding is ${roundedNum}`, () => {
        cy.server();
        cy.route({
            url: '/api/currency/**',
            method: 'patch'
        }).as('saveData');

        cy.loginViaApi();

        cy.visit(`/admin#/sw/settings/currency/detail/${product.price[0].currencyId}`);

        cy.get('.sw-loader').should('not.exist');

        cy.contains('.sw-settings-price-rounding__headline', 'Grand total').scrollIntoView();

        cy.get('.sw-settings-price-rounding__grand-interval-select')
            .typeSingleSelectAndCheck(roundedNum, '.sw-settings-price-rounding__grand-interval-select');

        cy.get('.sw-settings-currency-detail__save-action').click();
        cy.wait('@saveData').then((xhr) => {
            expect(xhr).to.have.property('status', 204);
        });
        cy.get('.sw-loader').should('not.exist');

        cy.visit('/');

        const page = new CheckoutPageObject();

        cy.get('.search-toggle-btn').should('be.visible').click();
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();
        cy.get('.product-detail-buy .btn-buy').click();

        // Off canvas
        cy.get(`${page.elements.offCanvasCart}.is-open`).should('be.visible');
        cy.get(`${page.elements.cartItem}-label`).contains(product.name);

        // Checkout
        cy.get('.offcanvas-cart-actions .btn-primary').click();

        cy.get('.checkout-aside-summary-value.checkout-aside-summary-total-rounded').contains('10.50');
        cy.get('.checkout-aside-summary-value.checkout-aside-summary-total').contains('10.51');
    });
});
