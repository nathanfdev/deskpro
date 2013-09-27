define ->
	Admin_Main_Directive_DpErrorClass = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				updateClass = ->
					formProp = scope.$eval(attrs.dpErrorClass)
					if not formProp then return

					set_errorclass = false
					if formProp.$invalid and (formProp.$dirty or formProp.$attempted)
						set_errorclass = true

					if set_errorclass
						element.addClass('has-error')
					else
						element.removeClass('has-error')

				watch_vars = [
					attrs.dpErrorClass+'.$invalid',
					attrs.dpErrorClass+'.$dirty',
					attrs.dpErrorClass+'.$attempted'
				]
				scope.$watch('dpErrorClass', ->
					for varname in watch_vars
						scope.$watch(varname, ->
							updateClass()
						, true)
				)
		}
	]

	return Admin_Main_Directive_DpErrorClass