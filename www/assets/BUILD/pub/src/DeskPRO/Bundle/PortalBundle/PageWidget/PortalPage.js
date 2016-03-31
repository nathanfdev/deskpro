import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { TicketForm } from './TicketForm';
import { PortalFormWidget } from './PortalFormWidget';
import { TicketView } from './TicketView';
import { TicketList } from './TicketList';
import { DownloadsList } from './DownloadsList';
import { DownloadPopupWidget } from './DownloadPopupWidget';
import { FeedbackPage } from './FeedbackPage';
import { ClickAwayDropdownWidget } from './ClickAwayDropdownWidget';
import { AlertsWidget } from './AlertsWidget';
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
import { TouchFocusWidget } from './TouchFocusWidget';
import { WidgetButton } from './WidgetButton';
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
    this.addWidgetDef(DownloadPopupWidget, '.dpx-download-popup');
    this.addWidgetDef(DownloadsList, '.download-list');
    this.addWidgetDef(FeedbackPage, '#feedback_page');
    this.addWidgetDef(HtmlLinkToPostWidget, 'body');
    this.addWidgetDef(CustomPerFieldEdit, '.form-custom-per-field');
    this.addWidgetDef(ClickAwayDropdownWidget, '.clickaway-dropdown');
    this.addWidgetDef(AgentBarWidget, '#agent-bar');
    this.addWidgetDef(ArticleHighlighter, '.dpx-kb-article-content');
    this.addWidgetDef(MobileMenuWidget, '.dpx-toggle-big-buttons');
    this.addWidgetDef(LoginPage, '#login-page');
    this.addWidgetDef(AlertsWidget, '#dpx-alerts');
    this.addWidgetDef(SearchResultsPage, '#search-results-page');
    this.addWidgetDef(Attachment, '.dpx-attachment');
    this.addWidgetDef(TouchFocusWidget, '.as-touch-focus');
    this.addWidgetDef(WidgetButton, '.widget-button');

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
