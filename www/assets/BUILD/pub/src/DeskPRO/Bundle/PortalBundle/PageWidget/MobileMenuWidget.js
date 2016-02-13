import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class MobileMenuWidget extends PageWidget {

  renderWidget() {
    const $toggle = this.$element;
    const $w = $(window);

    $toggle.click((ev) => {
      ev.preventDefault();
      $('.dpx-toggle-mobile').toggle();
    });

    // in the event that the "mobile" view is active, and the menu is "closed", and then the user
    // makes the browser bigger, we need to show the buttons again.
    $w.resize(() => {
      if ($w.width() > 760) {
        $('.dpx-toggle-mobile').show();
      }
    });
  }
}
