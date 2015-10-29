import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import DpCheckbox from "DeskPRO/Bundle/PortalBundle/React/Standalone/DpCheckbox";
import ReactDOM from "react-dom"
import React from "react"

export default class DpCheckboxGroup extends PageWidget {
  renderWidget() {
    this.$element.find('input[type="checkbox"]').each(function() {
      let $checkbox = $(this);
      let $label = $('label[for="'+ $checkbox.attr('id')+'"]');
      this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter($checkbox);
      $checkbox.hide();
      $label.hide();
      ReactDOM.render(React.createElement(DpCheckbox, { $checkbox, $label }), this.$rElement.get(0));
    });
  }
}
