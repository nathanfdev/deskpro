<?php

/**
 * This file is a set of asset bundles. The files and bundles
 * listed here declare how the 'assetic' build works.
 *
 * In templates, you can the template function dp_asset_html() to include the HTML
 * to add an asset to the page. For example, to include vendors (jquery, etc):
 *
 *     {{ dp_asset_html('agent_vendors') }}
 *
 * You can of course use any asset in the templates, they dont need to be defined here:
 *
 *    <script src="{{ asset('javascripts/something.js') }}"></script>
 *
 * ... it just wont be compiled/minified etc.
 *
 *
 * == Syntax ==
 *
 * Eac
 */
$CONFIG = [];

$CONFIG['OPTIONS'] = [
    'java_path'      => defined('DP_JAVA_PATH') ? DP_JAVA_PATH : '/usr/bin/java',
    'yui_compressor' => defined('DP_YUI_COMPRESSOR_PATH') ? DP_YUI_COMPRESSOR_PATH : (DP_ROOT.'/vendor-src/yuicompressor/yuicompressor.jar'),
    'nodejs'         => defined('DP_NODEJS_PATH') ? DP_NODEJS_PATH : '/usr/bin/nodejs',
    'less'           => defined('DP_LESSC_PATH') ? DP_LESSC_PATH : (DP_ROOT.'/../../www/assets/BUILD/web/node_modules/less/bin/lessc'),
    'smartsprites'   => defined(
        'DP_SMARTSPRITES_PATH'
    ) ? DP_SMARTSPRITES_PATH : (DP_ROOT.'/../../www/assets/BUILD/web/node_modules/gulp-smartsprites/smartsprites-0.2.9/smartsprites.sh'),
];

//##############################################################################
// JAVASCRIPTS
//##############################################################################

$CONFIG['agent'] = [
    'out'          => 'js/agent-all.js',
    'post_filters' => ['yui_simple'],
    'references'   => [
        'agent_vendors',
        'agent_common',
        'agent_deskpro_ui',
        'agent_misc',
        'agent_agent_ui',
        'agent_window_sections',
        'agent_settingswin',
        'agent_pages',
        'agent_pages_lists',
        'agent_element_handlers',
    ],
];

$CONFIG['agent_vendors'] = [
    'out'   => 'js/agent-vendors.js',
    'files' => [
        'vendor/modernizr.min.js',
        'javascripts/Orb/modernizr-ext.js',
        'node_modules/custom-event-polyfill/custom-event-polyfill.js',
        'vendor/jquery/jquery.min.js',
        'vendor/jquery/jquery-migrate.min.js',
        'vendor/jquery.patch.js',
        'vendor/jquery/jquery-ui/jquery-ui.min.js',
        'vendor/jquery/jquery-tmpl/jquery.tmpl.min.js',
        'vendor/jquery/jquery.cookie.js',
        'vendor/jquery/jquery.history.js',
        'vendor/jquery/tmpl.min.js',
        'bower_components/underscore/underscore-min.js',
        'vendor/jquery/jquery.localscroll.js',
        'vendor/jquery/jquery.mousewheel.js',
        'vendor/jquery/jquery.scrollTo.js',
        'vendor/jquery/jquery.sizes.min.js',
        'vendor/jquery/jquery.tinyscrollbar.js',
        'vendor/jquery/jquery.hotkeys.js',
        'vendor/jquery/jquery.textarea-expander.js',
        'vendor/jquery/jquery.serializeJSON.min.js',
        'vendor/jquery/jquery.dotdotdot.min.js',
        'vendor/jqTree/tree.jquery.js',
        'vendor/jquery/jquery-checkbox/jquery.checkbox.js',
        'vendor/redactor/redactor.js',
        'vendor/jquery/fileupload/jquery.fileupload.js',
        'vendor/jquery/fileupload/jquery.fileupload-ui.js',
        'vendor/jquery/fileupload/jquery.iframe-transport.js',
        'vendor/jquery/qtip/jquery.qtip.min.js',
        'javascripts/DeskPRO/tinycon.js',
        'vendor/select2/select2.js',
        'bower_components/moment/min/moment-with-locales.min.js',
        'bower_components/moment-timezone/builds/moment-timezone-with-data.min.js',
        'bower_components/notify.js/dist/notify.js',
        'bower_components/intl-tel-input/build/js/intlTelInput.min.js',
        'bower_components/jquery-qrcode/dist/jquery.qrcode.js',
        'vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        'bower_components/kbw-calendars/dist/js/jquery.calendars.js',
        'bower_components/kbw-calendars/dist/js/jquery.plugin.js',
        'bower_components/kbw-calendars/dist/js/jquery.calendars.plus.js',
        'bower_components/kbw-calendars/dist/js/jquery.calendars.picker.js',
        'bower_components/kbw-calendars/dist/js/jquery.calendars.picker-ar.js',
        'bower_components/kbw-calendars/dist/js/jquery.calendars.islamic.js',
        'bower_components/kbw-calendars/dist/js/jquery.calendars.islamic-ar.js',
        'node_modules/jquery-colorbox/jquery.colorbox-min.js',
        'node_modules/css-element-queries/src/ResizeSensor.js',
    ],
];

