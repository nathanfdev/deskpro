define([
	'angular',
	'angularRoute',

	'AdminUpgrade/App/App',
	'AdminUpgrade/Main/Ctrl/UpgradeHome',
	'AdminUpgrade/Main/Ctrl/UpgradeWatch'
], function(angular) {

	if (!window.console) {
		window.console = {
			log: function(){},
			warn: function(){},
			error: function(){}
		}
	}

	return {
		start: function() {
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
		}
	}
});