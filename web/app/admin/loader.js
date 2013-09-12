requirejs.config({
	baseUrl: DP_ASSET_URL,
	urlArgs: "bust=" + (new Date()).getTime(),
    paths: {
		jquery:             DP_ASSET_URL+'/app/bower_components/jquery/jquery',
		bootstrap_modal:    DP_ASSET_URL+'/app/bower_components/bootstrap/js/modal',
		bootstrap_switch:   DP_ASSET_URL+'/app/other_components/bootstrap-switch/bootstrap-switch',
		angular:            DP_ASSET_URL+'/app/bower_components/angular/angular',
		angularUiRouter:    DP_ASSET_URL+'/app/bower_components/angular-ui-router/release/angular-ui-router',
		angularSanitize:    DP_ASSET_URL+'/app/bower_components/angular-sanitize/angular-sanitize',
		angularBootstrap:   DP_ASSET_URL+'/app/bower_components/angular-bootstrap/ui-bootstrap-tpls.min',
		Admin:              DP_ASSET_URL+'/app/Admin'
	},
	shim: {
		'angular' : {'exports' : 'angular'},
		'angularUiRouter': ['angular'],
		'angularSanitize': ['angular'],
		'angularBootstrap': ['angular'],
		'bootstrap_modal': ['jquery'],
		'bootstrap_switch': {
			deps: ['jquery'],
			exports: 'jQuery.fn.bootstrapSwitch'
		}
	},
	priority: [
		"angular"
	]
});

window.name = "NG_DEFER_BOOTSTRAP!";

requirejs([
	'jquery',
	'angular',
	'bootstrap_switch',
	'angularUiRouter',
	'angularBootstrap',
	'Admin/App',

	'Admin/Main/Ctrl/Bare',
	'Admin/Main/Ctrl/SettingsNav',
	'Admin/TicketDeps/Ctrl/List',
	'Admin/TicketDeps/Ctrl/Edit'
], function(jquery, angular) {
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