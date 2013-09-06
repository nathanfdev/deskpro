define(['angular', 'angularRoute'],
function (angular, filters, services, directives, controllers) {
	'use strict';

	return angular.module('app', [
		'ngRoute',
		'dpAdmin.controllers.index'
	]);
});