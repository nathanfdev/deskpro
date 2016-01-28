import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { PortalCheckbox } from '../../../React/Form/PortalCheckbox';
import $ from 'jquery';

export class DpxCheckboxGroup extends PageWidget {

  renderWidget() {
    this.$element.find('input[type="checkbox"]').each((i, node) => {
      const $checkbox = $(node);
      const $label = $('label[for="' + $checkbox.attr('id') + '"]', this.$element);
      const $rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter($checkbox);

      $checkbox.hide();
      $label.hide();

      ReactDOM.render(React.createElement(PortalCheckbox, { $checkbox, $label }), $rElement.get(0));
    });
  }
}
