let product = {};

describe('Account: Order page', () => {
    beforeEach(() => {
        return cy.setToInitialState()
            .then(() => {
                return cy.createProductFixture();
            })
            .then(() => {
                return cy.fixture('product');
            })
            .then((result) => {
                product = result;
                return cy.createCustomerFixtureStorefront();
            })
            .then(() => {
                return cy.searchViaAdminApi({
                    endpoint: 'product',
                    data: {
                        field: 'name',
                        value: 'Product name'
                    }
                });
            })
            .then((result) => {
                return cy.createOrder(result.id, {
                    username: 'pep-erroni-for-testing@example.com',
                    password: 'shopware'
                });
            })
            .then(() => {
                cy.visit('/');
            })
            .then(() => {
                cy.get('.js-cookie-configuration-button > .btn').should('be.visible').click();
                cy.get('.offcanvas-cookie > .btn').scrollIntoView().should('be.visible').click();
            });
    });

    it('@visual: reorder order', () => {
        // Login
        cy.visit('/account/order');
        cy.get('.login-card').should('be.visible');
        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get('.login-submit [type="submit"]').click();

        // Order detail is expandable
        cy.get('.order-table').should('be.visible');
        cy.get('.order-table:nth-of-type(1) .order-table-header-order-number').contains('Order number: 10000');
        cy.takeSnapshot('[Order] Order table', '.account-orders', {widths: [375, 768, 1920]});

        cy.get('.order-table:nth-of-type(1) .order-hide-btn').click();
        cy.get('.order-detail-content').should('be.visible');
        cy.takeSnapshot('[Order] Order Details', '.account-orders', {widths: [375, 768, 1920]});

        // Re-order past order
        cy.get('.order-table-header-context-menu').click();
        cy.get('.order-table-header-context-menu-content-form').should('be.visible');
        cy.takeSnapshot('[Order] Order form actions', '.account-orders', {widths: [375, 768, 1920]});

        cy.get('.order-table-header-context-menu-content-form button').click();
        cy.get('.cart-offcanvas').should('be.visible');
        cy.get('.cart-offcanvas .alert-content').contains('1 product has been added to the shopping cart.');
        cy.takeSnapshot('[Order] Re-order - Add product to shopping cart', '.container', {widths: [375, 768, 1920]});

        cy.get('.btn.btn-block.btn-primary').click();
        cy.get('.checkout').should('be.visible');
        cy.get('.confirm-main-header').contains('Complete order');
        cy.takeSnapshot('[Order] Re-order - Confirm order page', '.checkout', {widths: [375, 768, 1920]});

        cy.get('.custom-control.custom-checkbox input').click({force: true});
        cy.get('#confirmFormSubmit').click();

        // Verify order
        cy.get('.finish-header').contains('Thank you for your order');
        cy.get('.finish-ordernumber').contains('Your order number: #10001');
        cy.takeSnapshot('[Order] Re-order - Confirm finish page', '.checkout', {widths: [375, 768, 1920]});
    });

    it('@visual: cancel order', () => {
        // Enable refunds
        cy.loginViaApi().then(() => {
            cy.visit('/admin#/sw/settings/cart/index');
            cy.contains('Enable refunds').click();
            cy.get('.sw-settings-cart__save-action').click();
            cy.get('.icon--small-default-checkmark-line-medium').should('be.visible');
        });

        // Login
        cy.visit('/account/order');
        cy.get('.login-card').should('be.visible');
        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get('.login-submit [type="submit"]').click();

        cy.get('.order-table-header-context-menu').click();
        cy.get('.order-table-header-context-menu-content').should('be.visible');
        cy.takeSnapshot('[Order] Order actions', '.account-orders', {widths: [375, 768, 1920]});

        cy.get('.dropdown-menu > [type="button"]').click();
        cy.get('.order-history-cancel-modal').should('be.visible');
        cy.takeSnapshot('[Order] Order History Cancel Modal', '.account-orders', {widths: [375, 768, 1920]});

        cy.get('.order-history-cancel-modal .btn-primary').click();
        cy.get('.order-table-header-order-status.badge').contains('Cancelled');
        cy.takeSnapshot('[Order] Order Cancelled', '.account-orders', {widths: [375, 768, 1920]});
    });

    it('@visual: change payment', () => {
        // Login
        cy.visit('/account/order');
        cy.get('.login-card').should('be.visible');
        cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get('.login-submit [type="submit"]').click();

        // edit order
        cy.get('.order-table').should('be.visible');
        cy.get('.order-table-header-order-table-body > :nth-child(3)').contains('Invoice');
        cy.get('.order-table-header-context-menu').click();
        cy.takeSnapshot('[Order] Order actions change payment', '.account', {widths: [375, 768, 1920]});

        // change payment
        cy.get('a.order-table-header-context-menu-content-link').click();
        cy.get('.payment-methods').should('be.visible');
        cy.get('.payment-methods > :nth-child(3)').click();
        cy.get('#confirmOrderForm > .btn').scrollIntoView();
        cy.takeSnapshot('[Order] Change payment method', '.checkout', {widths: [375, 768, 1920]});

        cy.get('#confirmOrderForm > .btn').click();
        cy.get('.finish-order-subtitle').should('contain', 'Paid in advance');
        cy.takeSnapshot('[Order] Change payment method success', '.checkout', {widths: [375, 768, 1920]});
    });
});
