import React from 'react';
import ReactDOM from 'react-dom';
import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import PortalCheckbox from 'DeskPRO/Bundle/PortalBundle/React/Form/PortalCheckbox';
import $ from 'jquery';

export default class DpxCheckboxGroup extends PageWidget {
  renderWidget() {
    this.$element.find('input[type="checkbox"]').each(function() {
      let $checkbox = $(this);
      let $label = $('label[for="'+ $checkbox.attr('id')+'"]');
      this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter($checkbox);
      $checkbox.hide();
      $label.hide();
      ReactDOM.render(React.createElement(PortalCheckbox, { $checkbox, $label }), this.$rElement.get(0));
    });
  }
}