$CONFIG['agent_settingswin'] = [
    'out'   => 'js/agent-settingswin.js',
    'files' => [
        'javascripts/DeskPRO/Agent/ElementHandler/SettingsWindow.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/Profile.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/Signature.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/TicketNotifications.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/OtherNotifications.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/Macros.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/MacroEdit.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/Filters.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/FilterEdit.js',
        'javascripts/DeskPRO/Agent/PageFragment/SettingsPage/TicketSlas.js',
        'javascripts/DeskPRO/Agent/ElementHandler/MediaManagerWindow.js',
        'javascripts/DeskPRO/Agent/PageFragment/MediaManagerPage/Upload.js',
        'javascripts/DeskPRO/Agent/PageFragment/MediaManagerPage/Browse.js',
    ],
];

$CONFIG['agent_window_sections'] = [
    'out'   => 'js/agent-window-sections.js',
    'files' => [
        'javascripts/DeskPRO/Agent/WindowElement/Section/AbstractSection.js',
        'javascripts/DeskPRO/Agent/WindowElement/Section/Tickets.js',
        'javascripts/DeskPRO/Agent/WindowElement/Section/People.js',
        'javascripts/DeskPRO/Agent/WindowElement/Section/Publish.js',
        'javascripts/DeskPRO/Agent/WindowElement/Section/AgentChat.js',
        'javascripts/DeskPRO/Agent/WindowElement/Section/UserChat.js',
        'javascripts/DeskPRO/Agent/WindowElement/Section/Feedback.js',
        'javascripts/DeskPRO/Agent/WindowElement/Section/Tasks.js',
    ],
];

$CONFIG['agent_pages_lists'] = [
    'out'   => 'js/agent-pages-lists.js',
    'files' => [
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/Basic.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/OrganizationList.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/PeopleList.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/RecycleBin.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/KbPendingArticles.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/KbValidatingArticles.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/KbList.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/AgentChatHistory.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/AgentTeamChatHistory.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/OpenChats.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/UserChatFilter.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/FeedbackFilter.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/GuideList.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/NewCustomFilter.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/NewsList.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/DownloadList.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishListComments.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishValidatingComments.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishDraftsList.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishSearch.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/FeedbackSearch.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishSearchLog.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/FeedbackCommentsValidating.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/FeedbackContentValidating.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/TaskList.js',
        'javascripts/DeskPRO/Agent/PageFragment/ListPane/Search.js',
        'javascripts/DeskPRO/Agent/PageFragment/List/TicketList.js',
        'javascripts/DeskPRO/Agent/PageFragment/List/Helper/TicketMassActions.js',
    ],
];

