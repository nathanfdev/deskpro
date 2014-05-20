define [
	'AdminRouting',
], (
	AdminRouting
) ->
	return (Module) ->
		Module.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
			$urlRouterProvider.otherwise("/")

			# Load templates through the dpTemplateManager
			# so we can take advantage of our preloading scheme
			makeProvider = (view) ->
				return ['dpTemplateManager', (dpTemplateManager) ->
					return dpTemplateManager.get(view)
				]

			for route in AdminRouting
				id = route.id
				url = route.url

				if route.templateName?
					route.templateProvider = makeProvider(route.templateName)

				opts = {
					url: url,
					data: route.data || null
				}

				if route.resolve
					opts.resolve = route.resolve

				if route.views
					opts.views = route.views
				else
					opts.views = {}

					v = {}
					if route.templateProvider
						v.templateProvider = route.templateProvider
					else if route.templateName
						v.templateName = route.templateName
					if route.controller
						v.controller = route.controller

					if route.target
						viewName = route.target
					else
						# An app-level (tickets.ticket_deps)
						# Is always added to the appbody
						if id.split('.').length == 2
							viewName = "appbody"
						else
							viewName = ""

					opts.views[viewName] = v

				$stateProvider.state(id, opts)
		])