import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from "src/helper/dom-access.helper";
import ViewportDetection from "src/helper/viewport-detection.helper";

export default class StickyHeader extends Plugin {
    static options = {
        cloneElClass: 'js-main-header-sticky',
        cloneHeaderLogoClass: '.header-logo-col',
        navigationFlyouts: '.navigation-flyouts',
        showOnScrollPosition: 100
    }

    init() {
        this.pluginManager = window.PluginManager;

        this.subscribeViewPortEvents();

        if (this.pluginShouldBeActive()) {
            this.initializePlugin();
        }
    }

    subscribeViewPortEvents() {
        document.$emitter.subscribe('ViewPort/hasChanged', this.update, {scope: this});
    }

    update() {
        if (this.pluginShouldBeActive()) {
            if (this._initialized) return;

            this.initializePlugin();
        } else {
            if (!this._initialized && this._navClone.length() < 1) return;

            this.destroy();
        }
    }

    initializePlugin() {
        this.createStickyHeader();
        this.addEventListeners();
        this.reinitializePlugin();

        this._initialized = true;
    }

    destroy() {
        this._navClone.remove();
        this.removeEventListeners();

        this._initialized = false;
    }

    pluginShouldBeActive() {
        if (['XS', 'SM', 'MD'].includes(ViewportDetection.getCurrentViewport())) {
            return false;
        }

        return true;
    }

    createStickyHeader() {
        // Create Sticky Header Base
        this._navClone = this.el.cloneNode(true);
        this._navClone.classList.add(this.options.cloneElClass);
        this._navClone.append(DomAccess.querySelector(document, this.options.navigationFlyouts).cloneNode(true));

        // Decorates Sticky Header
        this.decorateStickyHeader();

        // Push it to body
        document.body.appendChild(this._navClone);
    }

    decorateStickyHeader() {
        const headerLogo = DomAccess.querySelector(document, this.options.cloneHeaderLogoClass).cloneNode(true);
        this._navClone.prepend(headerLogo);

        DomAccess.querySelector(this._navClone, '.main-navigation').removeAttribute('id');
        this._navClone.removeAttribute('data-sticky-header');
        this._navClone.setAttribute('data-flyout-menu', 'true');
    }

    addEventListeners() {
        document.removeEventListener('scroll', this.onScroll.bind(this));
        document.addEventListener('scroll', this.onScroll.bind(this));
    }

    removeEventListeners() {
        document.removeEventListener('scroll', this.onScroll.bind(this));
    }

    onScroll() {
        const scrollPosition = document.documentElement.scrollTop;

        if (scrollPosition > this.options.showOnScrollPosition) {
            if (!this._navClone.classList.contains('is--active'))
                this._navClone.classList.add('is--active');
        } else {
            this._navClone.classList.remove('is--active');
        }
    }

    reinitializePlugin() {
        this.pluginManager.initializePlugin(
            'FlyoutMenu', '[data-flyout-menu="true"]', {}
        );
    }
}
