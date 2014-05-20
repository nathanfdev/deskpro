define ->
	DeskPRO_Directive_DpClickHref = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				clickHref = attrs['dpClickHref']
				element.on('click', (ev) ->
					if ev.which == 1 and not (ev.shiftKey or ev.altKey or ev.metaKey or ev.ctrlKey)
						ev.preventDefault()
						$('a').attr('href', clickHref).click()
				)
		}
	]

	return DeskPRO_Directive_DpClickHref