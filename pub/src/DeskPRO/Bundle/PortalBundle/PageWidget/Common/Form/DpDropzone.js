import React from "react"
import ReactDOM from "react-dom"
import $ from "jquery"
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import DropzoneUpload from "DeskPRO/Bundle/PortalBundle/React/DropzoneUpload"

export default class DpDropzone extends PageWidget {
  renderWidget() {
    this.$element.hide();
    this.$element.find('input').each(function() {
      $(this).disable();
    });
    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter(this.$element);
    ReactDOM.render(React.createElement(DropzoneUpload, { inputName: this.$element.data('base-name') + '[0][upload]' }), this.$rElement.get(0));
  }
}
