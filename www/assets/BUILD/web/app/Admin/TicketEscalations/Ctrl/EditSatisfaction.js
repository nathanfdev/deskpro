define([
  'Admin/TicketEscalations/Ctrl/Edit'
], (
  Admin_TicketEscalations_Ctrl_Edit
) => {
  class Admin_TicketEscalations_Ctrl_EditSatisfaction extends Admin_TicketEscalations_Ctrl_Edit {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketEscalations_Ctrl_EditSatisfaction';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams'];
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
      this.actionsTypeDef.setVar('object_type', 'escalation');

      const growl = this.Growl;
      // we need to suppress alerts when process initiated by this event
      return this.$scope.$on('trigger.save', () => {
        this.Growl = {
          success: () => {},
          error:   () => {}
        };
            // right, double 'then'
        return this.saveForm().then().then(() => this.Growl = growl);
      });
    }


    updateCriteriaOptionTypes() {
      let set = this.criteriaTypeDef.getOptionsForTypes();
      this.$scope.criteriaOptionTypes.length = 0;
      for (var opt of Array.from(set)) {
        this.$scope.criteriaOptionTypes.push(opt);
      }

      set = this.actionsTypeDef.getOptionsForTypes([], { dynamicOptions: this.customActions });
      this.$scope.actionOptionTypes.length = 0;
      return (() => {
        const result = [];
        for (opt of Array.from(set)) {
          result.push(this.$scope.actionOptionTypes.push(opt));
        }
        return result;
      })();
    }


    initialLoad() {
      let loadData = null;
      const promise = this.escData.loadEditSpecialEscalation('satisfaction', 0).then(data => loadData = data);

      const promise2 = this.criteriaTypeDef.loadDataOptions();
      const promise3 = this.actionsTypeDef.loadDataOptions();
      const promise4 = this.Api.sendDataGet({ customActions: '/ticket_triggers/get-custom-actions' }).then(result => this.customActions = result.data.customActions.action_defs);
      const promises = [promise, promise2, promise3, promise4];

      return this.$q.all(promises).then(() => this.$timeout(() => {
        this.updateCriteriaOptionTypes();
        return this.$timeout(() => {
          this.esc  = loadData.escalation;
          this.form = loadData.form;

          return this.$scope.$watch(
              () => (this.$scope.settings != null ? this.$scope.settings.satisfaction_enabled : undefined) && this.esc.is_enabled,
              (val) => {
                if (undefined === val) { return; }
                return this.escData.saveEnabledStateById(this.esc.id, __guard__(this.$scope.$parent != null ? this.$scope.$parent.settings : undefined, x => x.satisfaction_enabled) && this.esc.is_enabled);
              });
        });
      }));
    }
  }
  Admin_TicketEscalations_Ctrl_EditSatisfaction.initClass();


  return Admin_TicketEscalations_Ctrl_EditSatisfaction.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
