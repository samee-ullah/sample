import Plugin from 'src/plugin-system/plugin.class';

export default class AjMainSlider extends Plugin {
    init() {
        this.updateSlider();
    }

    updateSlider() {
        this.el.firstElementChild.classList.remove('cms-block-container');
        this.populateSliderContent();
    }

    populateSliderContent() {
        var sliderContent = $('.hidden-slider-content .slide-content');
        var ajMainSliderId = $('.aj-main-slider .image-slider-item-container.tns-slide-active')[0].id;
        var ajMainSliderPF = ajMainSliderId.split('-')[0];
        sliderContent.each(function (index, value) {
            $('#' + ajMainSliderPF + '-item' + index).append(value);
        });
    }
}
