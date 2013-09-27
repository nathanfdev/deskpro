define ->
	Admin_Main_Directive_DpPingFlash = [ ->
		return {
			restrict: 'A',
			scope: false,
			link: (scope, element, attrs) ->
				element.addClass('dp-ping-flash')
				id = '_ctrl_elemnt_ping.' + attrs['dpPingFlash'];

				scope.$watch(id, (newVal) ->
					if not newVal then return
					element.stop().fadeIn(500, ->
						window.setTimeout(->
							if (element)
								element.stop().fadeOut(400)
						, 400)
					)
				)
				return
		}
	]

	return Admin_Main_Directive_DpPingFlash