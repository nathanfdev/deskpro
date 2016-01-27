import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class DownloadsList extends PageWidget {

  renderWidget() {
    const me = this;
    this.$element.find('.as-vote-btn').on('click', function(ev) {
      const $voteBtn = $(this);
      const $widget = $voteBtn.closest('.as-vote-widget');
      const $count = $widget.find('.as-vote-count');

      ev.preventDefault();
      ev.stopPropagation();
      ev.stopImmediatePropagation();

      if ($voteBtn.hasClass('with-voted')) {
        $voteBtn.toggleClassClass('with-voted');
        $voteBtn.toggleClassClass('with-voted');
        return;
      }

      me.handleVote($widget, $voteBtn, $count);
    });
  }

  handleVote($widget, $voteBtn, $count) {
    const votes = parseInt($count.data('votes'), 10) || 0;
    $count.text(votes + 1);
    $widget.addClass('with-voted');

    const action = $voteBtn.attr('href');
    $.post(action);
  }
}
