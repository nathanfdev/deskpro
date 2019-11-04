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
      rows:           0,
      nextArrow:      $(this.$element).data('nextButtonTemplate'),
      prevArrow:      $(this.$element).data('prevButtonTemplate'),
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