$CONFIG['agent_pages'] = [
    'out'   => 'js/agent-pages.js',
    'files' => [
        'javascripts/DeskPRO/Agent/PageHelper/TicketFields.js',
        'javascripts/DeskPRO/Agent/PageHelper/TicketFieldDisplay.js',
        'javascripts/DeskPRO/Agent/PageHelper/ChatFields.js',
        'javascripts/DeskPRO/Agent/PageHelper/ChatFieldDisplay.js',
        'javascripts/DeskPRO/Agent/PageHelper/CategoryEdit.js',
        'javascripts/DeskPRO/Agent/PageHelper/DisplayOptions.js',
        'javascripts/DeskPRO/Agent/PageHelper/SelectionBar.js',
        'javascripts/DeskPRO/Agent/PageHelper/Popover.js',
        'javascripts/DeskPRO/Agent/PageHelper/FragmentOverlay.js',
        'javascripts/DeskPRO/Agent/PageHelper/ValidatingEdit.js',
        'javascripts/DeskPRO/Agent/PageHelper/RelatedContent.js',
        'javascripts/DeskPRO/Agent/PageHelper/RelatedContentList.js',
        'javascripts/DeskPRO/Agent/PageHelper/Comments.js',
        'javascripts/DeskPRO/Agent/PageHelper/CustomFieldUpload.js',
        'javascripts/DeskPRO/Agent/PageHelper/MiscContent.js',
        'javascripts/DeskPRO/Agent/PageHelper/ListNav.js',
        'javascripts/DeskPRO/Agent/PageHelper/StateSaver.js',
        'javascripts/DeskPRO/Agent/PageHelper/Results.js',
        'javascripts/DeskPRO/Agent/PageHelper/MassActions.js',
        'javascripts/DeskPRO/Agent/PageHelper/AcceptContentLink.js',
        'javascripts/DeskPRO/Agent/PageHelper/SendContentLink.js',
        'javascripts/DeskPRO/Agent/PageHelper/EditTitle.js',
        'javascripts/DeskPRO/Agent/PageHelper/TaskListControl.js',
        'javascripts/DeskPRO/Agent/PageHelper/TicketBilling.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/AgentChatTranscript.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Content/DeleteControl.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Content/StickyWords.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/DownloadsView.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/FeedbackView.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/KbViewArticle.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/TopicView.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewArticle.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewPerson.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewOrganization.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewDownload.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewFeedback.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewNews.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewsView.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewTask.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewTicket.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/NewTopic.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Organization.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Person.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/PersonHelper/ChangePic.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/PersonHelper/UploadVcard.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/PersonHelper/UploadFile.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/PersonHelper/ContactEditor.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/PersonPopout.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/PersonSession.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/PublishNewCat.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/SnippetViewer.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket/TicketLocked.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket/TicketActions.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/TicketHelper/LinkTicket.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/TicketHelper/LinkFeedback.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/UserChat.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Visitor.js',
        'javascripts/DeskPRO/Agent/PageFragment/Page/Test.js',
    ],
];

$CONFIG['agent_element_handlers'] = [
    'out'   => 'js/agent-element-handlers.js',
    'files' => [
        'javascripts/DeskPRO/Agent/ElementHandler/FormSaver.js',
        'javascripts/DeskPRO/Agent/ElementHandler/TicketReplyBox.js',
        'javascripts/DeskPRO/Agent/ElementHandler/TicketCcManage.js',
        'javascripts/DeskPRO/Agent/ElementHandler/SimpleAutoComplete.js',
        'javascripts/DeskPRO/Agent/ElementHandler/TabBox.js',
        'javascripts/DeskPRO/Agent/ElementHandler/PersonSearchBox.js',
        'javascripts/DeskPRO/Agent/ElementHandler/OrgSearchBox.js',
        'javascripts/DeskPRO/Agent/ElementHandler/TicketSearchBox.js',
        'javascripts/DeskPRO/Agent/ElementHandler/FeedbackSearchBox.js',
        'javascripts/DeskPRO/Agent/ElementHandler/PhoneCountryCode.js',
        'javascripts/DeskPRO/Agent/ElementHandler/PasswordPrompt.js',
        'javascripts/DeskPRO/Agent/ElementHandler/OverlayFrame.js',
        'javascripts/DeskPRO/Agent/ElementHandler/GoToBilling.js',
        'javascripts/DeskPRO/Agent/ElementHandler/TimezoneSwitch.js',
        'javascripts/DeskPRO/Agent/ElementHandler/RadioExpander.js',
        'javascripts/DeskPRO/Agent/ElementHandler/FirstLogin.js',
        'javascripts/DeskPRO/Agent/SourcePane/SearchForm.js',
    ],
];

