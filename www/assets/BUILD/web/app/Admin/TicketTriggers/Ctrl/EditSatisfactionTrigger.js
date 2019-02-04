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
  class Admin_TicketTriggers_Ctrl_EditSatisfactionTrigger extends Admin_TicketTriggers_Ctrl_EditBase {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketTriggers_Ctrl_EditSatisfactionTrigger';
      this.CTRL_AS   = 'TicketTriggersEdit';
      this.DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];
    }

    init() {
      this.$scope.triggerType = (this.triggerType = 'update');
      if (this.$stateParams.id) { this.id = this.$stateParams.id.replace('satisfaction-', ''); }

      this.trigger     = null;
      this.$scope.triggerId = (this.triggerId   = 0);
      this.options     = {};
      this.editFormMapper = new TriggerEditFormMapper();
      this.mode = null;
      this.appTriggerEvents = [];

      this.$scope.form = this.editFormMapper.getFormFromModel({});

      this.dpTriggers = this.DataService.get('TriggersUpdate');
      this.criteraTypeDef = this.dpObTypesDefTicketCriteria;
      this.actionsTypeDef = this.dpObTypesDefTicketActions;

      this.$scope.criteriaOptionTypes = [];
      this.$scope.actionOptionTypes = [];

      this.criteraTypeDef.setVar('object_type', 'trigger');
      this.actionsTypeDef.setVar('object_type', 'trigger');

      this.dpTriggers.loadList().then(list => this.allTriggers = list);

      this.$scope.types = {
        0: 'negative',
        1: 'neutral',
        2: 'positive'
      };

      this.$scope.setCtrlParams = (id) => {
        this.id = id;
        return this.initialLoad();
      };

      const growl = this.Growl;
      // we need to suppress alerts when process initiated by this event
      return this.$scope.$on('trigger.save', () => {
        this.Growl = {
          success: () => {},
          error:   () => {}
        };
        // right, double 'then'
        return this.saveTrigger().then().then(() => this.Growl = growl);
      });
    }


    /*
     * Load the trigger
     */
    initialLoad() {
      if ((this.id == null)) { return; }
      const get = {
        customActions: '/ticket_triggers/get-custom-actions',
        trigger:       `/ticket_triggers/satisfaction/${this.id}`
      };

      const promise = this.Api.sendDataGet(get).then((result) => {
        this.customActions = result.data.customActions.action_defs;
        if (__guard__(result.data != null ? result.data.trigger : undefined, x => x.trigger) != null) {
          this.trigger = result.data.trigger.trigger;
          this.triggerId = this.trigger.id;
          this.$scope.$watch(
            () => __guard__(this.$scope.$parent != null ? this.$scope.$parent.settings : undefined, x1 => x1.satisfaction_enabled) && this.trigger.is_enabled,
            (val) => {
              if (undefined === val) { return; }
              return this.dpTriggers.saveEnabledStateById(this.trigger.id, __guard__(this.$scope.$parent != null ? this.$scope.$parent.settings : undefined, x1 => x1.satisfaction_enabled) && this.trigger.is_enabled);
            });
        } else {
          this.trigger = {};
          this.triggerId = 0;
        }

        return this.$scope.form = this.editFormMapper.getFormFromModel(this.trigger);
      });

      const promise2 = this.actionsTypeDef.loadDataOptions();

      return this.$q.all([promise, promise2]).then(() => this.updateCriteriaOptionTypes());
    }
  }
  Admin_TicketTriggers_Ctrl_EditSatisfactionTrigger.initClass();


  return Admin_TicketTriggers_Ctrl_EditSatisfactionTrigger.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
