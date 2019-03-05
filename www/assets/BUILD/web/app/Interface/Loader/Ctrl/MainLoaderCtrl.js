define(function() {
  class MainLoaderCtrl {
    static initClass() {
      this.$inject = ['$scope'];
    }
    constructor($scope) {
      this.$scope = $scope;
    }
  }
  MainLoaderCtrl.initClass();
  return MainLoaderCtrl;
});
