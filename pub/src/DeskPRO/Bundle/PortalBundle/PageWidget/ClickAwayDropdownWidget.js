import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import ContactUsDropdown from "DeskPRO/Bundle/PortalBundle/React/ContactUsDropdown"
import $ from "jquery"
import React from "react"
import ReactDOM from "react-dom"

export default class ClickAwayDropdownWidget extends PageWidget {
  renderWidget() {
    const $trigger = this.$element;
    const target_id = $trigger.data('clickaway-target');
    const $target = $(`#${target_id}`);

    $trigger.click((event) => {
      event.preventDefault();
      event.stopPropagation();
      $target.toggle();
    });

    $target.click((event) => {
      event.stopPropagation();
    });

    $(document).click(() => {
      $target.hide();
    });
  }
}
