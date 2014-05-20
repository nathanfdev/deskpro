define [
	'angular',
	'DeskPRO/Main/Service/DpApi'
], (
	angular,
	DeskPRO_Main_Service_DpApi
) ->
	AdminUpgradeModule = angular.module('AdminUpgrade_App', ['ngRoute'])

	AdminUpgradeModule.factory('dpHttpInterceptor', ['$q', ($q) ->
		return {
			request: (config) ->
				if window.DP_SESSION_ID
					config.headers['X-DeskPRO-Session-ID'] = window.DP_SESSION_ID
				if window.DP_REQUEST_TOKEN
					config.headers['X-DeskPRO-Request-Token'] = window.DP_REQUEST_TOKEN

				return config

			response: (response) ->
				return response

			requestError: (rejection) ->
				return $q.reject(rejection)

			responseError: (rejection) ->
				return $q.reject(rejection)
		}
	])

	AdminUpgradeModule.config(['$httpProvider', ($httpProvider) ->
		$httpProvider.interceptors.push('dpHttpInterceptor');
	])

	AdminUpgradeModule.service('Api', ['$http', ($http) ->
		return new DeskPRO_Main_Service_DpApi(
			$http,
			window.DP_BASE_API_URL,
			window.DP_API_TOKEN
		)
	])

	AdminUpgradeModule.config(['$routeProvider', ($routeProvider) ->
		$routeProvider.when('/', {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Upgrade/home.html',
			controller: 'AdminUpgrade_Main_Ctrl_UpgradeHome'
		}).when('/progress', {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Upgrade/watch.html',
			controller: 'AdminUpgrade_Main_Ctrl_UpgradeWatch'
		}).otherwise({
			redirectTo: '/'
		})
	])

	return AdminUpgradeModule