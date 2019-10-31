import $ from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import Slider from 'react-slick';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class Carousel extends PageWidget {

  renderWidget() {
    const $carouselWrapper = this.$element;
    const $slides = $(this.$element).find('.dpx-carousel-item');
    const slides = $slides.map((index, $slide) =>
      <div
        key={`news_carousel_slide_${index}`}
        className={$($slide).removeClass('dpx-carousel-item').attr('class')}
        dangerouslySetInnerHTML={{ __html: $slide.innerHTML }}
      />
    ).toArray();
    const settings = {
      dots:           true,
      infinite:       true,
      speed:          500,
      slidesToShow:   4,
      slidesToScroll: 4
    };
    const CarouselWrapper = (
      <Slider {...settings}>
        {slides}
      </Slider>
    );

    ReactDOM.render(CarouselWrapper, $carouselWrapper.get(0));
  }
}
