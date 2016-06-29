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
      if ($iAgreeBox.hasClass('agreed')) {
        return; // already agreed
      }
      let action = $iAgreeBox.attr('href');
      let $counter = $iAgreeBox.find('span.counter');
      $counter.text(_.parseInt($counter.text()) + 1);
      $iAgreeBox.addClass('agreed');
      $.ajax({
        url:         action,
        method:      'POST',
        contentType: 'application/json'
      }).success(function (data) {
        if (!data.success) {
          $iAgreeBox.find('div').text(data.error);
          $counter.text(_.parseInt($counter.text()) - 1);
          $iAgreeBox.removeClass('agreed');
        }
      }).fail(function () {
        $counter.text(_.parseInt($counter.text()) - 1);
        $iAgreeBox.removeClass('agreed');
      });

      return false;
    });
  }
}
