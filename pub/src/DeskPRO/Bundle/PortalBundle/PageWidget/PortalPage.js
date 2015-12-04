import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import TicketForm from "DeskPRO/Bundle/PortalBundle/PageWidget/TicketForm";
import PortalFormWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/PortalFormWidget";
import TicketView from "DeskPRO/Bundle/PortalBundle/PageWidget/TicketView";
import TicketList from "DeskPRO/Bundle/PortalBundle/PageWidget/TicketList";
import DownloadsList from "DeskPRO/Bundle/PortalBundle/PageWidget/DownloadsList";
import FeedbackPage from "DeskPRO/Bundle/PortalBundle/PageWidget/FeedbackPage";
import ClickAwayDropdownWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/ClickAwayDropdownWidget";
import ClickToDismissWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/ClickToDismissWidget";
import HTmlLinkToPostWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/HtmlLinkToPostWidget";
import OmniSearchWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/OmniSearchWidget";
import LanguageChangerWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/LanguageChangerWidget";
import AgentBarWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/AgentBarWidget";
import LoginDropdownWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/LoginDropdownWidget";
import MobileMenuWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/MobileMenuWidget";
import ArticleHighlighter from "DeskPRO/Bundle/PortalBundle/PageWidget/ArticleHighlighter";
import CustomPerFieldEdit from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/CustomPerFieldEdit";
import $ from "jquery";

export default class PortalPage extends PageWidget {
  init() {
    this.addWidgetDef(OmniSearchWidget, "#omnisearch");
    this.addWidgetDef(LoginDropdownWidget, "#top-login-btn");
    this.addWidgetDef(LanguageChangerWidget, "#language-changer");
    this.addWidgetDef(TicketForm, "#new_ticket_page");
    this.addWidgetDef(TicketForm, "#edit_ticket_form");
    this.addWidgetDef(TicketView, "#ticket_view_page");
    this.addWidgetDef(TicketList, "#ticket_list_page");
    this.addWidgetDef(DownloadsList, ".download-list");
    this.addWidgetDef(FeedbackPage, "#feedback_page");
    this.addWidgetDef(HTmlLinkToPostWidget, "body");
    this.addWidgetDef(CustomPerFieldEdit, ".form-custom-per-field");
    this.addWidgetDef(ClickAwayDropdownWidget, ".clickaway-dropdown");
    this.addWidgetDef(ClickToDismissWidget, ".click-to-dismiss");
    this.addWidgetDef(AgentBarWidget, "#agent-bar");
    this.addWidgetDef(PortalFormWidget, ".dpx-form");
    this.addWidgetDef(ArticleHighlighter, ".dpx-kb-article-content");
    this.addWidgetDef(MobileMenuWidget, ".dpx-toggle-big-buttons");
    return new Promise((resolve) => {
      $(document).ready(resolve);
      if (window.DP_LOAD_FN && window.DP_LOAD_FN.length) {
        window.DP_LOAD_FN.forEach((fn) => {
          fn();
        });
      }
    });
  }

  renderWidget() {
    $(document.body).addClass('with-pageload-dpx-done');
  }
}
