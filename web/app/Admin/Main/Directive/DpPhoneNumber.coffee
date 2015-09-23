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
      restrict: 'A',
      scope: {
        defaultRegion: '@',
        startPhoneNumber: '@',
        phone: '='
      },
      template: '<div><input type="tel" class=".user_input" style="min-width: 250px" class="form-control" name="primary_phone" style="width:80%" /><input type="hidden" class=".hidden_ext" ng-model="phone.ext"><input type="hidden" class=".hidden_number" ng-model="phone.number"></div>',
      replace: true,
      link: ($scope, $element, $attrs) ->
        $elements = $element.find('input');
        $main = $($elements[0]);
        $ext = $($elements[1]);
        $num = $($elements[2]);

        didNotRun = true
        $attrs.$observe 'defaultRegion', (region) =>
          if didNotRun && region
            didNotRun = false
            $main.intlTelInput({
                defaultCountry: region.toLowerCase(),
                autoPlaceholder: true,
                autoFormat: true,
                allowExtensions: true,
                nationalMode: true
              })
            $main.intlTelInput('utilsLoaded')
            $main.bind('blur keyup change input', () ->
                raw_input = $main.val().split(" ext. ");
                if (raw_input.length > 1 && raw_input[1].length == 0)
                  $main.val(raw_input[0])

                $scope.phone = {number: $main.intlTelInput('getNumber'), ext: $main.intlTelInput('getExtension')}
              )

        didNotRun2 = true
        $attrs.$observe 'startPhoneNumber', (startPhoneNumber) =>
          if didNotRun2 && startPhoneNumber && startPhoneNumber.trim().length > 0
            didNotRun2 = false
            $main.intlTelInput("setNumber", startPhoneNumber)
    }
  ]

  return Admin_Main_Directive_DpPhoneNumber
