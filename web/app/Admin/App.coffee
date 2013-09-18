define [
	'angular',
	'Admin/Resources/config/routing'
	'Admin/Main/Service/AppState'
	'Admin/Main/Service/DpApi'
	'Admin/Main/DataService/EntityManager',
	'Admin/Main/DataService/Departments',
], (
	angular,
	routing,
	Admin_Main_Service_AppState,
	Admin_Main_Service_DpApi,
	Admin_Main_DataService_EntityManager,
	Admin_Main_DataService_Departments
) ->
	####################################################################################################################
	# Main services
	####################################################################################################################

	Admin_App = angular.module('Admin_App', ['ui.router', 'ui.bootstrap', 'ui.select2', 'ui.sortable']);

	Admin_App.service('AppState', ['$rootScope', '$state', ($rootScope, $state) ->
		return new Admin_Main_Service_AppState($rootScope, $state)
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

	Admin_App.directive('dpStateMark', ['$rootScope', '$state', ($rootScope, $state) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				checkState = (stateId, newStateId) ->
					return if not stateId or not newStateId
					stateIdRegex = '^'
					stateIdRegex += stateId.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")
					stateIdRegex += '\\b'

					if newStateId.match(new RegExp(stateIdRegex))
						return true
					else
						return false

				if $state.current?.name
					current_state_id = $state.current.name
					if $state.params.id
						current_state_id += '.' + $state.params.id

					if checkState(attrs.dpStateMark, current_state_id)
						element.addClass('state-on active')

				$rootScope.$on('dp_activeStateChange', (ev, newStateId) ->
					if checkState(attrs.dpStateMark, newStateId)
						element.addClass('state-on active')
					else
						element.removeClass('state-on active')
				, true);
		}
	])

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

	Admin_App.directive('dpTabBtn', ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				if not scope.dp_tab_ids
					scope.dp_tab_ids = {}

				id_segs = attrs['dpTabBtn']
				if not id_segs
					return

				id_segs   = id_segs.split('.')
				tab_val   = id_segs.pop()
				tab_group = id_segs.join('.')

				if element.hasClass('active')
					scope.dp_tab_ids[tab_group] = tab_val

				element.on('click', (ev) ->
					ev.preventDefault()
					scope.dp_tab_ids[tab_group] = tab_val
					scope.$apply()
				)

				scope.$watch(->
					return scope.dp_tab_ids[tab_group]
				, (newVal) ->
					if newVal == tab_val
						element.addClass('active')
					else
						element.removeClass('active')
				)
		}
	)

	Admin_App.directive('dpTabBody', ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				if not scope.dp_tab_ids
					scope.dp_tab_ids = {}

				id_segs = attrs['dpTabBody']
				if not id_segs
					return

				id_segs   = id_segs.split('.')
				tab_val   = id_segs.pop()
				tab_group = id_segs.join('.')

				if scope.dp_tab_ids[tab_group] == tab_val
					element.show()
				else
					element.hide()

				scope.$watch(->
					return scope.dp_tab_ids[tab_group]
				, (newVal) ->
					if newVal == tab_val
						element.show()
					else
						element.hide()
				)
		}
	)

	####################################################################################################################
	# Routing
	####################################################################################################################

	Admin_App.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
		$urlRouterProvider.otherwise("/")

		with_lists = {}

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

			opts = {url: url, views: views, with_nav_view: true}
			if route.with_list_view
				opts.with_list_view = true
			else if route.list?
				opts.with_list_view = true
			else if route.page?
				segs = id.split('.')
				segs.pop()

				if with_lists[segs.join('.')]
					opts.with_list_view = true

			if route.with_nav_view?
				opts.with_nav_view = route.with_nav_view

			if opts.with_list_view
				with_lists[id] = true

			$stateProvider.state(id, opts)
	])

	####################################################################################################################
	# Preload Nav Templates
	####################################################################################################################

	Admin_App.run(['$http', '$templateCache', ($http, $templateCache) ->
		templates = [
			'Index/app-nav-setup.html',
			'Index/app-nav-agents.html',
			'Index/app-nav-tickets.html',
			'Index/app-nav-crm.html',
			'Index/app-nav-portal.html',
			'Index/app-nav-chat.html',
			'Index/app-nav-twitter.html',
			'Index/app-nav-apps.html',
			'Index/app-nav-server.html',
			'Index/blank.html',
		]

		qs = []
		for t in templates
			qs.push('views[]=' + encodeURIComponent(t))

		qs = qs.join('&')

		$http({
			method: 'GET',
			url: DP_BASE_ADMIN_URL+'/load-view/multi?' + qs
		}).success( (data) ->
			for tpl in data
				id = DP_BASE_ADMIN_URL+'/load-view/'+ tpl.id
				$templateCache.put(id, tpl.source)
		)

		window.TC = $templateCache
	])

	if window.parent?.DP_FRAME_OVERLAY_admin
		window.parent?.DP_FRAME_OVERLAY_admin.callLoaded();

	return Admin_App