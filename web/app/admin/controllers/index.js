define(['angular'], function (angular) {
	'use strict';

return angular.module('dpAdmin.controllers.index')
	.controller('IndexCtrl', ['$scope', function ($scope) {
		$scope.hello = "World";
	}]);
});