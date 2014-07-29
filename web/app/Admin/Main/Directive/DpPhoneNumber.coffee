define ["jquery", "intl-tel-input"] , ($, intlTelInput) ->
	###
    # Description
    # -----------
    #
    # This turns an input into an intl-tel-input:
    # https://github.com/Bluefieldscom/intl-tel-input
    #
    # Example
    # -------
    # <input dp-phone-number>
	###
	Admin_Main_Directive_DpPhoneNumber = [ '$rootScope', '$timeout', ($rootScope, $timeout) ->
		return {
			require: 'ngModel',
			restrict: 'A',
			# we need to apply the intlTelInput after the scope loads, and also do some manual model binding
			link: (scope, element, attrs, ngModel) ->
				scope.$watch 'loaded', ->
					setTimeout ->
						read = ->
							ngModel.$setViewValue(element.val())
						element.on 'focus blur keyup change', ->
							scope.$apply read
						element.intlTelInput()
						read()
					, 0

		}
	]

	return Admin_Main_Directive_DpPhoneNumber
