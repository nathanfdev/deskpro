import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import TicketForm from './TicketForm';
import HelpcenterFormWidget from './HelpcenterFormWidget';
import { HcTicketList } from './HcTicketList';
import { DownloadsList } from './DownloadsList';
import { DownloadPopupWidget } from './DownloadPopupWidget';
import { HcCommunityTopicPage } from './HcCommunityTopicPage';
import { CommunityVoteWidget } from './CommunityVoteWidget';
import { ClickAwayDropdownWidget } from './ClickAwayDropdownWidget';
import { HtmlLinkToPostWidget } from './HtmlLinkToPostWidget';
import { HcOmniSearchWidget } from './HcOmniSearchWidget';
import { HelpcenterLoginDropdownWidget } from './HelpcenterLoginDropdownWidget';
import { HelpcenterSidebarFilters } from './HelpcenterSidebarFilters';
import { LogoutButtonWidget } from './LogoutButtonWidget';
import { MobileMenuWidget } from './MobileMenuWidget';
import { HcArticleHighlighter } from './HcArticleHighlighter';
import { CustomPerFieldEdit } from './Common/Form/CustomPerFieldEdit';
import { Attachment } from './Common/Attachment';
import { SocialShare } from './SocialShare';
import { CloseTicketWidget } from './CloseTicketWidget';
import { CloseFlashWidget } from './CloseFlashWidget';
import { DpxTabs } from './Common/DpxTabs';
import { Carousel } from './Carousel';
import { SearchTabs } from './SearchTabs';
import { TitleAnchorWidget } from './TitleAnchorWidget';
import { RemoveCCTicketReply } from './RemoveCCTicketReply';
import { MobileCategories } from './MobileCategories';
import { DpxFormClearDraft } from './Common/Form/Draft/DpxFormClearDraft';
import { HelpcenterCCForm } from './HelpcenterCCForm';
import { HelpcenterCCDelete } from './HelpcenterCCDelete';
import { HelpcenterGuideFilter } from './HelpcenterGuideFilter';

class HelpCenterPage extends PageWidget {

  init() {
    this.addWidgetDef(HelpcenterFormWidget, '.helpcenter-form');
    this.addWidgetDef(HcOmniSearchWidget, '#helpcenter-omnisearch');
    this.addWidgetDef(HelpcenterLoginDropdownWidget, '#hc-top-login-btn');
    this.addWidgetDef(LogoutButtonWidget, '#top-logout-btn');
    this.addWidgetDef(TicketForm, '#new_ticket_page');
    this.addWidgetDef(TicketForm, '#edit_ticket_form');
    this.addWidgetDef(HcTicketList, '#hc_ticket_list_page');
    this.addWidgetDef(DownloadPopupWidget, '.dpx-download-popup');
    this.addWidgetDef(DownloadsList, '.download-list');
    this.addWidgetDef(HcCommunityTopicPage, '#hc_community_page');
    // this is specifically on the view page, because the vote widget is managed
    // manually via the react component on the filter page
    this.addWidgetDef(CommunityVoteWidget, 'a.dp-po-like');
    this.addWidgetDef(HtmlLinkToPostWidget, 'body');
    this.addWidgetDef(CustomPerFieldEdit, '.form-custom-per-field');
    this.addWidgetDef(ClickAwayDropdownWidget, '.clickaway-dropdown');
    this.addWidgetDef(HcArticleHighlighter, '.dpx-hc-kb-article-content');
    this.addWidgetDef(MobileMenuWidget, '.dpx-toggle-big-buttons');
    this.addWidgetDef(Attachment, '.dpx-attachment');
    this.addWidgetDef(DpxTabs, '.dpx-tabs');
    this.addWidgetDef(SocialShare, '#social-share');
    this.addWidgetDef(CloseTicketWidget, '#closeTicketBtn');
    this.addWidgetDef(CloseFlashWidget, '.dp-po-message-bar-close');
    this.addWidgetDef(Carousel, '.dpx-carousel');
    this.addWidgetDef(SearchTabs, '.dp-po-search-sidebar-link');
    this.addWidgetDef(SearchTabs, '.dp-po-search-tabs-link');
    this.addWidgetDef(TitleAnchorWidget, '.dp-po-post-content :header');
    this.addWidgetDef(RemoveCCTicketReply, '.remove-cc-from-reply');
    this.addWidgetDef(MobileCategories, '.dp-po-category-title-expand');
    this.addWidgetDef(HelpcenterSidebarFilters, '.dpx-sidebar-filters');
    this.addWidgetDef(DpxFormClearDraft, '.dpx-clear-draft');
    this.addWidgetDef(HelpcenterCCForm, '#add-cc-user-popover form');
    this.addWidgetDef(HelpcenterCCDelete, '.dp-po-ticket-meta-cc-remove');
    this.addWidgetDef(HelpcenterGuideFilter, '#guide_filter_input');

    return new Promise((resolve) => {
      $(document).ready(resolve);
      if (window.DP_LOAD_FN && window.DP_LOAD_FN.length) {
        window.DP_LOAD_FN.forEach((fn) => {
          fn();
        });
      }
      window.DP_PAGE_IS_READY = true;
    });
  }

  renderWidget() {
    $(document.body).addClass('with-pageload-dpx-done');
  }
}

export default HelpCenterPage;
