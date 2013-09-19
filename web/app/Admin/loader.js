requirejs.config({
	baseUrl: DP_ASSET_URL,
	urlArgs: "bust=" + (new Date()).getTime(),
    paths: {
		angular:                DP_ASSET_URL+'/app/bower_components/angular/angular',
		angularBootstrap:       DP_ASSET_URL+'/app/bower_components/angular-bootstrap/ui-bootstrap-tpls.min',
		angularSanitize:        DP_ASSET_URL+'/app/bower_components/angular-sanitize/angular-sanitize',
		angularSelect2:         DP_ASSET_URL+'/app/bower_components/angular-ui-select2/src/select2',
		angularUiRouter:        DP_ASSET_URL+'/app/bower_components/angular-ui-router/release/angular-ui-router',
		angularUiSortable:      DP_ASSET_URL+'/app/bower_components/angular-ui-sortable/src/sortable',

		jquery:                 DP_ASSET_URL+'/app/bower_components/jquery/jquery',
		jqueryUi:               DP_ASSET_URL+'/app/bower_components/jquery-ui/ui/jquery-ui',
		underscore:             DP_ASSET_URL+'/app/bower_components/underscore/underscore-min',

		bootstrapModal:         DP_ASSET_URL+'/app/bower_components/bootstrap/js/modal',
		bootstrapSwitch:        DP_ASSET_URL+'/app/bower_components/bootstrap-switch/static/js/bootstrap-switch',
		select2:                DP_ASSET_URL+'/app/bower_components/select2/select2.min',
		toastr:                 DP_ASSET_URL+'/app/bower_components/toastr/toastr.min',

		Admin:                  DP_ASSET_URL+'/app/Admin'
	},
	shim: {
		'angular':              {'exports' : 'angular'},
		'angularBootstrap':     ['angular'],
		'angularSanitize':      ['angular'],
		'angularSelect2':       ['angular'],
		'angularUiRouter':      ['angular'],
		'angularUiSortable':    ['angular'],

		'jqueryUi':             ['jquery'],

		'bootstrapModal':      ['jquery'],
		'bootstrapSwitch':     ['jquery'],
		'select2':             ['jquery'],
		'toastr':              ['jquery'],
		'underscore':          { expores: '_' }
	},
	priority: [
		"angular"
	]
});

window.name = "NG_DEFER_BOOTSTRAP!";

requirejs([
	'angular',
	'angularBootstrap',
	'angularSelect2',
	'angularUiRouter',
	'angularUiSortable',

	'jquery',
	'jqueryUi',
	'underscore',

	'bootstrapSwitch',
	'select2',
	'toastr',

	'Admin/App',
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
	'Admin/TicketDeps/Ctrl/List',
	'Admin/TicketDeps/Ctrl/Edit'
], function(angular) {
	'use strict';

	window.DP_UID_COUNTER = 0;
	window.dp_get_uid = function() {
		return window.DP_UID_COUNTER++;
	};
	var $html = angular.element(document.getElementsByTagName('html')[0]);

	angular.element().ready(function() {
		$html.addClass('ng-app');
		angular.bootstrap($html, ['Admin_App']);
	});
});