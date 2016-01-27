import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { PortalAttach } from '../../../React/Form/DropZone/PortalAttach';
import $ from 'jquery';

export class DpxAttach extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);

    const component = React.createElement(PortalAttach, {
      widgetOptions: this.options
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
