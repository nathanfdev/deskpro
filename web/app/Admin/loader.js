requirejs.config({
	baseUrl: DP_ASSET_URL,
	urlArgs: "bust=" + (new Date()).getTime(),
    paths: {
		angular:                         DP_ASSET_URL+'/app/bower_components/angular/angular',
		angularAnimate:                  DP_ASSET_URL+'/app/bower_components/angular-animate/angular-animate.min',
		angularBootstrap:                DP_ASSET_URL+'/app/bower_components/angular-bootstrap/ui-bootstrap-tpls.min',
		angularSanitize:                 DP_ASSET_URL+'/app/bower_components/angular-sanitize/angular-sanitize',
		angularSelect2:                  DP_ASSET_URL+'/app/bower_components/angular-ui-select2/src/select2',
		angularUiAce:                    DP_ASSET_URL+'/app/bower_components/angular-ui-ace/ui-ace',
		angularUiRouter:                 DP_ASSET_URL+'/app/bower_components/angular-ui-router/release/angular-ui-router',
		angularUiSortable:               DP_ASSET_URL+'/app/bower_components/angular-ui-sortable/src/sortable',
		angularMoment:                   DP_ASSET_URL+'/app/bower_components/angular-moment/angular-moment.min',
		angularFileUpload:               DP_ASSET_URL+'/app/bower_components/blueimp-file-upload/js/jquery.fileupload-angular',
		angularSlider:                   DP_ASSET_URL+'/app/bower_components/angular-slider/angular-slider.min',
		angularGrid:                     DP_ASSET_URL+'/app/bower_components/angular-grid/build/ng-grid.min',

		moment:                          DP_ASSET_URL+'/app/bower_components/momentjs/min/moment-with-langs.min',
		aceEditor:                       DP_ASSET_URL+'/app/bower_components/ace-builds/src-min-noconflict/ace',
		stacktrace:                      DP_ASSET_URL+'/app/bower_components/stacktrace/stacktrace',

		jquery:                          DP_ASSET_URL+'/app/bower_components/jquery/jquery',
		jqueryUi:                        DP_ASSET_URL+'/app/bower_components/jquery-ui/ui/jquery-ui',
		underscore:                      DP_ASSET_URL+'/app/bower_components/underscore/underscore-min',

		bootstrapModal:                  DP_ASSET_URL+'/app/bower_components/bootstrap/js/modal',
		bootstrapTooltip:                DP_ASSET_URL+'/app/bower_components/bootstrap/js/tooltip',
		select2:                         DP_ASSET_URL+'/app/bower_components/select2/select2.min',
		toastr:                          DP_ASSET_URL+'/app/bower_components/toastr/toastr.min',

		DeskPRO:                         DP_ASSET_URL+'/app/DeskPRO/build/js',
		Admin:                           DP_ASSET_URL+'/app/Admin/build/js',
		AdminRouting:                    DP_ASSET_URL+'/app/Admin/Resources/config/routing',

		ColorPicker:                     DP_ASSET_URL+'/vendor/colorpicker/js/colorpicker.min',

		'jquery.ui.widget':              DP_ASSET_URL+'/app/bower_components/blueimp-file-upload/js/vendor/jquery.ui.widget',

		'jquery.fileupload':             DP_ASSET_URL+'/app/bower_components/blueimp-file-upload/js/jquery.fileupload',
		'jquery.fileupload-process':     DP_ASSET_URL+'/app/bower_components/blueimp-file-upload/js/jquery.fileupload-process',
		'jquery.fileupload-image':       DP_ASSET_URL+'/app/bower_components/blueimp-file-upload/js/jquery.fileupload-image',
		'jquery.fileupload-audio':       DP_ASSET_URL+'/app/bower_components/blueimp-file-upload/js/jquery.fileupload-audio',
		'jquery.fileupload-video':       DP_ASSET_URL+'/app/bower_components/blueimp-file-upload/js/jquery.fileupload-video',
		'jquery.fileupload-validate':    DP_ASSET_URL+'/app/bower_components/blueimp-file-upload/js/jquery.fileupload-validate',

		'load-image':                    DP_ASSET_URL+'/app/bower_components/blueimp-load-image/js/load-image',
		'load-image-meta':               DP_ASSET_URL+'/app/bower_components/blueimp-load-image/js/load-image-meta',
		'load-image-ios':                DP_ASSET_URL+'/app/bower_components/blueimp-load-image/js/load-image-ios',
		'load-image-exif':               DP_ASSET_URL+'/app/bower_components/blueimp-load-image/js/load-image-exif',

		'canvas-to-blob':                DP_ASSET_URL+'/app/bower_components/blueimp-canvas-to-blob/js/canvas-to-blob.min'
	},
	shim: {
		'angular':              {'exports' : 'angular'},
		'angularAnimate':       ['angular'],
		'angularBootstrap':     ['angular'],
		'angularSanitize':      ['angular'],
		'angularSelect2':       ['angular'],
		'angularUiAce':         ['angular'],
		'angularUiRouter':      ['angular'],
		'angularUiSortable':    ['angular'],
		'angularMoment':        ['angular'],
		'angularFileUpload':    ['jquery'],
		angularSlider:          ['angular'],
		angularGrid:          ['angular'],

		'jqueryUi':             ['jquery'],
		'ColorPicker':          ['jquery'],

		'bootstrapModal':      ['jquery'],
		'bootstrapTooltip':    ['jquery', 'jqueryUi'],
		'select2':             ['jquery'],
		'toastr':              ['jquery'],
		'underscore':          { exports: '_' },
		'stacktrace':          { exports: 'printStackTrace'}
	},
	priority: [
		"angular"
	]
});

