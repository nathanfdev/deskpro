(function() {
  define(['DeskPRO/OptionBuilder/Controller'], function(DeskPRO_OptionBuilder_Controller) {
    var DeskPRO_OptionBuilder_DirectiveFactory;
    return DeskPRO_OptionBuilder_DirectiveFactory = (function() {
      function DeskPRO_OptionBuilder_DirectiveFactory() {}

      DeskPRO_OptionBuilder_DirectiveFactory.prototype.getDirective = function() {
        return {
          restrict: 'E',
          require: 'ngModel',
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/OptionBuilder/control.html',
          replace: true,
          controller: DeskPRO_OptionBuilder_Controller.FACTORY,
          controllerAs: 'OptionBuilder',
          scope: {
            getTypesDef: '&typesDef'
          }
        };
      };

      return DeskPRO_OptionBuilder_DirectiveFactory;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=DirectiveFactory.js.map
*/