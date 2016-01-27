import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { PortalAttach } from '../../../React/Form/PortalAttach';
import $ from 'jquery';

export class DpxAttach extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);
    ReactDOM.render(React.createElement(PortalAttach), this.$rElement.get(0));
  }
}
