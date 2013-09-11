define ['jquery'], (jQuery) ->
	class Admin_Main_Directive_ToggleSwitch
		construct: ->
			@restrict = 'A'
			@template = '<div ng-transclude></div>'
			@replace = true
			@transclude = true
			@['require'] = 'ng-model'
			return

		link: (scope, element, attrs, ngModel) ->
			$check = jQuery(element)
			$wrap  = $check.parent()

			$wrap.addClass('make-switch').bootstrapSwitch();

			ngModel.$render = ->
				val = ngModel.$viewValue
				if val
					$check.bootstrapSwitch('active', true)
				else
					$check.bootstrapSwitch('active', false)

			ngModel.$render()