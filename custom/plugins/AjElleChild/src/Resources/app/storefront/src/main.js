import QuantityFieldPlugin from './script/quantity-field.plugin'
import StickyHeader from './script/sticky-header'
import Rumble from './script/rumble'
import AjMainSlider from './script/aj-main-slider';

// Register RumblePlugin via overriding AddToCartPlugin
window.PluginManager.override('AddToCart', Rumble, '[data-add-to-cart]');

// Register QuantityFieldPlugin
window.PluginManager.register('QuantityField', QuantityFieldPlugin, '[data-quantity-field]');

// Register StickyHeaderPlugin
window.PluginManager.register('StickyHeader', StickyHeader, '[data-sticky-header]');

// Register AjMainSlider
window.PluginManager.register('AjMainSlider', AjMainSlider, '.aj-main-slider');
