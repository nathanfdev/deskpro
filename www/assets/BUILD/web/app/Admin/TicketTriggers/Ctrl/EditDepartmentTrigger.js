define([
  'DeskPRO/Util/Arrays',
  'Admin/Main/Ctrl/Base',
  'Admin/TicketTriggers/TriggerEditFormMapper',
  'Admin/TicketTriggers/Ctrl/EditBase',
], (
  Arrays,
  Admin_Ctrl_Base,
  TriggerEditFormMapper,
  Admin_TicketTriggers_Ctrl_EditBase
) => {
  class Admin_TicketTriggers_Ctrl_EditDepartmentTrigger extends Admin_TicketTriggers_Ctrl_EditBase {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketTriggers_Ctrl_EditDepartmentTrigger';
      this.CTRL_AS   = 'TicketTriggersEdit';
      this.DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];
    }

    customInit() {
      this.triggerId = 0;
      if (this.$stateParams.id.indexOf('department-changed-') !== -1) {
        this.eventType = 'update';
        this.depId = this.$stateParams.id.replace(/^department\-changed\-(\d+)$/, '$1');
      } else {
        this.eventType = 'newticket';
        this.depId = this.$stateParams.id.replace(/^department\-(\d+)$/, '$1');
      }

      this.$scope.triggerType = this.$stateParams.type;
      this.$scope.triggerId   = 0;
      return this.$scope.depId       = this.depId;
    }

    /*
     * Load the trigger
     */
    initialLoad() {
      const get = {
        customActions: '/ticket_triggers/get-custom-actions',
        depInfo:       `/ticket_deps/${this.depId}`
      };

      if (this.eventType === 'newticket') {
        get.trigger = `/ticket_triggers/departments/${this.depId}`;
      } else {
        get.trigger = `/ticket_triggers/departments_changed/${this.depId}`;
      }

      const promise = this.Api.sendDataGet(get).then((result) => {
        this.customActions = result.data.customActions.action_defs;

        if (__guard__(result.data != null ? result.data.trigger : undefined, x => x.trigger) != null) {
          this.trigger = result.data.trigger.trigger;
          this.triggerId = this.trigger.id;
        } else {
          this.trigger = {};
          this.triggerId = 0;
        }

        this.dep = result.data.depInfo.department;
        return this.$scope.form = this.editFormMapper.getFormFromModel(this.trigger);
      });

      const promise2 = this.criteraTypeDef.loadDataOptions();
      const promise3 = this.actionsTypeDef.loadDataOptions();

      const promises = [promise, promise2, promise3];

      return this.$q.all(promises).then(() => {
        this.updateCriteriaOptionTypes();

        return this.$scope.$watch('form.typeForm', () => this.updateCriteriaOptionTypes()
        , true);
      });
    }
  }
  Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.initClass();


  return Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
