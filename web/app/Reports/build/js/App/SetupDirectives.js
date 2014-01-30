(function() {
  define(['DeskPRO/Directive/DpTimeWithUnit', 'DeskPRO/Directive/DpStateMark', 'DeskPRO/Directive/DpHelpPage'], function(DeskPRO_Directive_DpTimeWithUnit, DeskPRO_Directive_DpStateMark, DeskPRO_Directive_DpHelpPage) {
    return function(Module) {
      Module.directive('dpTimeWithUnit', DeskPRO_Directive_DpTimeWithUnit);
      Module.directive('dpStateMark', DeskPRO_Directive_DpStateMark);
      return Module.directive('dpHelpPage', DeskPRO_Directive_DpHelpPage);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupDirectives.js.map
*/