import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { CommunityTopicForm } from './CommunityTopicForm';
import { CommunityFilter } from '../React/Feedback/CommunityFilter';

export class CommunityTopicPage extends PageWidget {

  init() {
    this.addWidgetDef(CommunityTopicForm, '.feedback-form-interactive');
  }

  renderWidget() {
    const $interactiveFilterSection = this.$element.find('.feedback-filter-interactive');
    $interactiveFilterSection.hide();

    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter($interactiveFilterSection);
    ReactDOM.render(React.createElement(CommunityFilter, { filter_data: window.COMMUNITY_FILTER_STATE }), this.$rElement.get(0));
  }
}
