/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_RoundRobin_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_RoundRobin_Ctrl_List';
      this.DEPS = ['$timeout'];
      this.CTRL_AS = 'ListCtrl';
    }



    init() {
      this.service = this.DataService.get('RoundRobin');
      this.robins = [];
      return this.settings = null;
    }



    initialLoad() {
      this.service.all().then(robins => {
        return this.robins = robins;
      });
      return this.service.getSettings().then(settings => {
        return this.settings = settings;
      });
    }



    save($event) {
      $event.stopImmediatePropagation();

      if (!this.settings.enabled) {
        this.settings.enabled = true;
        return this.service.saveSettings();
      }

      return this.service.checkTriggers().then(data => {

        // need angular timeout to update template message
        return this.$timeout(
          () => {
            this.settings.active_triggers = data.active_triggers;
            this.$scope.$digest();

            if (0 === this.settings.active_triggers) {
              this.settings.enabled = false;
              return this.service.saveSettings();
            }

            const { service } = this;
            const { settings } = this;
            const title = this.getRegisteredMessage('modal_title');
            const msg = this.getRegisteredMessage('modal_message');

            return this.$modal.open({
              templateUrl: this.getTemplatePath('Index/modal-confirm.html'),
              controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {

                $scope.title = title;
                $scope.message = msg;

                $scope.dismiss = () => $modalInstance.dismiss();

                return $scope.confirm = function() {
                  settings.enabled = !settings.enabled;
                  service.saveSettings();
                  return $modalInstance.dismiss();
                };
              }
              ]
            });
          },
          1
        );
      });
    }
  }
  Admin_RoundRobin_Ctrl_List.initClass();


  return Admin_RoundRobin_Ctrl_List.EXPORT_CTRL();
});