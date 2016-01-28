import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { TicketForm } from './TicketForm';
import { PortalFormWidget } from './PortalFormWidget';
import { TicketView } from './TicketView';
import { TicketList } from './TicketList';
import { DownloadsList } from './DownloadsList';
import { FeedbackPage } from './FeedbackPage';
import { ClickAwayDropdownWidget } from './ClickAwayDropdownWidget';
import { ClickToDismissWidget } from './ClickToDismissWidget';
import { HideAlertsWidget } from './HideAlertsWidget';
import { LoginPage } from './LoginPage';
import { HtmlLinkToPostWidget } from './HtmlLinkToPostWidget';
import { OmniSearchWidget } from './OmniSearchWidget';
import { LanguageChangerWidget } from './LanguageChangerWidget';
import { AgentBarWidget } from './AgentBarWidget';
import { LoginDropdownWidget } from './LoginDropdownWidget';
import { SearchResultsPage } from './SearchResultsPage';
import { MobileMenuWidget } from './MobileMenuWidget';
import { ArticleHighlighter } from './ArticleHighlighter';
import { CustomPerFieldEdit } from './Common/Form/CustomPerFieldEdit';
import { Attachment } from './Common/Attachment';
import $ from 'jquery';

export class PortalPage extends PageWidget {

  init() {
    this.addWidgetDef(PortalFormWidget, '.dpx-form');
    this.addWidgetDef(OmniSearchWidget, '#omnisearch');
    this.addWidgetDef(LoginDropdownWidget, '#top-login-btn');
    this.addWidgetDef(LanguageChangerWidget, '#language-changer');
    this.addWidgetDef(TicketForm, '#new_ticket_page');
    this.addWidgetDef(TicketForm, '#edit_ticket_form');
    this.addWidgetDef(TicketView, '#ticket_view_page');
    this.addWidgetDef(TicketList, '#ticket_list_page');
    this.addWidgetDef(DownloadsList, '.download-list');
    this.addWidgetDef(FeedbackPage, '#feedback_page');
    this.addWidgetDef(HtmlLinkToPostWidget, 'body');
    this.addWidgetDef(CustomPerFieldEdit, '.form-custom-per-field');
    this.addWidgetDef(ClickAwayDropdownWidget, '.clickaway-dropdown');
    this.addWidgetDef(ClickToDismissWidget, '.click-to-dismiss');
    this.addWidgetDef(AgentBarWidget, '#agent-bar');
    this.addWidgetDef(ArticleHighlighter, '.dpx-kb-article-content');
    this.addWidgetDef(MobileMenuWidget, '.dpx-toggle-big-buttons');
    this.addWidgetDef(LoginPage, '#login-page');
    this.addWidgetDef(HideAlertsWidget, '#dpx-alerts');
    this.addWidgetDef(SearchResultsPage, '#search-results-page');
    this.addWidgetDef(Attachment, '.dpx-attachment');

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
