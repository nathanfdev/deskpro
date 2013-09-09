define [
	'angular',
	'Admin/Resources/config/routing'
	'Admin/Main/Service/AppState'
	'Admin/Main/Directive/ActiveStateMark'
], (
	angular,
	routing,
	Admin_Main_Service_AppState,
	Admin_Main_Directive_ActiveStateMark
) ->
	Admin_App = angular.module('Admin_App', ['ui.router']);

	Admin_App.service('AppState', Admin_Main_Service_AppState)
	Admin_App.directive('dpStateMark', Admin_Main_Directive_ActiveStateMark)

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