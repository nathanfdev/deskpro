requirejs.config({
	baseUrl: DP_ASSET_URL,
	urlArgs: "bust=" + (new Date()).getTime(),
    paths: {
		angular:                DP_ASSET_URL+'/app/bower_components/angular/angular',
		angularAnimate:         DP_ASSET_URL+'/app/bower_components/angular-animate/angular-animate.min',
		angularBootstrap:       DP_ASSET_URL+'/app/bower_components/angular-bootstrap/ui-bootstrap-tpls.min',
		angularSanitize:        DP_ASSET_URL+'/app/bower_components/angular-sanitize/angular-sanitize',
		angularSelect2:         DP_ASSET_URL+'/app/bower_components/angular-ui-select2/src/select2',
		angularUiAce:           DP_ASSET_URL+'/app/bower_components/angular-ui-ace/ui-ace',
		angularUiRouter:        DP_ASSET_URL+'/app/bower_components/angular-ui-router/release/angular-ui-router',
		angularUiSortable:      DP_ASSET_URL+'/app/bower_components/angular-ui-sortable/src/sortable',
		angularMoment:          DP_ASSET_URL+'/app/bower_components/angular-moment/angular-moment.min',

		momentjs:               DP_ASSET_URL+'/app/bower_components/momentjs/min/moment-with-langs.min',
		aceEditor:              DP_ASSET_URL+'/app/bower_components/ace-builds/src-min-noconflict/ace',
		stacktrace:             DP_ASSET_URL+'/app/bower_components/stacktrace/stacktrace',

		jquery:                 DP_ASSET_URL+'/app/bower_components/jquery/jquery',
		jqueryUi:               DP_ASSET_URL+'/app/bower_components/jquery-ui/ui/jquery-ui',
		underscore:             DP_ASSET_URL+'/app/bower_components/underscore/underscore-min',

		bootstrapModal:         DP_ASSET_URL+'/app/bower_components/bootstrap/js/modal',
		bootstrapTooltip:       DP_ASSET_URL+'/app/bower_components/bootstrap/js/tooltip',
		select2:                DP_ASSET_URL+'/app/bower_components/select2/select2.min',
		toastr:                 DP_ASSET_URL+'/app/bower_components/toastr/toastr.min',

		DeskPRO:                DP_ASSET_URL+'/app/DeskPRO/build/js',
		Admin:                  DP_ASSET_URL+'/app/Admin/build/js',
		AdminRouting:           DP_ASSET_URL+'/app/Admin/Resources/config/routing'
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

		'jqueryUi':             ['jquery'],

		'bootstrapModal':      ['jquery'],
		'bootstrapTooltip':    ['jquery', 'jqueryUi'],
		'select2':             ['jquery'],
		'toastr':              ['jquery'],
		'underscore':          { exports: '_' },
		'stacktrace':            { exports: 'printStackTrace'}
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

	'momentjs',
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

	'Admin/EmailStatus/Ctrl/SendmailList',
	'Admin/EmailStatus/Ctrl/SourceList',
	'Admin/Main/Ctrl/Bare',
	'Admin/Main/Ctrl/BareList',
	'Admin/Main/Ctrl/MainPage',
	'Admin/Main/Ctrl/Home',
	'Admin/Main/Ctrl/BackToAgent',
	'Admin/Main/Ctrl/NavAgents',
	'Admin/Main/Ctrl/NavApps',
	'Admin/Main/Ctrl/NavBase',
	'Admin/Main/Ctrl/NavChat',
	'Admin/Main/Ctrl/NavCrm',
	'Admin/Main/Ctrl/NavPortal',
	'Admin/Main/Ctrl/NavServer',
	'Admin/Main/Ctrl/NavSetup',
	'Admin/Main/Ctrl/NavTickets',
	'Admin/Main/Ctrl/NavTwitter',
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
	'Admin/Languages/Ctrl/TranslateModal',
	'Admin/Templates/Ctrl/EmailTemplateEditor',
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
	'Admin/ServerReqs/Ctrl/ServerReqs',
	'Admin/ServerPhpInfo/Ctrl/ServerPhpInfo',
	'Admin/ServerMysqlInfo/Ctrl/ServerMysqlInfo',
	'Admin/ServerMysqlStatus/Ctrl/ServerMysqlStatus',
	'Admin/ServerMysqlSortOrder/Ctrl/ServerMysqlSortOrder'
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
			var module = angular.module('Admin_App')
			for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
				module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
			}
		}

		angular.bootstrap($html, ['Admin_App']);
	});
});