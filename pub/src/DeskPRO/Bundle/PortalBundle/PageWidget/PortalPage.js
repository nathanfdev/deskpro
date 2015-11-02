import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import NewTicketForm from "DeskPRO/Bundle/PortalBundle/PageWidget/NewTicketForm";
import PortalFormWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/PortalFormWidget";
import TicketView from "DeskPRO/Bundle/PortalBundle/PageWidget/TicketView";
import FeedbackPage from "DeskPRO/Bundle/PortalBundle/PageWidget/FeedbackPage";
import ClickAwayDropdownWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/ClickAwayDropdownWidget";
import ClickToDismissWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/ClickToDismissWidget";
import HTmlLinkToPostWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/HtmlLinkToPostWidget";
import OmniSearchWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/OmniSearchWidget";
import LanguageChangerWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/LanguageChangerWidget";
import AgentBarWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/AgentBarWidget";
import LoginDropdownWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/LoginDropdownWidget";
import CustomPerFieldEdit from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/CustomPerFieldEdit";
import $ from "jquery";

export default class PortalPage extends PageWidget {
  init() {
    this.addWidgetDef(OmniSearchWidget, "#omnisearch");
    this.addWidgetDef(LoginDropdownWidget, "#top-login-btn");
    this.addWidgetDef(LanguageChangerWidget, "#language-changer");
    this.addWidgetDef(NewTicketForm, "#new_ticket_page");
    this.addWidgetDef(TicketView, "#ticket_view_page");
    this.addWidgetDef(FeedbackPage, "#feedback_page");
    this.addWidgetDef(HTmlLinkToPostWidget, "body");
    this.addWidgetDef(CustomPerFieldEdit, ".form-custom-per-field");
    this.addWidgetDef(ClickAwayDropdownWidget, ".clickaway-dropdown");
    this.addWidgetDef(ClickToDismissWidget, ".click-to-dismiss");
    this.addWidgetDef(AgentBarWidget, "#agent-bar");
    this.addWidgetDef(PortalFormWidget, ".dpx-form");
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
