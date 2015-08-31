import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import FeedbackForm from "DeskPRO/Bundle/PortalBundle/PageWidget/FeedbackForm"
import FeedbackFilter from "DeskPRO/Bundle/PortalBundle/React/FeedbackFilter"
import React from "react"

export default class HtmlLinkToPostWidget extends PageWidget {
  renderWidget() {
    let $postLinks = this.$element.find('a.post-link');
    $postLinks.each(function() {
      let $link = $(this);
      $link.click(function(e) {
        e.preventDefault();
        let action = $link.attr('href');
        let $form = $("<form></form>");
        $form.attr('action', action);
        $form.attr('method', 'POST');
        $('body').append($form);
        $form.submit();
        return false;
      });
    });
  }
}
