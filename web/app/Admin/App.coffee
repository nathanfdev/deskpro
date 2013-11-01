define [
	'angular',
	'AdminRouting',

	'Admin/Main/Service/AppState',
	'Admin/Main/Service/DpApi',
	'Admin/Main/Service/Growl',
	'Admin/Main/Service/InhelpState',
	'Admin/Main/Service/TemplateManager',
	'Admin/OptionBuilder/TypesDef/TicketCriteria',
	'Admin/OptionBuilder/TypesDef/TicketActions',

	'Admin/Main/Directive/Autofocus',
	'Admin/Main/Directive/BgImg',
	'Admin/Main/Directive/DpCommaSeparated',
	'Admin/Main/Directive/DpErrorClass',
	'Admin/Main/Directive/DpHelpPage',
	'Admin/Main/Directive/DpHideSpinning',
	'Admin/Main/Directive/DpInhelpBody',
	'Admin/Main/Directive/DpInhelpBtn',
	'Admin/Main/Directive/DpNavSubnav',
	'Admin/Main/Directive/DpOpenPhraseEditor',
	'Admin/Main/Directive/DpPingFlash',
	'Admin/Main/Directive/DpRegisterMessage',
	'Admin/Main/Directive/DpServerValidation',
	'Admin/Main/Directive/DpShowSpinning',
	'Admin/Main/Directive/DpStateMark',
	'Admin/Main/Directive/DpSubmitForm',
	'Admin/Main/Directive/DpTabBody',
	'Admin/Main/Directive/DpTabBtn',
	'Admin/Main/Directive/DpToggleSwitch',
	'Admin/Main/Directive/DpTristateCheck',
	'Admin/Main/Directive/DpWorkingHours',

	'Admin/TicketDeps/Directive/LayoutEditor',
	'Admin/TicketDeps/Directive/LayoutEditorField',

	'Admin/Main/DataService/EntityManager',
	'Admin/Main/DataService/Departments',
	'Admin/FeedbackStatuses/DataService/FeedbackStatuses',
	'Admin/FeedbackTypes/DataService/FeedbackTypes',
	'Admin/TicketAccounts/DataService/TicketAccounts',
	'Admin/Labels/Service/LabelManager'
], (
	angular,
	routing,

	Admin_Main_Service_AppState,
	Admin_Main_Service_DpApi,
	Admin_Main_Service_Growl,
	Admin_Main_Service_InhelpState,
	Admin_Main_Service_TemplateManager,
	Admin_OptionBuilder_TypesDef_TicketCriteria,
	Admin_OptionBuilder_TypesDef_TicketActions,

	Admin_Main_Directive_Autofocus,
	Admin_Main_Directive_BgImg,
	Admin_Main_Directive_DpCommaSeparated,
	Admin_Main_Directive_DpErrorClass,
	Admin_Main_Directive_DpHelpPage,
	Admin_Main_Directive_DpHideSpinning,
	Admin_Main_Directive_DpInhelpBody,
	Admin_Main_Directive_DpInhelpBtn,
	Admin_Main_Directive_DpNavSubnav,
	Admin_Main_Directive_DpOpenPhraseEditor,
	Admin_Main_Directive_DpPingFlash,
	Admin_Main_Directive_DpRegisterMessage,
	Admin_Main_Directive_DpServerValidation,
	Admin_Main_Directive_DpShowSpinning,
	Admin_Main_Directive_DpStateMark,
	Admin_Main_Directive_DpSubmitForm,
	Admin_Main_Directive_DpTabBody,
	Admin_Main_Directive_DpTabBtn,
	Admin_Main_Directive_DpToggleSwitch,
	Admin_Main_Directive_DpTristateCheck,
	Admin_Main_Directive_DpWorkingHours,

	Admin_TicketDeps_Directive_LayoutEditor,
	Admin_TicketDeps_Directive_LayoutEditorField,

	Admin_Main_DataService_EntityManager,
	Admin_Main_DataService_Departments,
	Admin_FeedbackStatuses_DataService_FeedbackStatuses,
	Admin_FeedbackTypes_DataService_FeedbackTypes,
	Admin_TicketAccounts_DataService_TicketAccounts,
	Admin_Labels_Service_LabelManager
) ->
	####################################################################################################################
	# Main services
	####################################################################################################################

	Admin_App = angular.module('Admin_App', [
		'ui.router',
		'ui.bootstrap',
		'ui.select2',
		'ui.sortable',
		'ui.ace',
		'deskpro.option_builder',
		'deskpro.category_builder'
	]);

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

	Admin_App.service('InhelpState', ['Api', (Api) ->
		return new Admin_Main_Service_InhelpState(Api)
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

	Admin_App.service('FeedbackStatusesData', ['em', 'Api', '$q', (em, Api, $q) ->
		return new Admin_FeedbackStatuses_DataService_FeedbackStatuses(em, Api, $q)
	])

	Admin_App.service('FeedbackTypesData', ['em', 'Api', '$q', (em, Api, $q) ->
		return new Admin_FeedbackTypes_DataService_FeedbackTypes(em, Api, $q)
	])

	Admin_App.service('TicketAccountsData', ['em', 'Api', '$q', (em, Api, $q) ->
		return new Admin_TicketAccounts_DataService_TicketAccounts(em, Api, $q)
	])

	Admin_App.service('LabelManager', ['Api', '$q', (Api, $q) ->
		return new Admin_Labels_Service_LabelManager(Api, $q)
	])

	Admin_App.filter('escape_url', [ ->
		return (text) ->
			return encodeURIComponent(text)
	])

	Admin_App.service('Growl', [ ->
		return new Admin_Main_Service_Growl()
	])

	Admin_App.factory('dpObTypesDefTicketCriteria', [ '$q', 'Api', 'dpTemplateManager', ($q, Api, dpTemplateManager) ->
		return new Admin_OptionBuilder_TypesDef_TicketCriteria($q, Api, dpTemplateManager)
	])

	Admin_App.factory('dpObTypesDefTicketActions', [ '$q', 'Api', 'dpTemplateManager', ($q, Api, dpTemplateManager) ->
		return new Admin_OptionBuilder_TypesDef_TicketActions($q, Api, dpTemplateManager)
	])

	####################################################################################################################
	# Main directives
	####################################################################################################################

	Admin_App.directive('autofocus',          Admin_Main_Directive_Autofocus)
	Admin_App.directive('bgImg',              Admin_Main_Directive_BgImg)
	Admin_App.directive('dpCommaSeparated',   Admin_Main_Directive_DpCommaSeparated)
	Admin_App.directive('dpErrorClass',       Admin_Main_Directive_DpErrorClass)
	Admin_App.directive('dpHelpPage',         Admin_Main_Directive_DpHelpPage)
	Admin_App.directive('dpHideSpinning',     Admin_Main_Directive_DpHideSpinning)
	Admin_App.directive('dpInhelpBody',       Admin_Main_Directive_DpInhelpBody)
	Admin_App.directive('dpInhelpBtn',        Admin_Main_Directive_DpInhelpBtn)
	Admin_App.directive('dpNavSubnav',        Admin_Main_Directive_DpNavSubnav)
	Admin_App.directive('dpOpenPhraseEditor', Admin_Main_Directive_DpOpenPhraseEditor)
	Admin_App.directive('dpPingFlash',        Admin_Main_Directive_DpPingFlash)
	Admin_App.directive('dpRegisterMessage',  Admin_Main_Directive_DpRegisterMessage)
	Admin_App.directive('dpServerValidation', Admin_Main_Directive_DpServerValidation)
	Admin_App.directive('dpShowSpinning',     Admin_Main_Directive_DpShowSpinning)
	Admin_App.directive('dpStateMark',        Admin_Main_Directive_DpStateMark)
	Admin_App.directive('dpSubmitForm',       Admin_Main_Directive_DpSubmitForm)
	Admin_App.directive('dpTabBody',          Admin_Main_Directive_DpTabBody)
	Admin_App.directive('dpTabBtn',           Admin_Main_Directive_DpTabBtn)
	Admin_App.directive('dpToggleSwitch',     Admin_Main_Directive_DpToggleSwitch)
	Admin_App.directive('dpTristateCheck',    Admin_Main_Directive_DpTristateCheck)
	Admin_App.directive('dpWorkingHours',     Admin_Main_Directive_DpWorkingHours)
	Admin_App.directive('dpTicketLayoutEditor',      Admin_TicketDeps_Directive_LayoutEditor)
	Admin_App.directive('dpTicketLayoutEditorField', Admin_TicketDeps_Directive_LayoutEditorField)

	####################################################################################################################
	# Routing
	####################################################################################################################

	Admin_App.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
		$urlRouterProvider.otherwise("/")

		with_lists = {}

		# Load templates through the dpTemplateManager
		# so we can take advantage of our preloading scheme
		makeProvider = (view) ->
			return ['dpTemplateManager', (dpTemplateManager) ->
				return dpTemplateManager.get(view)
			]

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

			if route.page?.templateName?
				route.page.templateProvider = makeProvider(route.page.templateName)
			if route.list?.templateName?
				route.list.templateProvider = makeProvider(route.list.templateName)
			if route.nav?.templateName?
				route.nav.templateProvider = makeProvider(route.nav.templateName)

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

			if route.page?
				opts.with_page_view = true

			if route.with_nav_view?
				opts.with_nav_view = route.with_nav_view

			if opts.with_list_view
				with_lists[id] = true

			$stateProvider.state(id, opts)
	])

	####################################################################################################################
	# Templates and pre-load templates
	####################################################################################################################

	Admin_App.service('dpTemplateManager', ['$templateCache', '$http', '$q', ($templateCache, $http, $q) ->
		return new Admin_Main_Service_TemplateManager($templateCache, $http, $q)
	])

	# Decorate the $templateCache so view names are always the 'short' names
	# and not URLs
	# e.g.  /deskpro/admin/load-view/Index/blank.html -> Index/blank.html
	Admin_App.config(['$provide', ($provide) ->
		$provide.decorator('$templateCache', ['$delegate', ($delegate) ->
			$delegate.ngGet = $delegate.get
			$delegate.get = (view) ->
				view = view.replace(/^.*?\/admin\/load\-view\//g, '')
				return $delegate.ngGet(view)

			$delegate.ngPut = $delegate.put
			$delegate.put = (view, value) ->
				view = view.replace(/^.*?\/admin\/load\-view\//g, '')
				return $delegate.ngPut(view, value)

			return $delegate
		])
	])

	# Add fcall() to $q service (like Kris Kowal's Q: https://github.com/kriskowal/q)
	# Add isPromise
	Admin_App.config(['$provide', ($provide) ->
		$provide.decorator('$q', ['$delegate', ($delegate) ->
			$delegate.fcall = (fn) ->
				d = $delegate.defer()
				d.resolve(fn())
				return d.promise

			$delegate.isPromise = (val) ->
				return val.then?

			return $delegate
		])
	])

	Admin_App.run(['dpTemplateManager', (dpTemplateManager) ->
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
			'Languages/modal-translate-phrase.html',
			'TicketDeps/code-phpapi.html',
			'TicketDeps/code-link.html',
			'TicketDeps/code-win.html',
			'TicketDeps/code-embed.html',
			'Index/blank.html',
			'Common/work-hours-directive.html',
			'TicketDeps/layout-editor.html',
			'TicketDeps/layout-editor-field.html'
		]

		for own _, route of routing
			if route.page? and route.page.templateName
				templates.push(route.page.templateName)
			if route.nav? and route.nav.templateName
				templates.push(route.nav.templateName)
			if route.list? and route.list.templateName
				templates.push(route.list.templateName)

		for t in templates
			dpTemplateManager.load(t)

		dpTemplateManager.loadPending().then(->
			window.DP_IS_BOOTED = true
		)
	])

	if window.parent?.DP_FRAME_OVERLAY_admin
		window.parent?.DP_FRAME_OVERLAY_admin.callLoaded();

	return Admin_App