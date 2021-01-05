import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class CommunityVoteWidget extends PageWidget {

  renderWidget() {
    const $iAgreeBox = this.$element;

    const onSuccess = (agreed) => {
      const $counter = $iAgreeBox.find('span.counter');
      if (agreed) {
        $iAgreeBox.removeClass('agreed');
      } else {
        $iAgreeBox.addClass('agreed');
      }
      $counter.text(parseInt($counter.text(), 10) + (agreed ? -1 : 1));
    };

    $iAgreeBox.click((event) => {
      event.preventDefault();
      const agreed = $iAgreeBox.hasClass('agreed');
      const voteUpUrl = $iAgreeBox.data('vote-up-url');
      const voteDownUrl = $iAgreeBox.data('vote-down-url');

      if ($iAgreeBox.hasClass('closed')) {
        return false; // rate closed
      }

      if ($iAgreeBox.hasClass('rate_forbidden')) {
        return false; // no permission
      }

      if ($iAgreeBox.hasClass('isDisabled')) {
        return false; // one request at a time
      }

      $iAgreeBox.addClass('isDisabled');

      setTimeout(() => {
        $iAgreeBox.removeClass('isDisabled');
      }, 5000);

      $.ajax({
        url:         agreed ? voteDownUrl : voteUpUrl,
        method:      'POST',
        contentType: 'application/json',
        dataType:    'json',
      }).success((data) => {
        $iAgreeBox.find('div').text(data.error);
        if (!data.success) {
          if ('redirect' in data) {
            window.location.href = data.redirect;
          }
          return;
        }
        onSuccess(agreed);
      }).fail((data) => {
        $iAgreeBox.find('div').text(data.error);
      }).done(() => {
        $iAgreeBox.removeClass('isDisabled');
      });

      return false;
    });
  }
}
