<?php

$CONFIG = array();

###############################################################################
# Agent
###############################################################################

$CONFIG['agent'] = array();

$CONFIG['agent']['window-sections'] = array(
	'mode' => 'yui',
	'out' => 'agent-window-sections.js',
	'files' => array(
		'javascripts/DeskPRO/Agent/WindowElement/Section/AbstractSection.js',
		'javascripts/DeskPRO/Agent/WindowElement/Section/Tickets.js',
		'javascripts/DeskPRO/Agent/WindowElement/Section/People.js',
		'javascripts/DeskPRO/Agent/WindowElement/Section/Publish.js',
		'javascripts/DeskPRO/Agent/WindowElement/Section/AgentChat.js',
		'javascripts/DeskPRO/Agent/WindowElement/Section/UserChat.js',
		'javascripts/DeskPRO/Agent/WindowElement/Section/Ideas.js',
                'javascripts/DeskPRO/Agent/WindowElement/Section/Task.js',
		'javascripts/DeskPRO/Agent/WindowElement/Section/Test.js',
	)
);

$CONFIG['agent']['pages-lists'] = array(
	'mode' => 'yui',
	'out' => 'agent-pages-lists.js',
	'files' => array(
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/Basic.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/BasicTicketResults.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/BasicOrganizationResults.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/OrganizationCustomFilter.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/PeopleList.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/TicketFilter.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/TicketFlagged.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/TicketDeletedList.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/TicketCustomFilter.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/TicketCustomFilterForm.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/TwitterStatus.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/RecycleBin.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/KbGlossary.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/KbPendingArticles.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/KbValidatingArticles.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/KbList.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/AgentChatHistory.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/OpenChats.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/IdeaFilter.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/NewCustomFilter.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/NewsList.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/DownloadList.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishValidatingComments.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishValidatingContent.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishDraftsList.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/PublishSearchLog.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/IdeaCommentsValidating.js',
		'javascripts/DeskPRO/Agent/PageFragment/ListPane/IdeaContentValidating.js',
                'javascripts/DeskPRO/Agent/PageFragment/ListPane/TaskList.js',
	)
);

$CONFIG['agent']['pages'] = array(
	'mode' => 'yui',
	'out' => 'agent-pages.js',
	'files' => array(
		'javascripts/DeskPRO/Agent/PageHelper/TicketActionsBar.js',
		'javascripts/DeskPRO/Agent/PageHelper/TicketMassActions.js',
		'javascripts/DeskPRO/Agent/PageHelper/NewUserOverlay.js',
		'javascripts/DeskPRO/Agent/PageHelper/ListColDrag.js',
		'javascripts/DeskPRO/Agent/PageHelper/ListColResize.js',
		'javascripts/DeskPRO/Agent/PageHelper/TicketDisplay.js',
		'javascripts/DeskPRO/Agent/PageHelper/ListSearchForm.js',
		'javascripts/DeskPRO/Agent/PageHelper/CategoryEdit.js',
		'javascripts/DeskPRO/Agent/PageHelper/DisplayOptions.js',
		'javascripts/DeskPRO/Agent/PageHelper/SelectionBar.js',
		'javascripts/DeskPRO/Agent/PageHelper/Popover.js',
		'javascripts/DeskPRO/Agent/PageHelper/ValidatingEdit.js',
		'javascripts/DeskPRO/Agent/PageHelper/RelatedContent.js',
		'javascripts/DeskPRO/Agent/PageHelper/RelatedContentList.js',
		'javascripts/DeskPRO/Agent/PageHelper/Comments.js',
		'javascripts/DeskPRO/Agent/PageHelper/MiscContent.js',
		'javascripts/DeskPRO/Agent/PageHelper/AutoSave.js',
		'javascripts/DeskPRO/Agent/PageHelper/StateSaver.js',
		'javascripts/DeskPRO/Agent/PageHelper/Results.js',

		'javascripts/DeskPRO/Agent/PageFragment/Page/SnippetViewer.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket/ReplyBox.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket/TicketLocked.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket/TicketChecker.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket/TicketActions.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket/Participants.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Ticket/TicketFields.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/PersonHelper/ChangePic.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/PersonHelper/ContactEditor.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Content/DeleteControl.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Content/StickyWords.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewArticle.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewPerson.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewOrganization.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewDownload.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewNews.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewTicket.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewIdea.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Organization.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/Person.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/PersonPopout.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/TwitterUser.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/KbViewArticle.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/AgentChatTranscript.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/UserChat.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/IdeaView.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewsView.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/DownloadsView.js',
		'javascripts/DeskPRO/Agent/PageFragment/Page/NewTask.js',
                'javascripts/DeskPRO/Agent/PageFragment/Page/Test.js',
	)
);

