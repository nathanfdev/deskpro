define(['angular', 'angularUiRouter', 'admin/controllers/index'],
function (angular) {
	'use strict';

	return angular.module('AdminApp', [
		'ui.router',
		'AdminApp.directives.common',
		'AdminApp.controllers.index'
	]);
});