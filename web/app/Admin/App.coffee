define [
	'angular',
	'Admin/Resources/config/routing',
	'Admin/Main/Service/AppState',
	'Admin/Main/Service/DpApi',
	'Admin/Main/Service/Growl',
	'Admin/Main/DataService/EntityManager',
	'Admin/Main/DataService/Departments',
], (
	angular,
	routing,
	Admin_Main_Service_AppState,
	Admin_Main_Service_DpApi,
	Admin_Main_Service_Growl,
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

	Admin_App.service('Growl', [ ->
		return new Admin_Main_Service_Growl()
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
			template: """
				<div class="dp-switch">
					<label><span></span></label>
				</div>
			""",
			replace: true,
			scope: {
				model: '=ngModel',
				lockedModel: '=lockedModel',
				change: '=ngChange',
				lockedTip: '@'
			},
			link: (scope, element, attrs, ngModel) ->


				updateVal = ->
					val = scope.model

					ngModel.$setViewValue(val)
					scope.model = val

					if val
						element.addClass('switch-on')
						element.removeClass('switch-off')
					else
						element.removeClass('switch-on')
						element.addClass('switch-off')

					if scope.change
						scope.$eval(scope.change)

				element.on('click', (ev) ->
					ev.preventDefault();

					if element.hasClass('locked')
						return

					scope.model = !scope.model
					scope.$apply(->
						updateVal(updateVal)
					)
				)

				scope.$watch('model', ->
					updateVal()
				)

				scope.$watch('lockedModel', (newVal) ->
					if newVal
						element.addClass('locked')
					else
						element.removeClass('locked')
				)

				if scope.lockedTip
					tipTarget = angular.element('<div class="mouse-target show-on-locked-on"></div>')
					tipTarget.attr('title', scope.lockedTip)
					tipTarget.appendTo(element)
					tipTarget.tooltip({
						placement: 'auto top',
						trigger: 'hover',
						container: 'body'
					})

				if scope.model
					ngModel.$setViewValue(true)
					element.addClass('switch-on')
					element.removeClass('switch-off')
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

	Admin_App.directive('bgImg', ->
		return {
			restrict: 'A',
			scope: {
				'bgImg': '&'
			},
			link: (scope, element, attrs) ->
				element.css({
					'background-image': 'url("' + scope.$eval(scope.bgImg) + '")'
				})
		}
	)

	Admin_App.directive('dpServerValidation', ->
		return {
			require: 'ngModel',
			restrict: 'A',
			link: (scope, elm, attrs, ngModel) ->
				ngModel.dpServerValidationKeys = attrs.dpServerValidation.split(',')

				if not ngModel.dpServerValidationKeys.length
					return

				# Server-side validation errors always reset
				# when we re-validate on the client (e.g., so they can re-submit)
				ngModel.$parsers.unshift( (viewValue) ->
					for own error_code, is_error of ngModel.$error
						if not is_error then continue

						for code in ngModel.dpServerValidationKeys
							code_safe = code.replace(/\./g, '_')
							if error_code == code_safe
								code_segs = code.split('.')
								last_seg = code_segs.pop();

								switch last_seg
									when 'required'
										ngModel.$setValidity('required', true)
									else
										ngModel.$setValidity(code_safe, true)

					return viewValue;
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
			'Index/modal-alert.html',
			'Index/modal-confirm-leavetab.html',
			'TicketDeps/code-link.html',
			'TicketDeps/code-win.html',
			'TicketDeps/code-embed.html',
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