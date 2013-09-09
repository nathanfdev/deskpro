requirejs.config({
	baseUrl: DP_ASSET_URL,
	urlArgs: "bust=" + (new Date()).getTime(),
    paths: {
		jquery:          DP_ASSET_URL+'/app/bower_components/jquery/jquery',
		angular:         DP_ASSET_URL+'/app/bower_components/angular/angular',
		angularUiRouter: DP_ASSET_URL+'/app/bower_components/angular-ui-router/release/angular-ui-router',
		angularSanitize: DP_ASSET_URL+'/app/bower_components/angular-sanitize/angular-sanitize',
		Admin:           DP_ASSET_URL+'/app/Admin'
	},
	shim: {
		'angular' : {'exports' : 'angular'},
		'angularUiRouter': ['angular'],
		'angularSanitize': ['angular']
	},
	priority: [
		"angular"
	]
});

window.name = "NG_DEFER_BOOTSTRAP!";

requirejs([
	'jquery',
	'angular',
	'angularUiRouter',
	'Admin/Resources/config/routing',
	'Admin/App',

	'Admin/Main/Directive/Section',
	'Admin/Main/Ctrl/Base',
	'Admin/Main/Ctrl/Index',
	'Admin/Main/Ctrl/SettingsNav',

	'Admin/TicketDeps/Ctrl/List',
	'Admin/TicketDeps/Ctrl/Edit'
], function(jquery, angular) {
	'use strict';
	var $html = angular.element(document.getElementsByTagName('html')[0]);

	angular.element().ready(function() {
		$html.addClass('ng-app');
		angular.bootstrap($html, ['Admin_App']);
	});
});