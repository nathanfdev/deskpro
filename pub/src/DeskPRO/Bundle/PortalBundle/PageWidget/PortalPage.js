import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import NewTicketForm from "DeskPRO/Bundle/PortalBundle/PageWidget/NewTicketForm";
import TicketView from "DeskPRO/Bundle/PortalBundle/PageWidget/TicketView";
import $ from "jquery";

export default class PortalPage extends PageWidget {
  init() {
    this.addWidgetDef(NewTicketForm, "#new_ticket_page");
    this.addWidgetDef(TicketView, "#ticket_view_page");
    return new Promise((resolve) => {
      $(document).ready(resolve);
    });
  }
}