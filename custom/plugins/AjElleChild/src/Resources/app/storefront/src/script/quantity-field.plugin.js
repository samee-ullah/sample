import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from "src/helper/dom-access.helper";

export default class QuantityFieldPlugin extends Plugin {
    init() {
        this.minus = DomAccess.querySelector(this.el, '.decrease');
        this.plus = DomAccess.querySelector(this.el, '.increase');
        this.field = DomAccess.querySelector(this.el, 'input[type="number"]');

        this.registerEvents();
    }

    registerEvents() {
        this.field.addEventListener('input', this.changeQuantity.bind(this));

        this.minus.addEventListener('click', this.decreaseQuantity.bind(this));
        this.plus.addEventListener('click', this.increaseQuantity.bind(this));
    }

    changeQuantity() {
        const step = parseInt(this.options.purchaseSteps);
        const maxQty = parseInt(this.options.maxQty);
        const fieldQty = parseInt(this.field.value);

        if (fieldQty <= step) {
            this.field.value = step;
            return;
        }

        if (fieldQty > maxQty) {
            this.field.value = maxQty;
            return;
        }
    }

    decreaseQuantity() {
        const step = parseInt(this.options.purchaseSteps);
        const newQty = parseInt(this.field.value) - step;

        if (newQty <= step) {
            this.field.value = step;
            return;
        }
        this.field.value = newQty;
    }

    increaseQuantity() {
        const step = parseInt(this.options.purchaseSteps);
        const maxQty = parseInt(this.options.maxQty);
        const newQty = parseInt(this.field.value) + step;

        if (newQty > maxQty) {
            this.field.value = maxQty;
            return;
        }
        this.field.value = newQty;
    }
}
