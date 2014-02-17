(function() {
  define(['DeskPRO/Directive/DpTimeWithUnit', 'DeskPRO/Directive/DpStateMark', 'DeskPRO/Directive/DpHelpPage', 'Admin/Main/Directive/DpNavSubnav', 'Reports/Directive/DpReportBuilderSelectBox', 'Reports/Directive/DpReportBillingSelectBox', 'Reports/Directive/DpReportBuilderTitle', 'Admin/Main/Directive/DpTabBody', 'Admin/Main/Directive/DpTabBtn', 'Admin/Main/Directive/DpHideSpinning', 'Admin/Main/Directive/DpShowSpinning', 'Admin/Main/Directive/DpSubmitForm', 'Admin/Main/Directive/DpErrorClass'], function(DeskPRO_Directive_DpTimeWithUnit, DeskPRO_Directive_DpStateMark, DeskPRO_Directive_DpHelpPage, Admin_Main_Directive_DpNavSubnav, Reports_Directive_DpReportBuilderSelectBox, Reports_Directive_DpReportBillingSelectBox, Reports_Directive_DpReportBuilderTitle, Admin_Main_Directive_DpTabBody, Admin_Main_Directive_DpTabBtn, Admin_Main_Directive_DpHideSpinning, Admin_Main_Directive_DpShowSpinning, Admin_Main_Directive_DpSubmitForm, Admin_Main_Directive_DpErrorClass) {
    return function(Module) {
      Module.directive('dpTimeWithUnit', DeskPRO_Directive_DpTimeWithUnit);
      Module.directive('dpStateMark', DeskPRO_Directive_DpStateMark);
      Module.directive('dpHelpPage', DeskPRO_Directive_DpHelpPage);
      Module.directive('dpNavSubnav', Admin_Main_Directive_DpNavSubnav);
      Module.directive('dpReportBuilderSelectBox', Reports_Directive_DpReportBuilderSelectBox);
      Module.directive('dpReportBillingSelectBox', Reports_Directive_DpReportBillingSelectBox);
      Module.directive('dpReportBuilderTitle', Reports_Directive_DpReportBuilderTitle);
      Module.directive('dpTabBody', Admin_Main_Directive_DpTabBody);
      Module.directive('dpTabBtn', Admin_Main_Directive_DpTabBtn);
      Module.directive('dpHideSpinning', Admin_Main_Directive_DpHideSpinning);
      Module.directive('dpShowSpinning', Admin_Main_Directive_DpShowSpinning);
      Module.directive('dpSubmitForm', Admin_Main_Directive_DpSubmitForm);
      return Module.directive('dpErrorClass', Admin_Main_Directive_DpErrorClass);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupDirectives.js.map
*/