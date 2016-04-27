import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class TouchFocusWidget extends PageWidget {

  renderWidget() {
    const openIfClosed = () => {
      if (!this.$element.hasClass('is-focused')) {
        this.$element.addClass('is-focused');
        $(document).on('click touchend', closeIfOpen);
      }
    };

    const closeIfOpen = () => {
      if (this.$element.hasClass('is-focused')) {
        this.$element.removeClass('is-focused');
        $(document).off('click touchend', closeIfOpen);
      }
    };

    this.$element.on('click touchend', (ev) => {
      if ($(ev.target).parents('.no-touch-focus').length === 0) {
        ev.stopPropagation();
        if ($(this).is('a')) {
          ev.preventDefault();
        }
        openIfClosed();
      }
    });
  }
}
