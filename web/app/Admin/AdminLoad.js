define([
	'angular',
	'angularAnimate',
	'angularSanitize',
	'angularBootstrap',
	'angularSelect2',
	'angularUiAce',
	'angularUiRouter',
	'angularUiSortable',
	'angularMoment',
	'angularFileUpload',
	'angularSlider',
	'angularSelectize',
	'angularGrid',
	'ngFileUpload',

	'moment',
	'momentTimezone',
	'aceEditor',

	'jquery',
	'jqueryUi',
	'underscore',
	'stacktrace',

	'microplugin',
	'sifter',
	'selectize',

	'ZeroClipboard',
	'ngClip',

	'bootstrapTooltip',

	'select2',
	'toastr',

	'DeskPRO/OptionBuilder/Module',
	'DeskPRO/CategoryBuilder/Module',

	'Admin/App/App',

	'Admin/Apps/Ctrl/List',
	'Admin/Apps/Ctrl/EditInstance',
	'Admin/Apps/Ctrl/EditCustomInstance',
	'Admin/Apps/Ctrl/PackageInfo',
	'Admin/Apps/Ctrl/PackageInstall',
	'Admin/Apps/Ctrl/Resync',
	'Admin/AuditLog/Ctrl/List',
	'Admin/AuditLog/Ctrl/View',
	'Admin/Agents/Ctrl/Edit',
	'Admin/Agents/Ctrl/EditProfile',
	'Admin/Agents/Ctrl/DeletedList',
	'Admin/Agents/Ctrl/DeletedRestore',
	'Admin/Agents/Ctrl/List',
	'Admin/Agents/Ctrl/Logs',
	'Admin/Agents/Ctrl/Import',
	'Admin/AgentGroups/Ctrl/Edit',
	'Admin/AgentGroups/Ctrl/List',
	'Admin/AgentTeams/Ctrl/Edit',
	'Admin/AgentTeams/Ctrl/List',
	'Admin/EmailStatus/Ctrl/SendmailList',
	'Admin/EmailStatus/Ctrl/SourceList',
	'Admin/EmailStatus/Ctrl/ViewSend',
	'Admin/EmailStatus/Ctrl/ViewSource',
	'Admin/Main/Ctrl/Bare',
	'Admin/Main/Ctrl/BareList',
	'Admin/Main/Ctrl/MainBody',
	'Admin/Main/Ctrl/MainPage',
	'Admin/Main/Ctrl/Home',
	'Admin/Main/Ctrl/BackToAgent',
	'Admin/Main/Ctrl/GoToReports',
	'Admin/Main/Ctrl/GoToUser',
	'Admin/Main/Ctrl/Nav',
	'Admin/CustomFields/Tickets/Ctrl/Edit',
	'Admin/CustomFields/Chat/Ctrl/Edit',
	'Admin/CustomFields/User/Ctrl/Edit',
	'Admin/CustomFields/Org/Ctrl/Edit',
	'Admin/Labels/Base/Ctrl/List',
	'Admin/Labels/Base/Ctrl/Edit',
	'Admin/Labels/Person/Ctrl/List',
	'Admin/Labels/Person/Ctrl/Edit',
	'Admin/Labels/Org/Ctrl/List',
	'Admin/Labels/Org/Ctrl/Edit',
	'Admin/Labels/Ticket/Ctrl/List',
	'Admin/Labels/Ticket/Ctrl/Edit',
	'Admin/Labels/Feedback/Ctrl/List',
	'Admin/Labels/Feedback/Ctrl/Edit',
	'Admin/Labels/Chat/Ctrl/List',
	'Admin/Labels/Chat/Ctrl/Edit',
	'Admin/Labels/Kb/Ctrl/List',
	'Admin/Labels/Kb/Ctrl/Edit',
	'Admin/Labels/News/Ctrl/List',
	'Admin/Labels/News/Ctrl/Edit',
	'Admin/Labels/Downloads/Ctrl/List',
	'Admin/Labels/Downloads/Ctrl/Edit',
	'Admin/Labels/Base/Ctrl/Settings',
	'Admin/Languages/Ctrl/Edit',
	'Admin/Languages/Ctrl/Install',
	'Admin/Languages/Ctrl/List',
	'Admin/Languages/Ctrl/PhraseList',
	'Admin/Languages/Ctrl/PhraseGroup',
	'Admin/Languages/Ctrl/PhraseResGroup',
	'Admin/Languages/Ctrl/Settings',
	'Admin/Languages/Ctrl/TranslateModal',
	'Admin/License/Ctrl/License',
	'Admin/Tasks/Ctrl/Edit',
	'Admin/Templates/Ctrl/EmailTemplateEditor',
	'Admin/Templates/Ctrl/EmailGroupList',
	'Admin/Templates/Ctrl/EmailList',
	'Admin/Templates/Ctrl/TemplateEditor',
	'Admin/Templates/Ctrl/TemplateGroupList',
	'Admin/Templates/Ctrl/TemplateList',
	'Admin/TicketAccounts/Ctrl/List',
	'Admin/TicketAccounts/Ctrl/Edit',
	'Admin/TicketAccounts/Ctrl/Settings',
	'Admin/TicketDeps/Ctrl/List',
	'Admin/TicketDeps/Ctrl/Edit',
	'Admin/TicketEscalations/Ctrl/List',
	'Admin/TicketEscalations/Ctrl/Edit',
	'Admin/TicketFields/Ctrl/EditCategories',
	'Admin/TicketFields/Ctrl/EditPriorities',
	'Admin/TicketFields/Ctrl/EditProducts',
	'Admin/TicketFields/Ctrl/EditWorkflows',
	'Admin/TicketFields/Ctrl/List',
	'Admin/TicketFilters/Ctrl/List',
	'Admin/TicketFilters/Ctrl/Edit',
	'Admin/TicketMacros/Ctrl/List',
	'Admin/TicketMacros/Ctrl/Edit',
	'Admin/TicketSlas/Ctrl/List',
	'Admin/TicketSlas/Ctrl/Edit',
	'Admin/TicketSettings/Ctrl/TicketSettings',
	'Admin/TicketSettings/Ctrl/FwdSettings',
	'Admin/TicketStatuses/Ctrl/List',
	'Admin/TicketStatuses/Ctrl/EditAwaitingAgent',
	'Admin/TicketStatuses/Ctrl/EditAwaitingUser',
	'Admin/TicketStatuses/Ctrl/EditClosed',
	'Admin/TicketStatuses/Ctrl/EditHiddenDeleted',
	'Admin/TicketStatuses/Ctrl/EditHiddenSpam',
	'Admin/TicketStatuses/Ctrl/EditHiddenValidating',
	'Admin/TicketStatuses/Ctrl/EditResolved',
	'Admin/TicketTriggers/Ctrl/EditDepartmentTrigger',
	'Admin/TicketTriggers/Ctrl/EditEmailAccountTrigger',
	'Admin/TicketTriggers/Ctrl/Edit',
	'Admin/TicketTriggers/Ctrl/List',
	'Admin/TicketUrgencies/Ctrl/List',
	'Admin/FeedbackStatuses/Ctrl/List',
	'Admin/FeedbackStatuses/Ctrl/Edit',
	'Admin/FeedbackTypes/Ctrl/List',
	'Admin/FeedbackTypes/Ctrl/Edit',
	'Admin/FeedbackCategories/Ctrl/List',
	'Admin/FeedbackCategories/Ctrl/Edit',
	'Admin/TwitterSetup/Ctrl/TwitterSetup',
	'Admin/TwitterAccounts/Ctrl/List',
	'Admin/TwitterAccounts/Ctrl/Edit',
	'Admin/Portal/Ctrl/Embeds',
	'Admin/Portal/Ctrl/PortalEditor',
	'Admin/Server/Ctrl/ServerReqs',
	'Admin/Server/Ctrl/ServerPhpInfo',
	'Admin/Server/Ctrl/ServerMysqlInfo',
	'Admin/Server/Ctrl/ServerMysqlStatus',
	'Admin/Server/Ctrl/ServerMysqlSortOrder',
	'Admin/Server/Ctrl/ServerErrorLogs',
	'Admin/Server/Ctrl/ServerErrorLogsView',
	'Admin/Server/Ctrl/ServerTaskQueue',
	'Admin/Server/Ctrl/ServerCronList',
	'Admin/Server/Ctrl/ServerCronLogs',
	'Admin/Server/Ctrl/ServerFileUploads',
	'Admin/Server/Ctrl/ServerFileCheck',
	'Admin/Server/Ctrl/ServerReportFile',
	'Admin/Settings/Ctrl/AdvancedSettings',
	'Admin/Settings/Ctrl/ElasticSearch',
	'Admin/Settings/Ctrl/GeneralSettings',
	'Admin/Settings/Ctrl/PortalSettings',
	'Admin/Settings/Ctrl/RegSettings',
	'Admin/Settings/Ctrl/PasswordSettings',
	'Admin/Settings/Ctrl/ServerSettings',
	'Admin/UserGroups/Ctrl/List',
	'Admin/UserGroups/Ctrl/Edit',
	'Admin/UserReg/Ctrl/UsersourceList',
	'Admin/ChatFields/Ctrl/List',
	'Admin/ChatSetup/Ctrl/ChatSetup',
	'Admin/ChatDeps/Ctrl/List',
	'Admin/ChatDeps/Ctrl/Edit',
	'Admin/ApiKeys/Ctrl/List',
	'Admin/ApiKeys/Ctrl/Edit',
	'Admin/UserFields/Ctrl/List',
	'Admin/OrgFields/Ctrl/List',
	'Admin/Banning/Ctrl/List',
	'Admin/Banning/Ctrl/EditIp',
	'Admin/Banning/Ctrl/EditEmail',
	'Admin/ImportCsv/Ctrl/ImportCsv',
	'Admin/UserRules/Ctrl/List',
	'Admin/UserRules/Ctrl/Edit',
	'Admin/KbSettings/Ctrl/KbSettings',
	'Admin/DownloadsSettings/Ctrl/DownloadsSettings',
	'Admin/NewsSettings/Ctrl/NewsSettings',
	'Admin/FeedbackSettings/Ctrl/FeedbackSettings',
	'Admin/RoundRobin/Ctrl/List',
	'Admin/RoundRobin/Ctrl/Edit',

	'CloudAdminLoad'

], function(angular) {
	return {
		start: function() {

			// Set path for ace editor
			ace.config.set("basePath",   DP_ASSET_URL + "/app/bower_components/ace-builds/src-min-noconflict");
			ace.config.set("modePath",   DP_ASSET_URL + "/app/bower_components/ace-builds/src-min-noconflict");
			ace.config.set("themePath",  DP_ASSET_URL + "/app/bower_components/ace-builds/src-min-noconflict");
			ace.config.set("workerPath", DP_ASSET_URL + "/app/bower_components/ace-builds/src-min-noconflict");

			var loadingEl = document.getElementById('dp_loading');
			loadingEl.parentNode.removeChild(loadingEl);
			loadingEl = null;

			window.DP_UID_COUNTER = 0;
			window.dp_get_uid = function () {
				return window.DP_UID_COUNTER++;
			};
			var $html = angular.element(document.getElementsByTagName('html')[0]);

			angular.element().ready(function () {
				$html.addClass('ng-app');

				if (window.DP_CTRL_REG) {
					var module = angular.module('Admin_App');
					for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
						module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
					}
				}

				angular.bootstrap($html, ['Admin_App']);
			});
		}
	}
});