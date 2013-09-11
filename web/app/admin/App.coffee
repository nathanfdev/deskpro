define [
	'angular',
	'Admin/Resources/config/routing'
	'Admin/Main/Service/AppState'
	'Admin/Main/Service/DpApi'
	'Admin/Main/Directive/ActiveStateMark',
	'Admin/Main/Directive/ToggleSwitch',
	'Admin/Main/DataService/EntityManager',
	'Admin/Main/DataService/Departments',
], (
	angular,
	routing,
	Admin_Main_Service_AppState,
	Admin_Main_Service_DpApi,
	Admin_Main_Directive_ActiveStateMark,
	Admin_Main_Directive_ToggleSwitch,
	Admin_Main_DataService_EntityManager,
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

	Admin_App.service('em', [ ->
		return new Admin_Main_DataService_EntityManager()
	])

	Admin_App.service('DepartmentData', ['em', 'Api', '$q', (em, Api, $q) ->
		return new Admin_Main_DataService_Departments(em, Api, $q)
	])

	####################################################################################################################
	# Main directives
	####################################################################################################################

	Admin_App.directive('dpStateMark', ->
		return new Admin_Main_Directive_ActiveStateMark()
	)

	Admin_App.directive('dpToggleSwitch', ->
		return {
			restrict: 'A',
			require: 'ngModel',
			link: (scope, element, attrs, ngModel) ->
				$check = jQuery(element)
				$wrap  = $check.parent()
				$wrap.attr('data-off-label', $check.attr('data-off-label') || "<i class='icon-remove'></i>");
				$wrap.attr('data-on-label', $check.attr('data-on-label') || "<i class='icon-check'></i>");
				$wrap.attr('data-off', $check.attr('data-off') || "danger");
				$wrap.attr('data-on', $check.attr('data-on') || "success");

				$wrap.addClass('make-switch').bootstrapSwitch();

				ngModel.$render = ->
					val = ngModel.$viewValue
					$check.bootstrapSwitch('setActive', val)

				scope.$watch("attrs.ngModel", (val) ->
					console.log("Changed")
					$check.bootstrapSwitch('setActive', val)
				)


		}
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