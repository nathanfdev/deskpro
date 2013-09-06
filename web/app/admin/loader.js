requirejs.config({
	baseUrl: DP_ASSET_URL,
	urlArgs: "bust=" + (new Date()).getTime(),
    paths: {
		jquery:          DP_ASSET_URL+'/app/bower_components/jquery/jquery',
		angular:         DP_ASSET_URL+'/app/bower_components/angular/angular',
		angularUiRouter: DP_ASSET_URL+'/app/bower_components/angular-ui-router/release/angular-ui-router',
		angularSanitize: DP_ASSET_URL+'/app/bower_components/angular-sanitize/angular-sanitize',
		admin:           DP_ASSET_URL+'/app/admin',
		jquerySplitter:  DP_ASSET_URL+'/app/other_components/jquery-splitter/jquery.splitter'
	},
	shim: {
		'angular' : {'exports' : 'angular'},
		'angularUiRouter': ['angular'],
		'angularSanitize': ['angular'],
		'jquerySplitter': ['jquery']
	},
	priority: [
		"angular"
	]
});

window.name = "NG_DEFER_BOOTSTRAP!";

requirejs( [
	'jquery',
	'angular',
	'angularUiRouter',
	'jquerySplitter',
	'admin/AdminApp',
	'admin/directives/common',
	'admin/controllers/index',
	'admin/routes'
], function(jquery, angular, app, routes) {
	'use strict';
	var $html = angular.element(document.getElementsByTagName('html')[0]);

	angular.element().ready(function() {
		$html.addClass('ng-app');
		angular.bootstrap($html, ['AdminApp']);
	});
});