$CONFIG['agent']['element-handlers'] = array(
	'mode' => 'yui',
	'out' => 'agent-element-handlers.js',
	'files' => array(
		'javascripts/DeskPRO/Agent/ElementHandler/TwitterFeed.js',
		'javascripts/DeskPRO/Agent/ElementHandler/FormSaver.js',
		'javascripts/DeskPRO/Agent/ElementHandler/TicketReplyBox.js',
		'javascripts/DeskPRO/Agent/ElementHandler/TabBox.js',
		'javascripts/DeskPRO/Agent/ElementHandler/PersonSearchBox.js',
	)
);

$CONFIG['agent']['common'] = array(
	'mode' => 'yui',
	'out' => 'agent-common.js',
	'files' => array(
		'javascripts/Orb/modernizr-ext.js',
		'javascripts/Orb/Orb.js',
		'javascripts/Orb/Class.js',
		'javascripts/Orb/Util/Options.js',
		'javascripts/Orb/Util/Events.js',
		'javascripts/Orb/Util/EventObj.js',
		'javascripts/Orb/Util/TimeAgo.js',
		'javascripts/Orb/Compat.js',
		'javascripts/DeskPRO/ElementHandler.js',
		'javascripts/DeskPRO/ElementHandler/ListRadio.js',
		'javascripts/DeskPRO/MessageBroker.js',
		'javascripts/DeskPRO/IntervalCaller.js',
		'javascripts/DeskPRO/TouchCaller.js',
		'javascripts/DeskPRO/AjaxPoller/Poller.js',
		'javascripts/DeskPRO/AjaxPoller/MessagePoller.js',
		'javascripts/DeskPRO/MessageChanneler/AbstractChanneler.js',
		'javascripts/DeskPRO/MessageChanneler/AjaxChanneler.js',
	)
);

$CONFIG['agent']['agent-ui'] = array(
	'mode' => 'yui',
	'out' => 'agent-ui.js',
	'files' => array(
		'javascripts/DeskPRO/BasicWindow.js',
		'javascripts/DeskPRO/Agent/Window.js',
		'javascripts/DeskPRO/Agent/Layout/DeskproWindow.js',
		'javascripts/DeskPRO/Agent/Layout/WindowLayout.js',
		'javascripts/DeskPRO/Agent/Layout/FooterLayout.js',
		'javascripts/DeskPRO/Agent/Layout/FooterActionbarLayout.js',
		'javascripts/DeskPRO/Agent/TabManager.js',
		'javascripts/DeskPRO/Agent/TabStrip.js',
		'javascripts/DeskPRO/Agent/TabWatcher.js',
		'javascripts/DeskPRO/Agent/ScrollerHandler.js',
		'javascripts/DeskPRO/Agent/KeyboardShortcuts.js',

		'javascripts/DeskPRO/Agent/PageFragment/Basic.js',
		'javascripts/DeskPRO/Agent/PageFragment/Loading.js',

		'javascripts/DeskPRO/Agent/Notifier/Notifier.js',
		'javascripts/DeskPRO/Agent/Notifier/Types/Abstract.js',
		'javascripts/DeskPRO/Agent/Notifier/Types/Ticket.js',

		'javascripts/DeskPRO/Agent/WindowElement/TabWatcher/Tickets.js',

		'javascripts/DeskPRO/Agent/WindowElement/MainMenuOpener.js',
		'javascripts/DeskPRO/Agent/WindowElement/MainMenu/Abstract.js',
		'javascripts/DeskPRO/Agent/WindowElement/MainMenu/Notifications.js',
		'javascripts/DeskPRO/Agent/WindowElement/MainMenu/SearchBoxResults.js',
		'javascripts/DeskPRO/Agent/WindowElement/MainMenu/SearchBoxType.js',

		// Omnisearch
		'javascripts/DeskPRO/UI/OmniSearch/SearchBox.js',
		'javascripts/DeskPRO/Agent/OmniSearchBox.js',
		'javascripts/DeskPRO/UI/OmniSearch/Context/ContextAbstract.js',
		'javascripts/DeskPRO/UI/OmniSearch/Context/TicketsContext.js',
		'javascripts/DeskPRO/UI/OmniSearch/Term/TermAbstract.js',
		'javascripts/DeskPRO/UI/OmniSearch/Term/GenericInputTerm.js',
		'javascripts/DeskPRO/UI/OmniSearch/Term/GenericMenuTerm.js',
		'javascripts/DeskPRO/UI/OmniSearch/Term/GenericDateTerm.js',
	)
);

