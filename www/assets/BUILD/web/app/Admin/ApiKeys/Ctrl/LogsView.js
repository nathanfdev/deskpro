define([
  'Admin/Main/Ctrl/Base',
  'angular'
], function(
  Admin_Ctrl_Base,
  angular
  ) {

  class Admin_ApiKeys_Ctrl_LogsView extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ApiKeys_Ctrl_LogsView';
      this.CTRL_AS = 'ViewCtrl';
      this.DEPS = ['$stateParams'];
    }

    init() {
      this.service = this.DataService.get('ApiLogs');
      return this.model = {};
    }

    /*
     * Loads the list
     */
    initialLoad() {
      return this.service.get(this.$stateParams.id).then( model => {
        if (model) { this.model = model; }
        return this.service.loadLog(this.$stateParams.id).then( model => {
          return this.model = model;
        });
      });
    }

    getResponseData() {
      return angular.toJson(this.model.response_data, true);
    }

    getRequestData() {
      return angular.toJson(this.model.request_data, true);
    }

    replay() {
      return this.service.replay(this.model, 'subrequest').then(model => {
        return this.$modal.open({
          templateUrl: this.getTemplatePath('ApiLogs/replay-modal.html'),
          size: 'lg',
          controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {

            $scope.model = model;

            $scope.getResponseData = () => angular.toJson($scope.model.response_data, true);

            $scope.getRequestData = () => angular.toJson($scope.model.request_data, true);

            return $scope.dismiss = () => $modalInstance.dismiss();
          }
          ]
        });
      });
    }
  }
  Admin_ApiKeys_Ctrl_LogsView.initClass();


  return Admin_ApiKeys_Ctrl_LogsView.EXPORT_CTRL();
});