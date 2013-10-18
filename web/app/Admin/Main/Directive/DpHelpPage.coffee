define ->
	###
    # Description
    # -----------
    #
    # This element defines the "help page" for a section. The help page is detached and re-positioned into the
    # right-most pane and has the ability to minimise into the section help icon.
    #
    # Example
    # -------
    # <dp-help-page>
    #    .....
    # </dp-help-page>
    #
    # <!-- In the list content we need the trigger as well: -->
    # <button class="btn help-page-trigger"><i class="icon-question-sign"></i></button>
	###
	Admin_Main_Directive_DpHelpPage = ['$rootScope', '$state', ($rootScope, $state) ->
		return {
			restrict: 'AE',
			scope: {},
			replace: true,
			transclude: true,
			template: '<section class="dp-help-page dp-section-page ng-hide" ng-hide="loading.dp_section_list"><div class="inner"><div class="close-btn"><i class="icon-remove"></i></div><div ng-transclude></div></div></section>',
			link: (scope, element, attrs) ->
				element.hide()
				isOpen = false

				$button = element.closest('.dp-section-list').find('.help-page-trigger').first()
				$border = angular.element('<div class="help-min-frame"></div>').hide().appendTo('body')

				$page = $('#dp_section_page')

				buttonW = $button.outerWidth()
				buttonH = $button.outerHeight()
				btnMod = -6

				element.detach().appendTo('#dp_section_body').css({
					position: 'absolute',
					'z-index': '10000',
					'overflow': 'auto'
				})

				scope.$on('$destroy', ->
					element.remove()
					$border.remove()
				)

				my_state = null
				if $state.current?.name
					state_segs = $state.current.name.split('.')
					if state_segs.length == 3
						state_segs.pop()

					my_state = state_segs.join('.')

				if not $state.current?.with_page_view or $state.current?.views['dp_section_page@']?.controller == 'Admin_Main_Ctrl_Bare'
					isOpen = true
					element.show()
					$button.hide()

				openFn = ->
					if isOpen then return
					isOpen = true

					pageH = $page.height()
					pageW = $page.width()
					pageOffset = $page.offset()

					buttonOffset = $button.offset()
					$border.css({
						width:  5,
						height: 5,
						left:   buttonOffset.left + (buttonW / 2) - 3,
						top:    buttonOffset.top + (buttonH / 2) - 3,
						borderRadius: 0
					})

					$border.show()
					$border.animate({
						height: pageH,
						width:  pageW,
						left:   pageOffset.left,
						top:    pageOffset.top,
					}, 310, ->
						$border.hide()
					)
					window.setTimeout(->
						element.fadeIn(100)
					, 210)
					$button.fadeOut(200)

				closeFn = (instantly) ->
					if not isOpen then return
					isOpen = false

					if instantly
						element.hide()
						$border.hide()
						$button.show()
						return

					pageH = $page.height()
					pageW = $page.width()
					pageOffset = $page.offset()

					$border.css({
						height: pageH,
						width:  pageW,
						left:   pageOffset.left,
						top:    pageOffset.top,
					})

					$button.fadeIn(200)
					buttonOffset = $button.offset()

					$border.show()
					element.fadeOut(125)
					$border.animate({
						width:  5,
						height: 5,
						left:   buttonOffset.left + (buttonW / 2) - 3,
						top:    buttonOffset.top + (buttonH / 2) - 3
					}, 310, ->
						$border.hide()
					)

				$button.on('click', (ev) ->
					ev.preventDefault();
					if isOpen then closeFn()
					else openFn()
				)

				element.find('.close-btn').on('click', (ev) ->
					ev.preventDefault();
					closeFn()
				)

				$rootScope.$on('$stateChangeStart', (ev, toState, toParams, fromState, fromParams) ->
					if my_state
						state_segs = toState.name.split('.')
						if state_segs.length == 3
							state_segs.pop()

						new_state = state_segs.join('.')

						if new_state != my_state
							closeFn(true)
						else
							closeFn()
					else
						closeFn()
				)

				return
		}
	]

	return Admin_Main_Directive_DpHelpPage