$CONFIG['agent']['deskpro-ui'] = array(
	'mode' => 'yui',
	'out' => 'agent-deskpro-ui.js',
	'files' => array(
		'javascripts/DeskPRO/UI/LabelsInput.js',
		'javascripts/DeskPRO/UI/Overlay.js',
		'javascripts/DeskPRO/UI/OptionBox.js',
		'javascripts/DeskPRO/UI/Menu.js',
		'javascripts/DeskPRO/UI/SimpleTabs.js',
		'javascripts/DeskPRO/UI/DateChooser.js',
		'javascripts/DeskPRO/UI/CatListEditor.js',
	)
);

$CONFIG['agent']['misc'] = array(
	'mode' => 'yui',
	'out' => 'agent-misc.js',
	'files' => array(
		'javascripts/DeskPRO/Form/InlineEdit.js',
		'javascripts/DeskPRO/Form/RuleBuilder.js',
		'javascripts/DeskPRO/FaviconBadge.js',
		'javascripts/DeskPRO/Agent/MediaBrowser.js',
		'javascripts/DeskPRO/Agent/InterfaceEffects.js',

		'javascripts/DeskPRO/Agent/Widget/FindPerson.js',
		'javascripts/DeskPRO/Agent/Widget/AgentSelector.js',
		'javascripts/DeskPRO/Agent/Widget/SnippetViewer.js',
		'javascripts/DeskPRO/Agent/Widget/MergeTicket.js',
		'javascripts/DeskPRO/Agent/Widget/MergeIdea.js',
		'javascripts/DeskPRO/Agent/Widget/AgentChatWin.js',
		'javascripts/DeskPRO/Agent/Widget/FilterGroupEditor.js',
		'javascripts/DeskPRO/Agent/Widget/FilterOptionsPop.js',

		'javascripts/DeskPRO/Agent/Widget/BackgroundPopout.js',

		'javascripts/DeskPRO/Agent/RuleBuilder/TermAbstract.js',
		'javascripts/DeskPRO/Agent/RuleBuilder/DateTerm.js',
		'javascripts/DeskPRO/Agent/RuleBuilder/LabelsTerm.js',

		'javascripts/DeskPRO/Agent/Ticket/ChangeManager.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/Abstract.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/Agent.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/Department.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/AgentTeam.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/StandardOption.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/Status.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/Reply.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/TicketField.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/Flag.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/Labels.js',
		'javascripts/DeskPRO/Agent/Ticket/Property/Hold.js',

		'javascripts/DeskPRO/Agent/TicketList/MassActions/Widget.js',

		'javascripts/DeskPRO/Agent/TicketList/ChangeManager.js',
		'javascripts/DeskPRO/Agent/TicketList/Property/Abstract.js',
		'javascripts/DeskPRO/Agent/TicketList/Property/StandardOption.js',
		'javascripts/DeskPRO/Agent/TicketList/Property/NewReply.js',
		'javascripts/DeskPRO/Agent/TicketList/Property/TicketField.js',
		'javascripts/DeskPRO/Agent/TicketList/Property/Flag.js',
		'javascripts/DeskPRO/Agent/TicketList/Property/Labels.js',
	)
);

/**
 * Vendor files for agent interface
 */
$CONFIG['agent']['vendors'] = array(
	'mode' => 'yui',
	'out' => 'agent-vendors.js',
	'files' => array(
		'vendor/jquery/jquery.min.js',
		'vendor/jquery/jquery-ui/jquery-ui.min.js',
		'vendor/jquery/jquery-tmpl/jquery.tmpl.min.js',

		'vendor/jquery/jquery.cookie.js',
		'vendor/jquery/jquery.history.js',
		'vendor/jquery/jquery.form.js',
		'vendor/jquery/jquery.form.js',
		'vendor/jquery/jquery.layout.min.js',
		'vendor/jquery/jquery.localscroll.js',
		'vendor/jquery/jquery.mousewheel.js',
		'vendor/jquery/jquery.scrollTo.js',
		'vendor/jquery/jquery.sizes.min.js',
		'vendor/jquery/jquery.tinyscrollbar.js',
		'vendor/jquery/jquery.hotkeys.js',
		'vendor/jquery/mwheelIntent.js',
		'vendor/jquery/jquery.textarea-expander.js',
		//'vendor/jquery/jquery.ajax-retry.js',

		'vendor/jquery/jquery-checkbox/jquery.checkbox.min.js',
		'vendor/jquery/token-field/jquery.token-field.js',

		'vendor/tiny_mce/jquery.tinymce.js',

		'vendor/jquery/colorbox/jquery.colorbox-min.js',

		'vendor/jquery/fileupload/jquery.fileupload.js',
		'vendor/jquery/fileupload/jquery.fileupload-ui.js',

		'vendor/jquery/jcrop/js/jquery.Jcrop.min.js',

		'vendor/jquery/tag-it/tag-it.js',

		'vendor/jquery/tipped/js/excanvas/excanvas.js',
		'vendor/jquery/tipped/js/spinners/spinners.js',
		'vendor/jquery/tipped/js/tipped/tipped.js',

		'vendor/mootools/mootools-core.min.js',
		'vendor/modernizr.min.js',
	)
);


