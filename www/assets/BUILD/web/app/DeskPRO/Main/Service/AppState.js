/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Util/EventsMixin'], function(EventsMixin) {
  let AppState;
  return (AppState = class AppState {
    constructor($rootScope, $state) {
      this.$rootScope = $rootScope;
      this.$state = $state;
      EventsMixin(this);
      this.vars = {};
    }
  });
});