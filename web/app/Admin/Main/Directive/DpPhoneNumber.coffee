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
				setTimeout ->
					element.intlTelInput()
					read = ->
						ngModel.$setViewValue(element.val())
					element.on 'focus blur keyup change', ->
						scope.$apply read
					read()
				, 1250 # really should find a better way. this is to queue this func to run after new scope dom is ready

		}
	]

	return Admin_Main_Directive_DpPhoneNumber
