(function() {
  define('Admin/OptionBuilder/TriggerTypesDef', [Admin_OptionBuilder_TriggerTypesDef](function() {
    var Admin_OptionBuilder_TypesDefService;
    return Admin_OptionBuilder_TypesDefService = (function() {
      function Admin_OptionBuilder_TypesDefService($q, Api, dpTemplateManager) {
        this.$q = $q;
        this.Api = Api;
        this.dpTemplateManager = dpTemplateManager;
      }

      /*
      		# Returns a types context
      */


      Admin_OptionBuilder_TypesDefService.prototype.getTypesDef = function(context, options) {
        switch (context) {
          case 'triggers':
            return new Admin_OptionBuilder_TriggerTypesDef(this.$q, this.Api, this.dpTemplateManager, options);
          default:
            throw {
              name: "dpOptionBuilderTypes.invalid_context",
              message: "Invalid types context: " + context
            };
        }
      };

      return Admin_OptionBuilder_TypesDefService;

    })();
  }));

}).call(this);

/*
//@ sourceMappingURL=TypesDefService.js.map
*/