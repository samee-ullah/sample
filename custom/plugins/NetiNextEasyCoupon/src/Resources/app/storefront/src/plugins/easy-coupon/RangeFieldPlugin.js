'use strict';

import Plugin from 'src/plugin-system/plugin.class';

const PREVENT_SUBMIT_ATTRIBUTE = 'neti-next-easy-coupon-prevent-submit';

export default class RangeFieldPlugin extends Plugin {
    static options = {
        from: 0,
        to: 100,
        errorContainer: '.is--nec-value-status.error',
        hiddenFormInput: 'input#nec-value',
        buyButton: '.btn-buy',
    };

    hiddenFormInput;
    errorContainer;
    buyButton;

    constructor(...args) {
        super(...args);

        this.hiddenFormInput = document.querySelector(this.options.hiddenFormInput);
        this.errorContainer  = document.querySelectorAll(this.options.errorContainer);
        this.buyButton       = document.querySelectorAll(this.options.buyButton);

        this.applyValueToHiddenFormInput(this.el.value);

        this.registerEventListener();
    }

    init() {}

    registerEventListener() {
        this.el.addEventListener('input', this.onValueChange.bind(this));
        this.buyButton.forEach(node => node.addEventListener('click', this.onBuyBtnClick.bind(this)));
    }

    onValueChange(event) {
        const { value } = event.target;
        const state     = !isNaN(value) && this.isInputValid(value);

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

    isInputValid(value) {
        const { from, to } = this.options;

        return value >= from && value <= to;
    }

    applyValueToHiddenFormInput(value) {
        this.hiddenFormInput.value = value;
    }
}
