import React from 'react';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class FeedbackVoteWidget extends PageWidget {
  renderWidget() {
    let $iAgreeBox = this.$element;
    $iAgreeBox.click(function (e) {
      e.preventDefault();
      if ($iAgreeBox.hasClass('closed')) {
        return; // rate closed
      }
      if ($iAgreeBox.hasClass('rate_forbidden')) {
        // TODO if user is not connected redirect to login then redirect to action
        return; // rate_forbidden
      }
      if ($iAgreeBox.hasClass('agreed')) {
        return; // already agreed
      }
      let action = $iAgreeBox.attr('href');
      let $counter = $iAgreeBox.find('span.counter');
      $counter.text(_.parseInt($counter.text()) + 1);
      $iAgreeBox.addClass('agreed');
      $.ajax({
        url: action,
        method: 'POST',
        contentType: 'application/json'
      }).fail(function(){
        $counter.text(_.parseInt($counter.text()) - 1);
        $iAgreeBox.removeClass('agreed');
      });

      return false;
    });
  }
}
