import _ from "lodash";
import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import DpDropzone from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpDropzone";

//######################################################################################################################
//# Page widget
//######################################################################################################################

export default class NewTicketForm extends PageWidget {
  init() {
    this.addWidgetDef(DpDropzone, ".new-ticket-attachements-interactive");
  }

  renderWidget() {
    $('.button-reply').on('click', (ev) => {
      ev.preventDefault();
      $('.reply-form').slideToggle();
    });
  }
}
