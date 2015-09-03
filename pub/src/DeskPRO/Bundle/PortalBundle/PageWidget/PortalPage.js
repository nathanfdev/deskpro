import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import NewTicketForm from "DeskPRO/Bundle/PortalBundle/PageWidget/NewTicketForm";
import EditTicketForm from "DeskPRO/Bundle/PortalBundle/PageWidget/EditTicketForm";
import TicketView from "DeskPRO/Bundle/PortalBundle/PageWidget/TicketView";
import FeedbackPage from "DeskPRO/Bundle/PortalBundle/PageWidget/FeedbackPage";
import HTmlLinkToPostWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/HtmlLinkToPostWidget";
import OmniSearchWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/OmniSearchWidget";
import ContactUsDropdownWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/ContactUsDropdownWidget";
import CustomPerFieldEdit from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/CustomPerFieldEdit";
import $ from "jquery";

export default class PortalPage extends PageWidget {
  init() {
    this.addWidgetDef(OmniSearchWidget, "#omnisearch");
    this.addWidgetDef(ContactUsDropdownWidget, ".search-and-ticket .search-and-ticket-dropdown");
    this.addWidgetDef(NewTicketForm, "#new_ticket_page");
    this.addWidgetDef(EditTicketForm, "#edit_ticket_page");
    this.addWidgetDef(TicketView, "#ticket_view_page");
    this.addWidgetDef(FeedbackPage, "#feedback_page");
    this.addWidgetDef(HTmlLinkToPostWidget, "body");
    this.addWidgetDef(CustomPerFieldEdit, ".form-custom-per-field");
    return new Promise((resolve) => {
      $(document).ready(resolve);
      if (window.DP_LOAD_FN && window.DP_LOAD_FN.length) {
        window.DP_LOAD_FN.forEach((fn) => {
          fn();
        });
      }
    });
  }
}
