import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import FeedbackForm from "DeskPRO/Bundle/PortalBundle/PageWidget/FeedbackForm"
import FeedbackFilter from "DeskPRO/Bundle/PortalBundle/React/FeedbackFilter"
import React from "react"

export default class FeedbackPage extends PageWidget {
  init() {
    this.addWidgetDef(FeedbackForm, ".feedback-form-interactive");
  }
  renderWidget() {
    let $interactiveFilterSection = this.$element.find('.feedback-filter-interactive');
    $interactiveFilterSection.hide();
    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter($interactiveFilterSection);
    React.render(React.createElement(FeedbackFilter, {filter_data: window.FEEDBACK_FILTER_STATE}), this.$rElement.get(0));

  }
}
