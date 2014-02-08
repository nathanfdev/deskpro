(function() {
  define(['DeskPRO/Directive/DpTimeWithUnit', 'DeskPRO/Directive/DpStateMark', 'DeskPRO/Directive/DpHelpPage', 'Admin/Main/Directive/DpNavSubnav', 'Reports/Directive/DpReportBuilderSelectBox', 'Reports/Directive/DpReportBuilderTitle'], function(DeskPRO_Directive_DpTimeWithUnit, DeskPRO_Directive_DpStateMark, DeskPRO_Directive_DpHelpPage, Admin_Main_Directive_DpNavSubnav, Reports_Directive_DpReportBuilderSelectBox, Reports_Directive_DpReportBuilderTitle) {
    return function(Module) {
      Module.directive('dpTimeWithUnit', DeskPRO_Directive_DpTimeWithUnit);
      Module.directive('dpStateMark', DeskPRO_Directive_DpStateMark);
      Module.directive('dpHelpPage', DeskPRO_Directive_DpHelpPage);
      Module.directive('dpNavSubnav', Admin_Main_Directive_DpNavSubnav);
      Module.directive('dpReportBuilderSelectBox', Reports_Directive_DpReportBuilderSelectBox);
      return Module.directive('dpReportBuilderTitle', Reports_Directive_DpReportBuilderTitle);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupDirectives.js.map
*/