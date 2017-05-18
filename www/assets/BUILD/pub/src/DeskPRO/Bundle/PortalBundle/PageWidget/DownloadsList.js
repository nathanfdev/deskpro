import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class DownloadsList extends PageWidget {

  renderWidget() {
    this.$element.find('.as-vote-widget').each((i, node) => {
      const $widget = $(node);
      const $btn = $widget.find('.as-vote-btn');
      const $count = $widget.find('.as-vote-count');

      $widget.on('vote', () => {
        const agreed = $btn.hasClass('agreed');
        const votes = parseInt($count.text(), 10) || 0;

        $count.text(votes + (agreed ? -1 : 1));
        if (!agreed) {
          $btn.addClass('agreed');
        } else {
          $btn.removeClass('agreed');
        }

        $widget.addClass('with-voted');
        if (!agreed) {
          $btn.addClass('agreed');
        } else {
          $btn.removeClass('agreed');
        }
      });

      $btn.on('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        const agreed = $btn.hasClass('agreed');
        const voteUpUrl = $btn.data('vote-up-url');
        const voteDownUrl = $btn.data('vote-down-url');

        $widget.removeClass('with-voted');

        setTimeout(() => {
          $.post(agreed ? voteDownUrl : voteUpUrl);
          $widget.trigger('vote');
        }, 100);
      });
    });
  }
}
