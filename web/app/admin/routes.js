define(['angular', 'admin/app', 'admin/controllers/index'], function(angular, app) {
	'use strict';

	return app.config(['$routeProvider', function($routeProvider) {
		$routeProvider.when('/', {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/index.html',
			controller: 'IndexCtrl'
		});
	}]);
});