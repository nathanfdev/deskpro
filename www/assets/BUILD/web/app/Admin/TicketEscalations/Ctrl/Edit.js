define([
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) => {
  class Admin_TicketEscalations_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketEscalations_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams', '$q'];
    }

    init() {
      this.escData = this.DataService.get('TicketEscalations');
      this.esc = null;

      this.criteriaTypeDef     = this.dpObTypesDefTicketFilter;
      this.actionsTypeDef      = this.dpObTypesDefTicketActions;
      this.$scope.criteriaOptionTypes = [];
      this.$scope.actionOptionTypes   = [];

      this.criteriaTypeDef.setVar('object_type', 'escalation');
      this.actionsTypeDef.setVar('object_type', 'escalation');

      this.criteriaTypeDef.setVar('object_type', 'escalation');
      return this.actionsTypeDef.setVar('object_type', 'escalation');
    }

    updateCriteriaOptionTypes() {
      let set = this.criteriaTypeDef.getOptionsForTypes();
      this.$scope.criteriaOptionTypes.length = 0;
      for (var opt of Array.from(set)) {
        this.$scope.criteriaOptionTypes.push(opt);
      }

      set = this.actionsTypeDef.getOptionsForTypes([], { dynamicOptions: this.customActions });
      this.$scope.actionOptionTypes.length = 0;
      for (opt of Array.from(set)) {
        this.$scope.actionOptionTypes.push(opt);
      }

      // filter out usergroup 'Everyone'
      // doesn't make sense to use it in Escalations
      const { options_data } = this.criteriaTypeDef;
      if (options_data != null ? options_data.usergroups : undefined) {
        return options_data.usergroups = options_data.usergroups.filter(group => group.sys_name !== 'everyone');
      }
    }

    initialLoad() {
      let loadData = null;
      const promise = this.escData.loadEditEscalationData(this.$stateParams.id || null).then(data => loadData = data);

      const promise2 = this.criteriaTypeDef.loadDataOptions();
      const promise3 = this.actionsTypeDef.loadDataOptions();
      const promise4 = this.Api.sendDataGet({ customActions: '/ticket_triggers/get-custom-actions' }).then(result => this.customActions = result.data.customActions.action_defs);

      const promises = [promise, promise2, promise3, promise4];

      return this.$q.all(promises).then(() => this.$timeout(() => {
        this.updateCriteriaOptionTypes();
        return this.$timeout(() => {
          this.esc  = loadData.escalation;
          return this.form = loadData.form;
        });
      }));
    }

    saveForm() {
      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.esc.id;

      const promise = this.escData.saveFormModel(this.esc, this.form);

      this.startSpinner('saving');
      return promise.then(() => {
        this.stopSpinner('saving', true).then(() => this.Growl.success('Saved'));

        this.skipDirtyState();
        __guard__(this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined, x => x.loadList());
        if (is_new) {
          return this.$state.go('tickets.ticket_escalations.gocreate');
        }
      });
    }
  }
  Admin_TicketEscalations_Ctrl_Edit.initClass();

  return Admin_TicketEscalations_Ctrl_Edit.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
