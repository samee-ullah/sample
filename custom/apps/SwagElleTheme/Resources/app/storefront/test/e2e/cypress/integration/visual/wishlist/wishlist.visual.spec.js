import products from '../../../fixtures/listing-pagination-products.json';

const product = {
    "id": "6dfd9dc216ab4ac99598b837ac600368",
    "name": "Test product 1",
    "stock": 1,
    "productNumber": "RS-1",
    "descriptionLong": "Product description",
    "price": [
        {
            "currencyId": "b7d2554b0ce847cd82f3ac9bd1c0dfca",
            "net": 8.40,
            "linked": false,
            "gross": 10
        }
    ],
    "url": "/product-name.html",
    "manufacturer": {
        "id": "b7d2554b0ce847cd82f3ac9bd1c0dfca",
        "name": "Test variant manufacturer"
    },
};

describe('Wishlist: Check appearance of wishlist', () => {
    beforeEach(() => {
        cy.setToInitialState().then(() => {
            cy.authenticate().then((result) => {
                const requestConfig = {
                    headers: {
                        Authorization: `Bearer ${result.access}`
                    },
                    method: 'post',
                    url: `api/_action/system-config/batch`,
                    body: {
                        null: {
                            'core.cart.wishlistEnabled': true,
                            'core.listing.productsPerPage': 4
                        }
                    }
                };

                return cy.request(requestConfig);
            });
        })

        return cy.createCustomerFixtureStorefront()
            .then(() => {
                return cy.createProductFixture(product)
                    .then(() => {
                        cy.setCookie('wishlist-enabled', '1');
                    })
            })
    });

    it('@visual @wishlist: Wishlist empty page', () => {
        cy.visit('/wishlist');
        cy.get('.wishlist-listing-header').should('be.visible').contains('Your wishlist is empty');
        cy.takeSnapshot('[Wishlist] Empty page', '.wishlist-page');
    })

    it('@visual @wishlist: Wishlist state is set correctly', () => {
        cy.visit('/');

        cy.window().then((win) => {
            cy.expect(win.salesChannelId).to.not.empty;
            cy.expect(win.customerLoggedInState).to.equal(0);
            cy.expect(win.wishlistEnabled).to.equal(1);

            cy.visit('/account/login');

            // Login
            cy.get('.login-card').should('be.visible');
            cy.get('#loginMail').typeAndCheckStorefront('pep-erroni-for-testing@example.com');
            cy.get('#loginPassword').typeAndCheckStorefront('shopware');
            cy.get('.login-submit [type="submit"]').click();

            cy.window().then((win) => {
                cy.expect(win.customerLoggedInState).to.equal(1);
            });
        })
        cy.visit('/');

        cy.window().then((win) => {
            cy.get('.header-actions-btn .header-wishlist-icon .icon-heart svg').should('be.visible');
        })

        cy.takeSnapshot('[Wishlist] Home page with wishlist enable', 'body');
    });

    it('@visual @wishlist: Heart icon badge display on product box in product listing', () => {
        cy.visit('/');

        cy.window().then((win) => {
            let heartIcon = cy.get('.product-box .product-wishlist-action-circle').first();

            heartIcon.first().should('be.visible');
            heartIcon.first().should('have.class', 'product-wishlist-not-added');
            heartIcon.get('.icon-wishlist-not-added').should('be.visible');
            heartIcon.should('not.have.class', 'product-wishlist-added');
            cy.takeSnapshot('[Wishlist] Product box with inactive wishlist icon', '.product-box');

            cy.get('.product-box .product-wishlist-action-circle').first().click()

            heartIcon = cy.get('.product-box .product-wishlist-action-circle').first();

            heartIcon.should('have.class', 'product-wishlist-added');
            heartIcon.get('.icon-wishlist-added').first().should('be.visible');
            heartIcon.should('not.have.class', 'product-wishlist-not-added');
            cy.takeSnapshot('[Wishlist] Product box with active wishlist icon', '.product-box');
        })
    });

    it('@visual @wishlist: Heart icon badge display in product detail', () => {
        cy.visit('/');

        cy.window().then((win) => {
            cy.get('.product-name').first().click();
            cy.get('.product-wishlist-action').should('be.visible');

            cy.get('.icon-wishlist-not-added').should('be.visible');
            cy.get('.text-wishlist-not-added').should('be.visible').contains('Add to wishlist');
            cy.get('.icon-wishlist-added').should('not.be.visible');

            cy.get('.product-wishlist-action').click();

            cy.get('.icon-wishlist-added').should('be.visible');
            cy.get('.text-wishlist-remove').should('be.visible').contains('Remove from wishlist');
            cy.get('.icon-wishlist-not-added').should('not.be.visible');

            cy.takeSnapshot('[Wishlist] Product detail', '.product-detail');
        })
    });

    it('@visual @wishlist: Heart icon badge display the counter', () => {
        cy.visit('/');

        cy.get('.product-box .product-wishlist-action-circle').first().click();
        cy.takeSnapshot('[Wishlist] Heart icon counter', 'body');
    });

    it('@visual @wishlist: Heart icon badge display on product box in product listing pagination', () => {
        Array.from(products).forEach(product => cy.createProductFixture(product));

        cy.visit('/');

        Array.from(products).slice(0, 4).forEach(item => {
            let heartIcon = cy.get(`.product-wishlist-${item.id}`, {timeout: 10000}).first();

            heartIcon.should('have.class', 'product-wishlist-not-added');
            heartIcon.should('not.have.class', 'product-wishlist-added');

            heartIcon.click();

            heartIcon = cy.get(`.product-wishlist-${item.id}`, {timeout: 10000}).first();

            heartIcon.should('have.class', 'product-wishlist-added');
            heartIcon.should('not.have.class', 'product-wishlist-not-added');
        });

        cy.get('.pagination-nav .page-next').eq(0).click();

        Array.from(products).slice(4, 8).forEach(item => {
            let heartIcon = cy.get(`.product-wishlist-${item.id}`, {timeout: 10000}).first();

            heartIcon.should('have.class', 'product-wishlist-not-added');
            heartIcon.should('not.have.class', 'product-wishlist-added');

            heartIcon.click();

            heartIcon = cy.get(`.product-wishlist-${item.id}`, {timeout: 10000}).first();

            heartIcon.should('have.class', 'product-wishlist-added');
            heartIcon.should('not.have.class', 'product-wishlist-not-added');
        });

        cy.takeSnapshot('[Wishlist] Wishlist page', '.cms-block-product-listing');
    });
});
