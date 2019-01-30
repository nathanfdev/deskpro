// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let MainLoaderCtrl;
  return MainLoaderCtrl = (function() {
    MainLoaderCtrl = class MainLoaderCtrl {
      static initClass() {
        this.$inject = ['$scope'];
      }
      constructor($scope) {
        this.$scope = $scope;
      }
    };
    MainLoaderCtrl.initClass();
    return MainLoaderCtrl;
  })();
});