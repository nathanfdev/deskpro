import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { HcCommunityFilter } from '../React/Feedback/HcCommunityFilter';

export class HcCommunityTopicPage extends PageWidget {
  renderWidget() {
    const $interactiveFilterSection = this.$element.find('.community-filter-interactive');
    $interactiveFilterSection.hide();

    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter($interactiveFilterSection);
    ReactDOM.render(React.createElement(HcCommunityFilter, { filter_data: window.COMMUNITY_FILTER_STATE }), this.$rElement.get(0));
  }
}
