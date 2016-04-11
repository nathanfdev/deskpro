import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class MobileTopbarWidget extends PageWidget {

  renderWidget() {
    const $toggle = this.$element;
    const $w = $(window);

    $toggle.click((ev) => {
      ev.preventDefault();
      if ($w.width() <= 760) {
        $('.top-bar #menu .hideable').toggleClass('hidden');
        $(this.$element).find('.fa').toggleClass('fa-angle-double-down fa-angle-double-up');
      }
    });

    // in the event that the "mobile" view is active, and the menu is "closed", and then the user
    // makes the browser bigger, we need to show the buttons again.
    $w.resize(() => {
      if ($w.width() > 760) {
        $('.top-bar #menu .hideable').removeClass('hidden');
        $(this.$element).find('.fa').addClass('fa-angle-double-down').removeClass('fa-angle-double-up');
      }
    });
  }
}
