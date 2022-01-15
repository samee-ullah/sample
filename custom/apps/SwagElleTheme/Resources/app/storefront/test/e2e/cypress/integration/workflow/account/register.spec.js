import AccountPageObject from '../../../support/pages/account.page-object';

describe('Account: Register via account menu', { tags: ['@workflow', '@register'] }, () => {
    it('@workflow @register: trigger validation error', () => {
        const page = new AccountPageObject();
        cy.visit('/');
        cy.get('.account-menu [type="button"]').click();
        cy.get('.account-menu-dropdown').should('be.visible');

        cy.get('.account-menu-register').children('a').click();
        cy.get(page.elements.registerCard).should('be.visible');

        cy.get('[name="email"]:invalid').should('be.visible');
        cy.get(`${page.elements.registerSubmit} [type="submit"]`).click();
    });

    it('@workflow @register: fill registration form and submit', () => {
        const page = new AccountPageObject();
        cy.visit('/account/login');
        cy.get(page.elements.registerCard).should('be.visible');

        cy.get('select[name="salutationId"]').select('Mr.');
        cy.get('input[name="firstName"]').type('John');
        cy.get('input[name="lastName"]').type('Doe');

        cy.get(`${page.elements.registerForm} input[name="email"]`).type('john-doe-for-testing@example.com');
        cy.get(`${page.elements.registerForm} input[name="password"]`).type('1234567890');

        cy.get('input[name="billingAddress[street]"]').type('123 Main St');
        cy.get('input[name="billingAddress[zipcode]"]').type('9876');
        cy.get('input[name="billingAddress[city]"]').type('Anytown');

        cy.get('select[name="billingAddress[countryId]"]').select('USA');
        cy.get('select[name="billingAddress[countryStateId]"').should('be.visible');

        cy.get('select[name="billingAddress[countryStateId]"]').select('Ohio');

        cy.get(`${page.elements.registerSubmit} [type="submit"]`).click();

        cy.url().should('not.include', '/register');
        cy.url().should('include', '/account');

        cy.get('.account-welcome h1').should((element) => {
            expect(element).to.contain('Overview');
        });
    });

    it('@workflow @register: fill registration form and submit with full config enable', () => {
        cy.authenticate().then((result) => {
            const requestConfig = {
                headers: {
                    Authorization: `Bearer ${result.access}`
                },
                method: 'post',
                url: `api/_action/system-config/batch`,
                body: {
                    null: {
                        'core.loginRegistration.showAccountTypeSelection': true,
                        'core.loginRegistration.showTitleField': true,
                        'core.loginRegistration.requireEmailConfirmation': true,
                        'core.loginRegistration.doubleOptInRegistration': true,
                        'core.loginRegistration.requirePasswordConfirmation': true,
                        'core.loginRegistration.showPhoneNumberField': true,
                        'core.loginRegistration.phoneNumberFieldRequired': true,
                        'core.loginRegistration.showBirthdayField': true,
                        'core.loginRegistration.birthdayFieldRequired': true,
                        'core.loginRegistration.showAdditionalAddressField1': true,
                        'core.loginRegistration.additionalAddressField1Required': true,
                        'core.loginRegistration.showAdditionalAddressField2': true,
                        'core.loginRegistration.additionalAddressField2Required': true,
                        'core.loginRegistration.requireDataProtectionCheckbox': true,
                    }
                }
            };

            return cy.request(requestConfig);
        });

        const page = new AccountPageObject();
        cy.visit('/account/login');
        cy.get(page.elements.registerCard).should('be.visible');

        const accountTypeSelector = 'select[name="accountType"]';
        const accountTypeSelectorForDifferentAddress = 'select[name="shippingAddress[accountType]"]';

        cy.get(accountTypeSelector).should('be.visible');

        cy.get(accountTypeSelector).select('Commercial');
        cy.get('select[name="salutationId"]').select('Mr.');
        cy.get('input[name="title"]').type('Master');
        cy.get('input[name="firstName"]').type('John');
        cy.get('input[name="lastName"]').type('Doe');
        cy.get('select[name="birthdayDay"]').select('1');
        cy.get('select[name="birthdayMonth"]').select('1');
        cy.get('select[name="birthdayYear"]').select('2001');

        cy.get('#billingAddresscompany').type('Company ABC');
        cy.get('#billingAddressdepartment').type('ABC Department');
        cy.get('#vatIds').type('1234567');
        cy.get('#personalMail').type('testvat@gmail.com');
        cy.get('#personalMailConfirmation').type('testvat@gmail.com');
        cy.get('#personalPassword').type('password@123456');
        cy.get('#personalPasswordConfirmation').type('password@123456');
        cy.get('#billingAddressAddressCountry').select('Germany');
        cy.get('#billingAddressAddressCountryState').select('Berlin');

        cy.get('#billingAddressAddressStreet').type('ABC Ansgarstr 4');
        cy.get('#billingAddressAddressZipcode').type('49134');
        cy.get('#billingAddressAddressCity').type('Wallenhorst');
        cy.get('#billingAddressAddressCity').type('Wallenhorst');
        cy.get('#billingAddressAdditionalField1').type('DEF Ansgarstr 2');
        cy.get('#billingAddressAdditionalField2').type('GHK Ansgarstr 3');
        cy.get('#billingAddressAddressPhoneNumber').type('0123456789');

        cy.get('.register-different-shipping label[for="differentShippingAddress"]').click();

        cy.get(accountTypeSelectorForDifferentAddress).should('be.visible');

        cy.get(accountTypeSelectorForDifferentAddress).select('Commercial');
        cy.get('#shippingAddresspersonalSalutation').select('Mr.');
        cy.get('#shippingAddresspersonalTitle').type('Dr.');
        cy.get('#shippingAddresspersonalFirstName').type('Johny');
        cy.get('#shippingAddresspersonalLastName').type('Doe');
        cy.get('#shippingAddresscompany').type('Company XYZ');
        cy.get('#shippingAddressdepartment').type('XYZ Department');
        cy.get('#shippingAddressAddressStreet').type('XYZ Ansgarstr 20');
        cy.get('#shippingAddressAddressZipcode').type('67890');
        cy.get('#shippingAddressAddressCity').type('Newland');
        cy.get('#shippingAddressAddressCountry').select('Germany');
        cy.get('#shippingAddressAdditionalField1').type('XYZ Ansgarstr 202');
        cy.get('#shippingAddressAdditionalField2').type('XYZ Ansgarstr 201');
        cy.get('#shippingAddressAddressPhoneNumber').type('0123456788');

        cy.get('input[name="acceptedDataProtection"]').should('not.be.visible')
            .check({ force: true })
            .should('be.checked');

        cy.get(`${page.elements.registerSubmit} [type="submit"]`).click();

        cy.get('.alert').should('have.class', 'alert-success');

        cy.authenticate().then((result) => {
            const requestConfig = {
                headers: {
                    Authorization: `Bearer ${result.access}`
                },
                method: 'post',
                url: `api/_action/system-config/batch`,
                body: {
                    null: {
                        'core.loginRegistration.showAccountTypeSelection': false,
                        'core.loginRegistration.showTitleField': false,
                        'core.loginRegistration.requireEmailConfirmation': false,
                        'core.loginRegistration.doubleOptInRegistration': false,
                        'core.loginRegistration.requirePasswordConfirmation': false,
                        'core.loginRegistration.showPhoneNumberField': true,
                        'core.loginRegistration.phoneNumberFieldRequired': false,
                        'core.loginRegistration.showBirthdayField': false,
                        'core.loginRegistration.birthdayFieldRequired': false,
                        'core.loginRegistration.showAdditionalAddressField1': false,
                        'core.loginRegistration.additionalAddressField1Required': false,
                        'core.loginRegistration.showAdditionalAddressField2': false,
                        'core.loginRegistration.additionalAddressField2Required': false,
                        'core.loginRegistration.requireDataProtectionCheckbox': false,
                    }
                }
            };

            return cy.request(requestConfig);
        });
    });
});
