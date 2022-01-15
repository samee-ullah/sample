let product = {};

describe('Wishlist: Disable wishlist', () => {
    beforeEach(() => {
        cy.setToInitialState().then(() => {
            cy.authenticate()
                .then((result) => {
                    const requestConfig = {
                        headers: {
                            Authorization: `Bearer ${result.access}`
                        },
                        method: 'post',
                        url: `api/_action/system-config/batch`,
                        body: {
                            null: {
                                'core.cart.wishlistEnabled': false // disable wishlist
                            }
                        }
                    };

                    return cy.request(requestConfig);
                });

            return cy.createCustomerFixtureStorefront()
                .then(() => {
                    return cy.createProductFixture()
                        .then(() => {
                            return cy.createDefaultFixture('category')
                        })
                        .then(() => {
                            return cy.fixture('product');
                        })
                        .then((res) => {
                            product = res;
                        })
                })
        })
    });

    it('@workflow @wishlist: Wishlist state is not set', () => {
        cy.visit('/');

        cy.window().then((win) => {
            cy.expect(win.customerLoggedInState).to.equal(undefined);
            cy.expect(win.wishlistEnabled).to.equal(undefined);
            cy.get('.header-actions-btn .header-wishlist-icon .icon-heart svg').should('not.exist');
        })
    });

    it('@workflow @wishlist: Heart icon badge is not display on product box in product listing', () => {
        cy.visit('/');

        cy.window().then((win) => {
            cy.get('.product-box .product-wishlist-action-circle').should('not.exist');
        })
    });
});
