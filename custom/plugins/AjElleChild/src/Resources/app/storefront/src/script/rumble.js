import AddToCart from 'src/plugin/add-to-cart/add-to-cart.plugin'
import DomAccess from "src/helper/dom-access.helper"
import HttpClient from "src/service/http-client.service"

export default class Rumble extends AddToCart {
    init() {
        this.pluginManager = window.PluginManager
        this._cartEl = DomAccess.querySelector(document, '.header-cart')
        this._client = new HttpClient(window.accessKey, window.contextToken)
        super.init()
    }

    _openOffCanvasCart(instance, requestUrl, formData) {
        this._client.post(requestUrl, formData, this._afterAddItemToCart.bind(this))
    }

    _afterAddItemToCart() {
        this._refreshCartValue()

        this._rumbleButton()
    }

    _refreshCartValue() {
        const cartWidgetEl = DomAccess.querySelector(this._cartEl, '[data-cart-widget]')
        const cartWidgetInstance = this.pluginManager.getPluginInstanceFromElement(cartWidgetEl, 'CartWidget')
        cartWidgetInstance.fetch()
    }

    _rumbleButton() {
        this._cartEl.classList.add('rumble')
        window.setTimeout(() => {
            this._cartEl.classList.remove('rumble')
        }, 3000)
    }
}
