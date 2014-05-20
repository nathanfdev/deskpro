define ->
	DeskPRO_Directive_DpHelpPage = ['$rootScope', '$state', ($rootScope, $state) ->
		return {
			restrict: 'E',
			scope: {},
			replace: true,
			transclude: true,
			template: """
				<section class="dp-help-content-wrapper">
					<div class="dp-help-content-outer">
						<div class="dp-help-content-outer2">
							<div class="dp-help-content" ng-transclude></div>
							<div class="dp-arrow-wrap"><em><i class="fa fa-chevron-down down"></i><i class="fa fa-chevron-up up"></i></em></div>
						</div>
					</div>
				</section>
			""",
			link: (scope, element, attrs) ->
				isOpen = false
				backdrop = null
				element.find('header').first().prepend('<aside><i class="fa fa-question-circle"></i></aside>')

				open = ->
					return if isOpen
					origH = element.height()
					element.height(origH)

					if not backdrop
						backdrop = $('<div/>').addClass('dp-help-content-backdrop')
						backdrop.on('click', (ev) ->
							ev.preventDefault()
							close()
						)
						backdrop.insertBefore(element)

					backdrop.show()
					element.addClass('open')
					article = element.find('.dp-help-content').find('article').first()
					article.slideDown(200, 'linear')
					isOpen = true

				close = ->
					return if not isOpen
					backdrop.hide()
					article = element.find('.dp-help-content').find('article').first()
					article.slideUp(200, 'linear', ->
						element.removeClass('open')
					)
					isOpen = false

				toggle = ->
					if not isOpen
						open()
					else
						close()

				element.find('.dp-arrow-wrap').on('click', (ev) ->
					ev.preventDefault();
					toggle()
				)
				element.find('header').first().on('click', (ev) ->
					ev.preventDefault();
					toggle()
				)

				element.on('click', (ev) ->
					ev.stopPropagation();
					open()
				)
		}
	]

	return DeskPRO_Directive_DpHelpPage
