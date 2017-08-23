import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class FeedbackVoteWidget extends PageWidget {

  renderWidget() {
    const $iAgreeBox = this.$element;

    $iAgreeBox.click((event) => {
      event.preventDefault();

      if ($iAgreeBox.hasClass('closed')) {
        return false; // rate closed
      }
      if ($iAgreeBox.hasClass('rate_forbidden')) {
        return false; // no permission
      }

      const agreed = $iAgreeBox.hasClass('agreed');
      const voteUpUrl = $iAgreeBox.data('vote-up-url');
      const voteDownUrl = $iAgreeBox.data('vote-down-url');
      const $counter = $iAgreeBox.find('span.counter');
      const onFail = () => {
        $counter.text(parseInt($counter.text(), 10) - (agreed ? -1 : 1));
        if (agreed) {
          $iAgreeBox.addClass('agreed');
        } else {
          $iAgreeBox.removeClass('agreed');
        }
      };

      $counter.text(parseInt($counter.text(), 10) + (agreed ? -1 : 1));
      if (agreed) {
        $iAgreeBox.removeClass('agreed');
      } else {
        $iAgreeBox.addClass('agreed');
      }

      $.ajax({
        url:         agreed ? voteDownUrl : voteUpUrl,
        method:      'POST',
        contentType: 'application/json'
      }).success((data) => {
        if (!data.success) {
          $iAgreeBox.find('div').text(data.error);
          onFail();
        }
      }).fail(onFail);

      return false;
    });
  }
}
