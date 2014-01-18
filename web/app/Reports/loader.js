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

		moment:                 DP_ASSET_URL+'/app/bower_components/momentjs/min/moment-with-langs.min',
		stacktrace:             DP_ASSET_URL+'/app/bower_components/stacktrace/stacktrace',

		jquery:                 DP_ASSET_URL+'/app/bower_components/jquery/jquery',
		jqueryUi:               DP_ASSET_URL+'/app/bower_components/jquery-ui/ui/jquery-ui',
		underscore:             DP_ASSET_URL+'/app/bower_components/underscore/underscore-min',

		bootstrapModal:         DP_ASSET_URL+'/app/bower_components/bootstrap/js/modal',
		bootstrapTooltip:       DP_ASSET_URL+'/app/bower_components/bootstrap/js/tooltip',
		select2:                DP_ASSET_URL+'/app/bower_components/select2/select2.min',
		toastr:                 DP_ASSET_URL+'/app/bower_components/toastr/toastr.min',

		Admin:                  DP_ASSET_URL+'/app/Admin/build/js',
		Reports:                DP_ASSET_URL+'/app/Reports/build/js',
		ReportsRouting:         DP_ASSET_URL+'/app/Reports/Resources/config/routing'
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
	'angularBootstrap',
	'angularSelect2',
	'angularUiRouter',
	'angularUiSortable',

	'jquery',
	'jqueryUi',
	'underscore',

	'bootstrapTooltip',

	'select2',
	'toastr',

	'Reports/App/App'
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
			var module = angular.module('Reports_App');
			for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
				module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
			}
		}

		angular.bootstrap($html, ['Reports_App']);
	});
});