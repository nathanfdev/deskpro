// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_TicketEscalations_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketEscalations_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS = ['$state', '$stateParams', 'DataService'];
    }

    init() {
      this.list = [];
      this.list_satisfaction = [];
      this.list_statuses = [];
      return this.escData = this.DataService.get('TicketEscalations');
    }



    initialLoad() {
      return this.loadList().then(list => {
        if (this.$state.current.name === 'tickets.ticket_escalations') {
          if (this.list[0]) {
            return this.$state.go('tickets.ticket_escalations.edit', {id: this.list[0].id});
          } else {
            return this.$state.go('tickets.ticket_escalations.create');
          }
        }
      });
    }



    loadList() {
      const d = this.$q.defer();
      this.escData.loadList().then(list => {
        this.list.length = 0;
        this.list_satisfaction.length = 0;
        this.list_statuses.length = 0;
        if (!list) { return d.resolve([]); }

        list.map(item => {
          if ('satisfaction' === item.sys_type) {
            return this.list_satisfaction.push(item);
          } else if ('statuses' === item.sys_type) {
            return this.list_statuses.push(item);
          } else {
            return this.list.push(item);
          }
        });
        return d.resolve(list);
      });
      return d.promise;
    }



    /*
     * Show the delete dlg
     */
    startDelete(esc) {
      if ((esc != null ? esc.sys_name : undefined) != null) { return; }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketEscalations/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then( () => {
        return this.escData.deleteEscalationById(esc.id).then(() => {
          this.list = this.list.filter(item => esc.id !== item.id);
          this.list_satisfaction = this.list_satisfaction.filter(item => esc.id !== item.id);
          this.list_statuses = this.list_statuses.filter(item => esc.id !== item.id);
          if ((this.$state.current.name === 'tickets.ticket_escalations.edit') && (parseInt(this.$state.params.id) === esc.id)) {
            return this.$state.go('tickets.ticket_escalations');
          }
        });
      });
    }

    /*
     * Update the enabled state of a esc
     */
    updateEscEnabledState(esc) {
      return this.escData.saveEnabledStateById(esc.id, esc.is_enabled);
    }
  }
  Admin_TicketEscalations_Ctrl_List.initClass();

  return Admin_TicketEscalations_Ctrl_List.EXPORT_CTRL();
});