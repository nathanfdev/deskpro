import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { WidgetButton as ReactWidgetButton } from '../React/WidgetButton';
import $ from 'jquery';

export class WidgetButton extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);

    const component = React.createElement(ReactWidgetButton, {});

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
