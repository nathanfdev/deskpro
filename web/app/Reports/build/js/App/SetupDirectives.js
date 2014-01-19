(function() {
  define(['DeskPRO/Directive/DpTimeWithUnit', 'DeskPRO/Directive/DpStateMark'], function(DeskPRO_Directive_DpTimeWithUnit, DeskPRO_Directive_DpStateMark) {
    return function(Module) {
      Module.directive('dpTimeWithUnit', DeskPRO_Directive_DpTimeWithUnit);
      return Module.directive('dpStateMark', DeskPRO_Directive_DpStateMark);
    };
  });

}).call(this);

/*
//@ sourceMappingURL=SetupDirectives.js.map
*/