define(["jquery", "intl-tel-input"], function($, intlTelInput) {
  /*
    * Description
    * -----------
    *
    * This turns an input into an intl-tel-input (no phone extension support):
    * https://github.com/Bluefieldscom/intl-tel-input
    *
    * For phone extension support, see the DpPhoneNumber directive. This directive exists
    * for simple cases where an extension is not desired (to SMS text message someone, for example).
    *
    * You can pass in the default selected 2 character country code.
    *
    * ng-model is bound to the phone number. no extensions will be returned (only
    * the phone number itself). to use extensions, use DpPhoneNumber.
    *
    * NOTE: If you are using a scoped model (ie API) for this input's phone number
    *       value, you should always pass in the country code parameter
    *       that will be evaluated at the time as the model. Otherwise,
    *       the widget might render incorrectly due to DOM issues.
    *       This is because we need to call setNumber() after its visible to the user.
    *
    * Example
    * -------
    * <input dp-phone-number="CA">
    *
    * Example with handling a model attached:
    * <input dp-phone-number-no-ext="{{ phone.region }}" ng-model="phone.number">
  */
  const Admin_Main_Directive_DpPhoneNumberNoExt = ['$rootScope', '$timeout', ($rootScope, $timeout) =>
    ({
    require: 'ngModel',
    restrict: 'A',
    scope: {
      region: '@dpPhoneNumberNoExt'
    },
    link(scope, element, attr, ngModel) {
      // when we get the dpPhoneNumber attribute value, setup intlTelInput
      return attr.$observe('dpPhoneNumberNoExt', function(reg) {
        element.intlTelInput({
          defaultCountry: reg.toLowerCase(),
          autoPlaceholder: true,
          utilsScript: window.DP_ASSET_URL.replace(/web\//, 'pub/') + 'build/phonenumber_utils.js',
          nationalMode: true
        });

        $timeout(() => element.intlTelInput("setNumber", element.val())
        , 1);

        element.intlTelInput('utilsLoaded');
        return element.bind('blur keyup change input', () =>
          scope.$apply(() => ngModel.$setViewValue(element.intlTelInput('getNumber')))
        );
      });
    }


    })
  
  ];

  return Admin_Main_Directive_DpPhoneNumberNoExt;
});
