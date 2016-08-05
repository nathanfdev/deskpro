import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';
import _ from 'lodash';

export class FeedbackVoteWidget extends PageWidget {

  renderWidget() {
    const $iAgreeBox = this.$element;

    $iAgreeBox.click(e => {
      e.preventDefault();

      if ($iAgreeBox.hasClass('closed')) {
        return false; // rate closed
      }
      if ($iAgreeBox.hasClass('rate_forbidden')) {
        return false; // no permission
      }
      if ($iAgreeBox.hasClass('agreed')) {
        return false; // already agreed
      }

      const action = $iAgreeBox.attr('href');
      const $counter = $iAgreeBox.find('span.counter');
      $counter.text(_.parseInt($counter.text()) + 1);
      $iAgreeBox.addClass('agreed');

      $.ajax({
        url:         action,
        method:      'POST',
        contentType: 'application/json'
      }).success(data => {
        if (!data.success) {
          $iAgreeBox.find('div').text(data.error);
          $counter.text(_.parseInt($counter.text()) - 1);
          $iAgreeBox.removeClass('agreed');
        }
      }).fail(() => {
        $counter.text(_.parseInt($counter.text()) - 1);
        $iAgreeBox.removeClass('agreed');
      });

      return false;
    });
  }
}
