requirejs.config({
	baseUrl: DP_ASSET_URL,
    paths: {
		jquery:          DP_ASSET_URL+'/app/bower_components/jquery/jquery',
		angular:         DP_ASSET_URL+'/app/bower_components/angular/angular',
		angularRoute:    DP_ASSET_URL+'/app/bower_components/angular-route/angular-route',
		angularSanitize: DP_ASSET_URL+'/app/bower_components/angular-sanitize/angular-sanitize',
		admin:           DP_ASSET_URL+'/app/admin'
	},
	shim: {
		'angular' : {'exports' : 'angular'},
		'angularRoute': ['angular'],
		'angularSanitize': ['angular']
	},
	priority: [
		"angular"
	]
});

window.name = "NG_DEFER_BOOTSTRAP!";

requirejs( [
	'jquery',
	'angular',
	'admin/app',
	'admin/routes'
], function(jquery, angular, app, routes) {
	'use strict';
	var $html = angular.element(document.getElementsByTagName('html')[0]);

	angular.element().ready(function() {
		$html.addClass('ng-app');
		angular.bootstrap($html, [app['name']]);
	});
});