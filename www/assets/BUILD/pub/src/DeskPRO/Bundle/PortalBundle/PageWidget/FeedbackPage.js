import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { FeedbackForm } from './FeedbackForm';
import { FeedbackFilter } from '../React/Feedback/FeedbackFilter';

export class FeedbackPage extends PageWidget {

  init() {
    this.addWidgetDef(FeedbackForm, '.feedback-form-interactive');
  }

  renderWidget() {
    const $interactiveFilterSection = this.$element.find('.feedback-filter-interactive');
    $interactiveFilterSection.hide();

    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter($interactiveFilterSection);
    ReactDOM.render(React.createElement(FeedbackFilter, { filter_data: window.COMMUNITY_FILTER_STATE }), this.$rElement.get(0));
  }
}
