define [
	'Admin/Main/Service/TemplateManager',
	'AdminRouting'
], (
	Admin_Main_Service_TemplateManager,
	AdminRouting
) ->
	return (Module) ->
		Module.service('dpTemplateManager', ['$templateCache', '$http', '$q', ($templateCache, $http, $q) ->
			return new Admin_Main_Service_TemplateManager($templateCache, $http, $q)
		])

		# Decorate the $templateCache so view names are always the 'short' names
		# and not URLs
		# e.g.  /deskpro/admin/load-view/Index/blank.html -> Index/blank.html
		Module.config(['$provide', ($provide) ->
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

		# Preload templates
		Module.run(['dpTemplateManager', (dpTemplateManager) ->
			templates = [
				'Index/app-nav-portal.html',
				'Index/app-nav-twitter.html',
				'Index/modal-alert.html',
				'Index/modal-confirm-leavetab.html',
				'Index/modal-confirm.html',
				'Languages/modal-translate-phrase.html',
				'Index/blank.html',
				'Index/home.html',
				'Common/work-hours-directive.html',
			]

			for t in templates
				dpTemplateManager.load(t)

			dpTemplateManager.loadPending().then(->
				window.setTimeout(->
					window.DP_IS_BOOTED = true
				, 400)
			)
		])