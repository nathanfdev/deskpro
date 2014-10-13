define ->
	Admin_License_Directive_PaymentFormDirective = [ ->
		return {
			restrict: 'A',
			require: "ngModel",
			scope: {},
			link: (scope, element, attrs, model) ->
				model.$formatters.push( (m) ->
					return m
				)
				return
		}
	]

	return Admin_License_Directive_PaymentFormDirective