window.name = "NG_DEFER_BOOTSTRAP!";

requirejs([
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
	'angularGrid',

	'moment',
	'aceEditor',

	'jquery',
	'jqueryUi',
	'underscore',
	'stacktrace',

	'bootstrapTooltip',

	'select2',
	'toastr',

	'DeskPRO/OptionBuilder/Module',
	'DeskPRO/CategoryBuilder/Module',

	'Admin/App/App',

	'Admin/Agents/Ctrl/Edit',
	'Admin/Agents/Ctrl/List',
	'Admin/Agents/Ctrl/Logs',
	'Admin/AgentGroups/Ctrl/Edit',
	'Admin/AgentGroups/Ctrl/List',
	'Admin/AgentTeams/Ctrl/Edit',
	'Admin/AgentTeams/Ctrl/List',
	'Admin/EmailStatus/Ctrl/SendmailList',
	'Admin/EmailStatus/Ctrl/SourceList',
	'Admin/Main/Ctrl/Bare',
	'Admin/Main/Ctrl/BareList',
	'Admin/Main/Ctrl/MainPage',
	'Admin/Main/Ctrl/Home',
	'Admin/Main/Ctrl/BackToAgent',
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
	'Admin/Languages/Ctrl/Edit',
	'Admin/Languages/Ctrl/Install',
	'Admin/Languages/Ctrl/List',
	'Admin/Languages/Ctrl/Settings',
	'Admin/Languages/Ctrl/TranslateModal',
	'Admin/License/Ctrl/License',
	'Admin/Templates/Ctrl/EmailTemplateEditor',
	'Admin/Templates/Ctrl/EmailGroupList',
	'Admin/Templates/Ctrl/EmailList',
	'Admin/Templates/Ctrl/TemplateEditor',
	'Admin/Templates/Ctrl/TemplateGroupList',
	'Admin/Templates/Ctrl/TemplateList',
	'Admin/TicketAccounts/Ctrl/List',
	'Admin/TicketAccounts/Ctrl/Edit',
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
	'Admin/TicketStatuses/Ctrl/List',
	'Admin/TicketStatuses/Ctrl/EditAwaitingAgent',
	'Admin/TicketStatuses/Ctrl/EditAwaitingUser',
	'Admin/TicketStatuses/Ctrl/EditClosed',
	'Admin/TicketStatuses/Ctrl/EditHiddenDeleted',
	'Admin/TicketStatuses/Ctrl/EditHiddenSpam',
	'Admin/TicketStatuses/Ctrl/EditHiddenValidating',
	'Admin/TicketStatuses/Ctrl/EditResolved',
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
	'Admin/Plugins/Ctrl/List',
	'Admin/Plugins/Ctrl/Install',
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
	'Admin/Settings/Ctrl/EmailSettings',
	'Admin/Settings/Ctrl/GeneralSettings',
	'Admin/Settings/Ctrl/PortalSettings',
	'Admin/Settings/Ctrl/ServerSettings',
	'Admin/UserGroups/Ctrl/List',
	'Admin/UserGroups/Ctrl/Edit',
	'Admin/UserReg/Ctrl/Settings',
	'Admin/UserReg/Ctrl/UsersourceList',
	'Admin/UserReg/Ctrl/UsersourceNewType',
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
	'Admin/UserRules/Ctrl/Edit'
], function(angular) {
	'use strict';

	window.DP_UID_COUNTER = 0;
	window.dp_get_uid = function() {
		return window.DP_UID_COUNTER++;
	};
	var $html = angular.element(document.getElementsByTagName('html')[0]);

	angular.element().ready(function() {
		$html.addClass('ng-app');

		if (window.DP_CTRL_REG) {
			var module = angular.module('Admin_App');
			for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
				module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
			}
		}

		angular.bootstrap($html, ['Admin_App']);
	});
});