import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import NewTicketForm from "DeskPRO/Bundle/PortalBundle/PageWidget/NewTicketForm";
import $ from "jquery";

export default class PortalPage extends PageWidget {
  init() {
    this.addWidgetDef(NewTicketForm, "#new_ticket_page");
    return new Promise((resolve) => {
      $(document).ready(resolve);
    });
  }

  renderWidget() {
    console.log("PORTAL PAGE RENDERED");
  }
}