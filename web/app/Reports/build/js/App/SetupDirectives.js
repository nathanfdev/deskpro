(function() {
  define(['DeskPRO/Directive/DpTimeWithUnit', 'DeskPRO/Directive/DpStateMark', 'DeskPRO/Directive/DpHelpPage', 'Admin/Main/Directive/DpNavSubnav', 'Reports/Directive/DpReportBuilderSelectBox', 'Reports/Directive/DpReportBuilderTitle', 'Admin/Main/Directive/DpTabBody', 'Admin/Main/Directive/DpTabBtn'], function(DeskPRO_Directive_DpTimeWithUnit, DeskPRO_Directive_DpStateMark, DeskPRO_Directive_DpHelpPage, Admin_Main_Directive_DpNavSubnav, Reports_Directive_DpReportBuilderSelectBox, Reports_Directive_DpReportBuilderTitle, Admin_Main_Directive_DpTabBody, Admin_Main_Directive_DpTabBtn) {
    return function(Module) {
      Module.directive('dpTimeWithUnit', DeskPRO_Directive_DpTimeWithUnit);
      Module.directive('dpStateMark', DeskPRO_Directive_DpStateMark);
      Module.directive('dpHelpPage', DeskPRO_Directive_DpHelpPage);
      Module.directive('dpNavSubnav', Admin_Main_Directive_DpNavSubnav);
      Module.directive('dpReportBuilderSelectBox', Reports_Directive_DpReportBuilderSelectBox);
      Module.directive('dpReportBuilderTitle', Reports_Directive_DpReportBuilderTitle);
      Module.directive('dpTabBody', Admin_Main_Directive_DpTabBody);
      return Module.directive('dpTabBtn', Admin_Main_Directive_DpTabBtn);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupDirectives.js.map
*/