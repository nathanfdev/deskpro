define ['angular', 'Admin/Resources/config/routing'], (angular, routing) ->
	Admin_App = angular.module('Admin_App', ['ui.router']);

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