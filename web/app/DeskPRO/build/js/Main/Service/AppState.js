(function() {
  define(['Admin/Main/Util/EventsMixin'], function(EventsMixin) {
    var AppState;
    return AppState = (function() {
      function AppState($rootScope, $state) {
        this.$rootScope = $rootScope;
        this.$state = $state;
        EventsMixin(this);
        this.vars = {};
      }

      return AppState;

    })();
  });

}).call(this);

//# sourceMappingURL=AppState.js.map
