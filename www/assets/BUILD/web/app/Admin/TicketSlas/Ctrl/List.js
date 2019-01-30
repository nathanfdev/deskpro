// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_TicketSlas_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketSlas_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS = ['$state', '$stateParams', 'DataService'];
    }

    init() {
      this.list = [];
      return this.slaData = this.DataService.get('TicketSlas');
    }

    initialLoad() {
      const promise = this.slaData.loadList();
      promise.then( list => {
        return this.list = list;
      });

      return promise;
    }

    /*
     * Show the delete dlg
     */
    startDelete(sla_id) {

      let sla = null;
      for (let v of Array.from(this.list)) {
        if (v.id === sla_id) {
          sla = v;
          break;
        }
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketSlas/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then( () => {
        return this.slaData.deleteSlaById(sla.id).then(() => {
          if ((this.$state.current.name === 'tickets.slas.edit') && (parseInt(this.$state.params.id) === sla.id)) {
            return this.$state.go('tickets.slas');
          }
        });
      });
    }
  }
  Admin_TicketSlas_Ctrl_List.initClass();

  return Admin_TicketSlas_Ctrl_List.EXPORT_CTRL();
});