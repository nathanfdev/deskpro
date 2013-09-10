define [
	'angular',
	'Admin/Resources/config/routing'
	'Admin/Main/Service/AppState'
	'Admin/Main/Service/DpApi'
	'Admin/Main/Directive/ActiveStateMark',
	'Admin/Main/DataService/Departments',
], (
	angular,
	routing,
	Admin_Main_Service_AppState,
	Admin_Main_Service_DpApi,
	Admin_Main_Directive_ActiveStateMark,
	Admin_Main_DataService_Departments
) ->
	####################################################################################################################
	# Main services
	####################################################################################################################

	Admin_App = angular.module('Admin_App', ['ui.router']);

	Admin_App.service('AppState', ['$rootScope', ($rootScope) ->
		return new Admin_Main_Service_AppState($rootScope)
	])
	Admin_App.service('Api', ['$http', ($http) ->
		return new Admin_Main_Service_DpApi(
		  $http,
		  window.DP_BASE_API_URL,
		  window.DP_API_TOKEN
		)
	])

	Admin_App.factory('$exceptionHandler', ['$log', ($log) ->
		return (exception, cause) ->
			throw exception
	])

	####################################################################################################################
	# Data services
	####################################################################################################################

	Admin_App.service('DepartmentData', ['Api', '$q', (Api, $q) ->
		return new Admin_Main_DataService_Departments(Api, $q)
	])

	####################################################################################################################
	# Main directives
	####################################################################################################################

	Admin_App.directive('dpStateMark', ->
		return new Admin_Main_Directive_ActiveStateMark()
	)

	####################################################################################################################
	# Routing
	####################################################################################################################

	Admin_App.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
		$urlRouterProvider.otherwise("/settings/ticket_deps")

		for route in routing
			id = route.id
			url = route.url
			views = {}

			if route.nav?
				views['dp_section_nav'] = route.nav
			if route.list?
				views['dp_section_list@'] = route.list
			if route.page?
				views['dp_section_page@'] = route.page

			$stateProvider.state(id, {url: url, views: views})
	])

	return Admin_App