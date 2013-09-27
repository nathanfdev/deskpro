define ->
	Admin_Main_Directive_DpToggleSwitch = [ ->
		return {
			restrict: 'A',
			require:  'ngModel',
			template: """
				<div class="dp-switch">
					<label><span></span></label>
				</div>
			""",
			replace: true,
			scope: {
				model: '=ngModel',
				lockedModel: '=lockedModel',
				change: '=ngChange',
				lockedTip: '@'
			},
			link: (scope, element, attrs, ngModel) ->

				updateVal = ->
					val = scope.model

					ngModel.$setViewValue(val)
					scope.model = val

					if val
						element.addClass('switch-on')
						element.removeClass('switch-off')
					else
						element.removeClass('switch-on')
						element.addClass('switch-off')

					if scope.lockedModel
						element.addClass('locked')
					else
						element.removeClass('locked')

					if scope.change
						scope.$eval(scope.change)

				element.on('click', (ev) ->
					ev.preventDefault();

					if element.hasClass('locked')
						return

					scope.model = !scope.model
					scope.$apply(->
						updateVal(updateVal)
					)
				)

				scope.$watch('model', ->
					updateVal()
				)

				scope.$watch('lockedModel', (newVal) ->
					if newVal
						element.addClass('locked')
					else
						element.removeClass('locked')
				)

				if scope.lockedTip
					tipTarget = angular.element('<div class="mouse-target show-on-locked-on"></div>')
					tipTarget.attr('title', scope.lockedTip)
					tipTarget.appendTo(element)
					tipTarget.tooltip({
						placement: 'auto top',
						trigger: 'hover',
						container: 'body'
					})

				if scope.model
					ngModel.$setViewValue(true)
					element.addClass('switch-on')
					element.removeClass('switch-off')
		}
	]

	return Admin_Main_Directive_DpToggleSwitch