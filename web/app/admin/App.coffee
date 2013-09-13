define [
	'angular',
	'Admin/Resources/config/routing'
	'Admin/Main/Service/AppState'
	'Admin/Main/Service/DpApi'
	'Admin/Main/Directive/ActiveStateMark',
	'Admin/Main/DataService/EntityManager',
	'Admin/Main/DataService/Departments',
], (
	angular,
	routing,
	Admin_Main_Service_AppState,
	Admin_Main_Service_DpApi,
	Admin_Main_Directive_ActiveStateMark,
	Admin_Main_DataService_EntityManager,
	Admin_Main_DataService_Departments
) ->
	####################################################################################################################
	# Main services
	####################################################################################################################

	Admin_App = angular.module('Admin_App', ['ui.router', 'ui.bootstrap', 'ui.select2']);

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
			require:  'ngModel',
			template: '<div ng-class="{\'switch-on\': model, \'switch-off\': !model}"><input type="checkbox" /></div>',
			replace: true,
			scope: {
				options: '@dpToggleSwitch',
				model: '=ngModel'
			}
			link: (scope, element, attrs, ngModel) ->
				if scope.options
					options = scope.$eval(scope.options)
				else
					options = {}

				$wrap = jQuery(element)
				$wrap.attr('data-off-label', options['off-label'] || "<i class='icon-remove'></i>");
				$wrap.attr('data-on-label', options['on-label'] || "<i class='icon-ok'></i>");
				$wrap.attr('data-off', options['off-class'] || "danger");
				$wrap.attr('data-on', options['on-class'] || "success");

				$wrap.addClass('dp-switch')
				if scope.class
					$wrap.addClass(scope.class)

				if scope.model
					$wrap.find('input').get(0).checked = true
					ngModel.$setViewValue(true)

				$wrap.bootstrapSwitch()

				$wrap.find('input').on('change', ->
					el = this
					scope.$apply(->
						val = !!el.checked
						ngModel.$setViewValue(val)
						scope.model = val
					);
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