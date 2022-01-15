import RangeFieldPlugin from './plugins/easy-coupon/RangeFieldPlugin';
import SelectFieldPlugin from './plugins/easy-coupon/SelectFieldPlugin';

window.PluginManager.register(
    'NetiNextEasyCouponRangeField',
    RangeFieldPlugin,
    '[neti-next-easy-coupon-range-field]'
);

window.PluginManager.register(
    'NetiNextEasyCouponSelectField',
    SelectFieldPlugin,
    '[neti-next-easy-coupon-select-field]'
);