$CONFIG['agent_common'] = [
    'out'   => 'js/agent-common.js',
    'files' => [
        'javascripts/DeskPRO/Agent/TouchClicker.js',
        'javascripts/DeskPRO/DP.js',
        'javascripts/Orb/Orb.js',
        'javascripts/Orb/Class.js',
        'javascripts/Orb/Util/Options.js',
        'javascripts/Orb/Util/Events.js',
        'javascripts/Orb/Util/TimeAgo.js',
        'javascripts/Orb/Util/CallQueue.js',
        'javascripts/Orb/Compat.js',
        'javascripts/DeskPRO/ElementHandler.js',
        'javascripts/DeskPRO/ElementHandler/SimpleTabs.js',
        'javascripts/DeskPRO/MessageBroker.js',
        'javascripts/DeskPRO/TouchCaller.js',
        'javascripts/DeskPRO/WordHighlighter.js',
        'javascripts/DeskPRO/AjaxPoller/Poller.js',
        'javascripts/DeskPRO/AjaxPoller/MessagePoller.js',
        'javascripts/DeskPRO/MessageChanneler/AbstractChanneler.js',
        'javascripts/DeskPRO/MessageChanneler/AjaxChanneler.js',
        'javascripts/DeskPRO/Translate.js',
        'javascripts/DeskPRO/Agent/RteEditor.js',
        'javascripts/DeskPRO/TextExpander.js',
    ],
];

$CONFIG['agent_agent_ui'] = [
    'out'   => 'js/agent-ui.js',
    'files' => [
        'javascripts/DeskPRO/BasicWindow.js',
        'javascripts/DeskPRO/Agent/Window.js',
        'javascripts/DeskPRO/Agent/Layout/DeskproWindow.js',
        'javascripts/DeskPRO/Agent/TabWatcher.js',
        'javascripts/DeskPRO/Agent/ScrollerHandler.js',
        'javascripts/DeskPRO/Agent/KeyboardShortcuts.js',
        'javascripts/DeskPRO/Agent/Notifications.js',
        'javascripts/DeskPRO/Agent/RecentTabs.js',
        'javascripts/DeskPRO/Agent/PageFragment/Basic.js',
        'javascripts/DeskPRO/Agent/PageFragment/Loading.js',
        'javascripts/DeskPRO/Agent/WindowElement/TabWatcher/Tickets.js',
        'javascripts/DeskPRO/Agent/WindowElement/TabWatcher/UserChat.js',
        'javascripts/DeskPRO/Agent/WindowElement/TabBar.js',
        'javascripts/DeskPRO/Agent/WindowElement/TabBarOverflow.js',
        'javascripts/DeskPRO/Agent/TextSnippetAjaxDriver.js',
    ],
];

$CONFIG['agent_deskpro_ui'] = [
    'out'   => 'js/agent-deskpro-ui.js',
    'files' => [
        'javascripts/DeskPRO/UI/LabelsInput.js',
        'javascripts/DeskPRO/UI/Overlay.js',
        'javascripts/DeskPRO/UI/PhoneNumberInputs.js',
        'javascripts/DeskPRO/UI/OptionBox.js',
        'javascripts/DeskPRO/UI/OptionBoxRevertable.js',
        'javascripts/DeskPRO/UI/OptionBoxBuilder.js',
        'javascripts/DeskPRO/UI/Menu.js',
        'javascripts/DeskPRO/UI/Menu2.js',
        'javascripts/DeskPRO/UI/SimpleTabs.js',
        'javascripts/DeskPRO/UI/Select/Widget.js',
        'javascripts/DeskPRO/UI/Select/Menu.js',
        'javascripts/DeskPRO/UI/Select/WidgetSimple.js',
        'javascripts/DeskPRO/UI/Select/MenuHtml.js',
    ],
];