###############################################################################
# Admin
###############################################################################

/**
 * Admin UI specific
 */
$CONFIG['admin']['admin-ui'] = array(
	'mode' => 'yui',
	'out' => 'admin-ui.js',
	'files' => array(
		'javascripts/DeskPRO/Admin/Window.js',
		'javascripts/DeskPRO/Admin/PopoutWindow.js',
		'javascripts/DeskPRO/Admin/PageHandler/Basic.js',
		'javascripts/DeskPRO/Admin/TableReorder.js',
	)
);

/**
 * Admin UI specific
 */
$CONFIG['admin']['admin-handlers'] = array(
	'mode' => 'yui',
	'out' => 'admin-handlers.js',
	'files' => array(
		'javascripts/DeskPRO/Admin/Departments/AjaxSave.js',
		'javascripts/DeskPRO/Admin/Departments/AgentSelector.js',
		'javascripts/DeskPRO/Admin/Departments/UsergroupSelector.js',
		'javascripts/DeskPRO/Admin/ElementHandler/TicketPropertiesList.js',
	)
);


###############################################################################
# User
###############################################################################

$CONFIG['user'] = array();

$CONFIG['user']['common'] = array(
	'mode' => 'yui',
	'out' => 'user-common.js',
	'files' => array(
		'javascripts/Orb/modernizr-ext.js',
		'javascripts/Orb/Orb.js',
		'javascripts/Orb/Class.js',
		'javascripts/Orb/Util/Options.js',
		'javascripts/Orb/Util/Events.js',
		'javascripts/Orb/Util/TimeAgo.js',
		'javascripts/Orb/Compat.js',
		'javascripts/DeskPRO/IntervalCaller.js',
		'javascripts/DeskPRO/MessageBroker.js',
		'javascripts/DeskPRO/BasicWindow.js',
		'javascripts/DeskPRO/UI/SimpleTabs.js',
		'javascripts/DeskPRO/UI/Overlay.js',
		'javascripts/DeskPRO/User/Window.js',

		'javascripts/DeskPRO/User/ElementHandler/ElementHandlerAbstract.js',
		'javascripts/DeskPRO/User/ElementHandler/MoreLoader.js',
		'javascripts/DeskPRO/User/ElementHandler/Helper/IdeaVote.js',
		'javascripts/DeskPRO/User/ElementHandler/LoginBox.js',
		'javascripts/DeskPRO/User/ElementHandler/NewTicket.js',
		'javascripts/DeskPRO/User/ElementHandler/FormUploadHandler.js',
		'javascripts/DeskPRO/User/ElementHandler/OmniSearch.js',
		'javascripts/DeskPRO/User/ElementHandler/TicketList.js',
		'javascripts/DeskPRO/User/ElementHandler/TicketView.js',
		'javascripts/DeskPRO/User/ElementHandler/InlineEmailManage.js',
		'javascripts/DeskPRO/User/ElementHandler/CommentFormLogin.js',

		'javascripts/DeskPRO/User/SuggestedContentOverlay.js',
		'javascripts/DeskPRO/User/InlineSuggestions.js',
		'javascripts/DeskPRO/User/InlineLoginForm.js',

		'javascripts/DeskPRO/FormValidator/FormValidator.js',
		'javascripts/DeskPRO/FormValidator/FieldValidator.js',
		'javascripts/DeskPRO/FormValidator/LengthValidator.js',
		'javascripts/DeskPRO/FormValidator/EmailValidator.js',
		'javascripts/DeskPRO/FormValidator/TwoLevelSelectValidator.js',
	)
);

$CONFIG['user']['vendors'] = array(
	'mode' => 'yui',
	'out' => 'user-vendors.js',
	'files' => array(
		'vendor/jquery/jquery.min.js',
		'vendor/jquery/jquery.cookie.js',
		'vendor/jquery/jquery.history.js',
		'vendor/jquery/jquery.form.js',
		'vendor/jquery/jquery-ui/jquery-ui.min.js',
		'vendor/jquery/jquery-tmpl/jquery.tmpl.min.js',

		'vendor/jquery/fileupload/jquery.fileupload.js',
		'vendor/jquery/fileupload/jquery.fileupload-ui.js',

		'vendor/mootools/mootools-core.min.js',
		'vendor/modernizr.min.js',
	)
);
