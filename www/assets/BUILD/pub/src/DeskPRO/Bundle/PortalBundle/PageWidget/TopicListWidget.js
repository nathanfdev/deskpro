import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import TopicList from '../React/Guides/TopicList';

class TopicListWidget extends PageWidget {
  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter(this.$element);

    ReactDOM.render(
      <TopicList topics={JSON.parse(window.topicList)} />,
      this.$rElement.get(0)
    );
  }
}
export default TopicListWidget;
