import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { PortalAttach } from '../../../React/Form/DropZone/PortalAttach';
import $ from 'jquery';

export class DpxAttach extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);

    const $input = this.$element.find('input[type=file]');
    const inputName = $input.attr('name').replace('[0][upload]', '');
    const component = React.createElement(PortalAttach, {
      widgetOptions: this.options,
      $input,
      inputName
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
