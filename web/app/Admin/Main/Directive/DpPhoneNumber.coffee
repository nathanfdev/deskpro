define ["jquery", "intl-tel-input", "intl-tel-input-utils"] , ($, intlTelInput, utils) ->
  ###
    # Description
    # -----------
    #
    # This turns an input into an intl-tel-input:
    # https://github.com/Bluefieldscom/intl-tel-input
    #
    # You can pass in the default selected 2 character country code.
    #
    # NOTE: If you are using a scoped model (ie API) for this input's phone number
    #       value, you should always pass in the country code parameter
    #       that will be evaluated at the time as the model. Otherwise,
    #       the widget might render incorrectly due to DOM issues.
    #       This is because we need to call setNumber() after its visible to the user.
    #
    # Example
    # -------
    # <input dp-phone-number="CA">
    #
    # Example with handling a model attached:
    # <input dp-phone-number="{{ phone.region }}" ng-model="phone.number">
  ###
  Admin_Main_Directive_DpPhoneNumber = [ '$rootScope', '$timeout', ($rootScope, $timeout) ->
    return {
      require: 'ngModel',
      restrict: 'A',
      scope: {
        region: '@dpPhoneNumber'
      },
      link: (scope, element, attr, ngModel) ->
        # when we get the dpPhoneNumber attribute value, setup intlTelInput
        attr.$observe 'dpPhoneNumber', (reg) ->
          element.intlTelInput({
              autoPlaceholder: true,
              autoFormat: true,
              nationalMode: true,
              defaultCountry: reg.toLowerCase(),
          })
          element.intlTelInput("setNumber", element.val())
          element.intlTelInput('utilsLoaded')
          element.bind('blur keyup change input', () ->
            scope.$apply(() ->
              ngModel.$setViewValue(element.intlTelInput('getNumber'))
            )
          )

    }
  ]

  return Admin_Main_Directive_DpPhoneNumber
