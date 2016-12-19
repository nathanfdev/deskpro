import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class DownloadsList extends PageWidget {

  renderWidget() {
    this.$element.find('.as-vote-widget').each((i, node) => {
      const $widget = $(node);
      const $btn = $widget.find('.as-vote-btn');
      const $count = $widget.find('.as-vote-count');

      $widget.on('vote', () => {
        const votes = parseInt($count.data('votes'), 10) || 0;

        $count.text(votes + 1);
        $widget.addClass('with-voted');
      });

      $btn.on('click', event => {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        if ($btn.hasClass('with-voted')) {
          $btn.toggleClass('with-voted');
          $btn.toggleClass('with-voted');
          return;
        }

        $.post($btn.attr('href'));
        $widget.trigger('vote');
      });
    });
  }
}
