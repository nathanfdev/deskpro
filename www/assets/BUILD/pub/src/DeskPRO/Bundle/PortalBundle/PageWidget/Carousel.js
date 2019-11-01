import $ from 'jquery';
import 'slick-carousel';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class Carousel extends PageWidget {

  renderWidget() {
    $(this.$element).slick({
      dots:           true,
      infinite:       false,
      speed:          300,
      slidesToShow:   4,
      slidesToScroll: 4,
      nextArrow:      '<button class="dp-po-news-carousel-arrow dp-po-news-carousel-arrow-right"><i class="dp-po-icon far fa-angle-right"></i></button>',
      prevArrow:      '<button class="dp-po-news-carousel-arrow dp-po-news-carousel-arrow-left"><i class="dp-po-icon far fa-angle-left"></i></button>',
      responsive:     [{
        breakpoint: 768,
        settings:   {
          slidesToShow:   1,
          slidesToScroll: 1,
          infinite:       true,
          dots:           true,
          arrows:         false,
        }
      }]
    });
  }
}
