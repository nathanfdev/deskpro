define [
	'angular',
	'DP_LANG',
	'ReportsRouting',

	'Admin/Main/Service/DpApi',
	'Admin/Main/Service/Growl',
	'Admin/Main/Service/TemplateManager',

	'Admin/Main/Translate/DpInterpolation',
], (
	angular,
	DP_LANG,
	routing,

	Admin_Main_Service_DpApi,
	Admin_Main_Service_Growl,
	Admin_Main_Service_TemplateManager,

	Admin_Main_Translate_DpInterpolation
) ->
	####################################################################################################################
	# Main services
	####################################################################################################################

	Reports_App = angular.module('Reports_App', ['ui.router', 'ui.bootstrap', 'ui.select2', 'ui.sortable', 'pascalprecht.translate']);

	Reports_App.service('Api', ['$http', ($http) ->
		return new Admin_Main_Service_DpApi(
		  $http,
		  window.DP_BASE_API_URL,
		  window.DP_API_TOKEN
		)
	])

	Reports_App.factory('$exceptionHandler', ['$log', ($log) ->
		return (exception, cause) ->
			throw exception
	])

	Reports_App.filter('escape_url', [ ->
		return (text) ->
			return encodeURIComponent(text)
	])

	Reports_App.service('Growl', [ ->
		return new Admin_Main_Service_Growl()
	])

	####################################################################################################################
	# Translation
	####################################################################################################################

	Reports_App.factory('translateDpInterpolation', Admin_Main_Translate_DpInterpolation)

	Reports_App.config(['$translateProvider', ($translateProvider) ->
		$translateProvider.translations('default', DP_LANG)
		$translateProvider.preferredLanguage('default')
		$translateProvider.useInterpolation('translateDpInterpolation')
	])

	####################################################################################################################
	# Routing
	####################################################################################################################

	Reports_App.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
		#TODO
	])

	####################################################################################################################
	# Templates and pre-load templates
	####################################################################################################################

	Reports_App.service('dpTemplateManager', ['$templateCache', '$http', '$q', ($templateCache, $http, $q) ->
		return new Admin_Main_Service_TemplateManager($templateCache, $http, $q)
	])

	# Decorate the $templateCache so view names are always the 'short' names
	# and not URLs
	# e.g.  /deskpro/adm/load-view/Index/blank.html -> Index/blank.html
	Reports_App.config(['$provide', ($provide) ->
		$provide.decorator('$templateCache', ['$delegate', '$http', ($delegate, $http) ->
			$delegate.ngGet = $delegate.get
			$delegate.get = (view) ->
				view = view.replace(/^.*?\/adm\/load\-view\//g, '')
				return $delegate.ngGet(view)

			$delegate.ngPut = $delegate.put
			$delegate.put = (view, value) ->
				view = view.replace(/^.*?\/adm\/load\-view\//g, '')
				return $delegate.ngPut(view, value)

			return $delegate
		])
	])

	if window.parent?.DP_FRAME_OVERLAY_reports
		window.parent?.DP_FRAME_OVERLAY_reports.callLoaded();

	return Reports_App