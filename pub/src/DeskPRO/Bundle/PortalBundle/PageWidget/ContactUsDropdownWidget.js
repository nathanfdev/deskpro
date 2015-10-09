import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import ContactUsDropdown from "DeskPRO/Bundle/PortalBundle/React/ContactUsDropdown"
import $ from "jquery"
import React from "react"
import ReactDOM from "react-dom"

export default class ContactUsDropdownWidget extends PageWidget {
  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget search-and-ticket-dropdown"></div>').insertAfter(this.$element);
    ReactDOM.render(React.createElement(ContactUsDropdown), this.$rElement.get(0));
  }
}
