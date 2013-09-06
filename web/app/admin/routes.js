define(['angular', 'admin/AdminApp', 'admin/controllers/index'], function(angular, app) {
	'use strict';

	return app.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
		$urlRouterProvider.otherwise("/");

		$stateProvider.state('index', {
			url: '/',
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/index.html',
			controller: 'IndexCtrl'
		});
	}]);
});