requirejs.config({
	baseUrl: DP_ASSET_URL,
	urlArgs: "bust=" + (new Date()).getTime(),
    paths: {
		angular:                         DP_ASSET_URL+'/app/bower_components/angular/angular',
		angularRoute:                    DP_ASSET_URL+'/app/bower_components/angular-route/angular-route.min',

		DeskPRO:                         DP_ASSET_URL+'/app/DeskPRO/build/js',
		AdminUpgrade:                    DP_ASSET_URL+'/app/AdminUpgrade/build/js'
	},
	shim: {
		'angular':         {'exports' : 'angular'},
		'angularRoute':    ['angular']
	},
	priority: [
		"angular"
	]
});

window.name = "NG_DEFER_BOOTSTRAP!";
requirejs([
	'angular',
	'angularRoute',

	'AdminUpgrade/App/App',
	'AdminUpgrade/Main/Ctrl/UpgradeHome',
	'AdminUpgrade/Main/Ctrl/UpgradeWatch'
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
			var module = angular.module('AdminUpgrade_App');
			for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
				module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
			}
		}

		angular.bootstrap($html, ['AdminUpgrade_App']);
	});
});