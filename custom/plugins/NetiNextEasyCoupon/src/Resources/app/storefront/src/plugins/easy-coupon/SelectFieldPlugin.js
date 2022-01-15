'use strict';

import Plugin from 'src/plugin-system/plugin.class';

const PREVENT_SUBMIT_ATTRIBUTE = 'neti-next-easy-coupon-prevent-submit';

export default class SelectFieldPlugin extends Plugin {
    static options = {
        valueButton: '.is--nec-voucher-value',
        errorContainer: '.is--nec-value-status.error',
        hiddenFormInput: 'input#nec-value',
        buyButton: '.btn-buy',
    };

    valueButton;
    hiddenFormInput;
    errorContainer;
    buyButton;

    constructor(...args) {
        super(...args);

        this.valueButton     = this.el.querySelectorAll(this.options.valueButton);
        this.hiddenFormInput = document.querySelector(this.options.hiddenFormInput);
        this.errorContainer  = document.querySelectorAll(this.options.errorContainer);
        this.buyButton       = document.querySelectorAll(this.options.buyButton);

        this.buyButton.forEach(node => this.toggleBuyButton(node, false));

        this.registerEventListener();
    }

    init() {}

    registerEventListener() {
        this.valueButton.forEach(node => node.addEventListener('click', this.onValueButtonClick.bind(this)));
        this.buyButton.forEach(node => node.addEventListener('click', this.onBuyBtnClick.bind(this)));
    }

    onValueButtonClick(event) {
        const value = parseFloat(event.target.getAttribute('data-rel'));
        const state = !isNaN(value);

        this.setValueButtonActive(state ? event.target : false);
        this.applyValueToHiddenFormInput(state ? value : 0);

        this.errorContainer.forEach(node => node.classList.toggle('d-none', state));
        this.buyButton.forEach(node => this.toggleBuyButton(node, state));
    }

    onBuyBtnClick(event) {
        if (event.target.hasAttribute(PREVENT_SUBMIT_ATTRIBUTE)) {
            event.preventDefault();
        }
    }

    toggleBuyButton(button, state) {
        button.classList.toggle('disabled', !state);
        button.toggleAttribute('disabled', !state);
        button.toggleAttribute(PREVENT_SUBMIT_ATTRIBUTE, !state);
    }

    setValueButtonActive(node) {
        this.valueButton.forEach(node => node.classList.remove('active'));

        if (false !== node) {
            node.classList.add('active');
        }
    }

    applyValueToHiddenFormInput(value) {
        this.hiddenFormInput.value = value;
    }
}
