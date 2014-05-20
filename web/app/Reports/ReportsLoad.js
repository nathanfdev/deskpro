define([
	'angular',
	'angularAnimate',
	'angularSanitize',
	'angularBootstrap',
	'angularSelect2',
	'angularUiRouter',
	'angularUiSortable',
	'angularMoment',
	'angularFileUpload',
	'angularSlider',

	'moment',

	'jquery',
	'jqueryUi',
	'underscore',
	'stacktrace',

	'bootstrapTooltip',

	'select2',
	'toastr',

	'DeskPRO/OptionBuilder/Module',
	'DeskPRO/CategoryBuilder/Module',

	'Reports/App/App',

	'Reports/Main/Ctrl/MainPage',
	'Reports/Main/Ctrl/Bare',

	'Reports/Main/Ctrl/BackToAgent',

	'Reports/Overview/Ctrl/Overview',
	'Reports/Builder/Ctrl/List',
	'Reports/Builder/Ctrl/Edit',
	'Reports/AgentActivity/Ctrl/AgentActivity',
	'Reports/AgentHours/Ctrl/AgentHours',
	'Reports/TicketSatisfaction/Ctrl/TicketSatisfaction',
	'Reports/Billing/Ctrl/List',
	'Reports/Billing/Ctrl/View'
], function(angular) {
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
					var module = angular.module('Reports_App');
					for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
						module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
					}
				}

				angular.bootstrap($html, ['Reports_App']);
			});
		}
	}
});