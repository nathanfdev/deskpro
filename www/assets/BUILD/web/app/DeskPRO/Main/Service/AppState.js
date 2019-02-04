define(['Admin/Main/Util/EventsMixin'], (EventsMixin) => {
  class AppState {
    constructor($rootScope, $state) {
      this.$rootScope = $rootScope;
      this.$state = $state;
      EventsMixin(this);
      this.vars = {};
    }
  }

  return AppState;
});