$CONFIG['agent_misc'] = [
    'out'   => 'js/agent-misc.js',
    'files' => [
        'javascripts/DeskPRO/Form/InlineEdit.js',
        'javascripts/DeskPRO/Form/RuleBuilder.js',
        'javascripts/DeskPRO/FaviconBadge.js',
        'javascripts/DeskPRO/Agent/Widget/SnippetViewer.js',
        'javascripts/DeskPRO/Agent/Widget/TicketChangeUser.js',
        'javascripts/DeskPRO/Agent/Widget/Merge.js',
        'javascripts/DeskPRO/Agent/Widget/AgentChatWin.js',
        'javascripts/DeskPRO/Agent/Widget/BackgroundPopout.js',
        'javascripts/DeskPRO/Agent/RuleBuilder/TermAbstract.js',
        'javascripts/DeskPRO/Agent/RuleBuilder/DateTerm.js',
        'javascripts/DeskPRO/Agent/RuleBuilder/DateTimeTerm.js',
        'javascripts/DeskPRO/Agent/RuleBuilder/LabelsTerm.js',
        'javascripts/DeskPRO/Agent/RuleBuilder/TicketFeedbackLinksTerm.js',
        'javascripts/DeskPRO/Agent/RuleBuilder/SelectNewOption.js',
        'javascripts/DeskPRO/Agent/Ticket/ChangeManager.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Abstract.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Agent.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Department.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/AgentTeam.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/StandardOption.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Status.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Reply.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/TicketField.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Urgency.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Flag.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Labels.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Hold.js',
        'javascripts/DeskPRO/Agent/Ticket/Property/Problem.js',

        'javascripts/DeskPRO/Agent/TicketList/ListView.js',
        'javascripts/DeskPRO/Agent/TicketList/ChangeManager.js',
        'javascripts/DeskPRO/Agent/TicketList/Property/Abstract.js',
        'javascripts/DeskPRO/Agent/TicketList/Property/StandardOption.js',
        'javascripts/DeskPRO/Agent/TicketList/Property/NewReply.js',
        'javascripts/DeskPRO/Agent/TicketList/Property/TicketField.js',
        'javascripts/DeskPRO/Agent/TicketList/Property/Flag.js',
        'javascripts/DeskPRO/Agent/TicketList/Property/Labels.js',
        'javascripts/DeskPRO/UI/MultiLevelSelect.js',
    ],
];

//##############################################################################
// CSS
//##############################################################################

$CONFIG['agent_css1'] = [
    'out'          => 'css/agent-pack1.css',
    'post_filters' => ['smartsprites', 'css', 'image_gradients'],
    'references'   => [
        'agent_interface_css1',
    ],
];

$CONFIG['agent_css2'] = [
    'out'          => 'css/agent-pack2.css',
    'post_filters' => ['smartsprites', 'css', 'image_gradients'],
    'references'   => [
        'agent_interface_css2',
    ],
];

$CONFIG['agent_vendor_out_css'] = [
    'out'          => 'css/agent-vendors-all.css',
    'post_filters' => ['css'],
    'references'   => [
        'agent_vendors_css',
    ],
];

$CONFIG['agent_interface_css1'] = [
    'out'     => 'css/agent-interface1.css',
    'filters' => ['less'],
    'files'   => [
        'stylesheets-less/agent/dp-interface.less',
        'stylesheets-less/agent/dp-agent-chat.less',
        'stylesheets-less/agent/overlayCreateTicket.less',
        'stylesheets-less/agent/dp-source-pane.less',
        'stylesheets-less/agent/dp-list-pane.less',
    ],
];

$CONFIG['agent_interface_css2'] = [
    'out'     => 'css/agent-interface2.css',
    'filters' => ['less'],
    'files'   => [
        'stylesheets-less/agent/dp-content-pane.less',
        'stylesheets-less/agent/agent.less',
    ],
];

$CONFIG['reports_interface_css1'] = [
    'out'     => 'css/reports-interface1.css',
    'filters' => ['less'],
    'files'   => [
        'stylesheets-less/agent/agent.less',
        'stylesheets-less/agent/dp-source-pane.less',
    ],
];

$CONFIG['agent_interface_ie_css'] = [
    'out'     => 'css/agent-interface-ie.css',
    'filters' => ['less'],
    'files'   => [
        'stylesheets-less/agent/agent-ie.less',
    ],
];

$CONFIG['agent_interface_print_css'] = [
    'out'     => 'css/agent-interface-print.css',
    'filters' => ['less'],
    'media'   => 'print',
    'files'   => [
        'stylesheets-less/agent/print.less',
    ],
];

$CONFIG['agent_vendors_css'] = [
    'out'     => 'css/agent-vendors.css',
    'filters' => ['css_path'],
    'files'   => [
        'stylesheets/vendor/jquery-ui/dp-theme/jquery-ui.css',
        'vendor/jquery/qtip/jquery.qtip.min.css',
        'vendor/select2/select2.css',
        'vendor/redactor/redactor.css',
        'vendor/bootstrap/css/common.css',
        'vendor/bootstrap/css/modal.css',
        'vendor/bootstrap/css/dropdown.css',
        'vendor/bootstrap/css/table.css',
        'vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
        'vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-patch.css',
        'stylesheets/user/content-editor.css',
        'bower_components/kbw-calendars/dist/css/smoothness.calendars.picker.css',
    ],
];
