define ->
	return ['$compile', 'dpTemplateManager', ($compile, dpTemplateManager) ->
		directive = {}
		directive.restrict    = 'E'
		directive.replace     = true

		directive.template = (el, attrs) ->
			return dpTemplateManager.getNow("TicketDeps/layout-editor-#{attrs.type}field.html")

		directive.link = (scope, element, attrs, ngModel) ->
			console.log("link field")

		return directive
	]