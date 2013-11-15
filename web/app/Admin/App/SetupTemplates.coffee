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

			for own _, route of AdminRouting
				if route.page? and route.page.templateName
					templates.push(route.page.templateName)
				if route.nav? and route.nav.templateName
					templates.push(route.nav.templateName)
				if route.list? and route.list.templateName
					templates.push(route.list.templateName)

			for t in templates
				dpTemplateManager.load(t)
				dpTemplateManager.loadPending().then(-> window.DP_IS_BOOTED = true)
		])