import _ from "lodash";
import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";

//######################################################################################################################
//# Page widget
//######################################################################################################################

export default class NewTicketForm extends PageWidget {
  renderWidget() {
    $('.button-reply').on('click', (ev) => {
      ev.preventDefault();
      $('.reply-form').slideToggle();
    });
  }
}
