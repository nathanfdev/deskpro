define ["jquery", "intl-tel-input", "intl-tel-input-utils"] , ($, intlTelInput, utils) ->
  ###
    # Description
    # -----------
    #
    # This turns a div into an intl-tel-input with extension support:
    # https://github.com/Bluefieldscom/intl-tel-input
    #
    # You can pass in the default selected 2 character country code.
    #
    # attributes:
    #
    # default_region = the default country code to use if no phone number
    # start-phone-number = the phone number to start with. this will likely be the same
    #                      data as the model, except formatted as "{num} ext. {extension}"
    # phone = a JS model (object) that this writes to, in the format of { number: "+19024030560", ext: "999" }
    #
    # Example
    # -------
    # <div dp-phone-number
		#			 phone="EditCtrl.form.primary_phone"
		#			 default_region="{{ ng_var('EditCtrl.primary_phone_number_region')  }}"
		#			 start-phone-number="{{ ng_var('EditCtrl.form.primary_phone.number') }} ext. {{ ng_var('EditCtrl.form.primary_phone.ext') }}">
		#		</div>
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

        dialCodes = $.fn.intlTelInput.getCountryData().reduce((a, cdata) =>
          a[cdata.dialCode] = cdata.iso2
          return a
        , {})

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
            $main.bind('change input', () ->
              main_val = $main.val();
              for dcode, isocode of dialCodes
                dial_code = '+' + dcode;
                shouldRemoveDialCode = main_val.indexOf(dial_code) == 0 && main_val.length > dial_code.length + 1
                if shouldRemoveDialCode
                  $main.intlTelInput('setNumber', $main.val().substr(dial_code.length).trim())
                  $main.intlTelInput('selectCountry', isocode);

              raw_input = $main.val().split("ext.");
              if ((raw_input.length > 1 && raw_input[1].trim().length == 0) or raw_input.length == 1)
                $main.intlTelInput('setNumber', raw_input[0].replace(/\s/g, ''))

              $scope.phone = {number: $main.intlTelInput('getNumber'), ext: $main.intlTelInput('getExtension')}

              if not $main.intlTelInput('getExtension').trim()
                $main.intlTelInput('setNumber', raw_input[0].trim())
                $main.intlTelInput('setExtension', null)
            )

        didNotRun2 = true
        $attrs.$observe 'startPhoneNumber', (startPhoneNumber) =>
          raw_input = startPhoneNumber.split("ext.")
          if didNotRun2 && raw_input.length > 0 && raw_input[0].trim().length > 0
            didNotRun2 = false
            num = raw_input[0].trim()
            if raw_input.length > 1 && raw_input[1].trim().length > 0
              num += ' ext. ' + raw_input[1].trim()
            $main.intlTelInput("setNumber", num)
    }
  ]

  return Admin_Main_Directive_DpPhoneNumber
