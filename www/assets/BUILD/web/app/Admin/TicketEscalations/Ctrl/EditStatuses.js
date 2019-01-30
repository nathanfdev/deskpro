// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/TicketEscalations/Ctrl/Edit'
], function(
  Admin_TicketEscalations_Ctrl_Edit
) {
  class Admin_TicketEscalations_Ctrl_EditStatuses extends Admin_TicketEscalations_Ctrl_Edit {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketEscalations_Ctrl_EditStatuses';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams'];
    }



    init() {
      this.escData = this.DataService.get('TicketEscalations');
      this.esc = null;
      this.id = this.$stateParams.id;

      this.criteriaTypeDef     = this.dpObTypesDefTicketFilter;
      this.actionsTypeDef      = this.dpObTypesDefTicketActions;
      this.$scope.criteriaOptionTypes = [];
      this.$scope.actionOptionTypes   = [];

      this.criteriaTypeDef.setVar('object_type', 'escalation');
      this.actionsTypeDef.setVar('object_type', 'escalation');

      this.criteriaTypeDef.setVar('object_type', 'escalation');
      this.actionsTypeDef.setVar('object_type', 'escalation');

      this.$scope.initWith = id => {
        this.id = id;
        return this.initialLoad();
      };

      const growl = this.Growl;
      // we need to suppress alerts when process initiated by this event
      return this.$scope.$on('trigger.save', () => {
        this.Growl = {
          success: () => {},
          error: () => {}
        };
            // right, double 'then'
        return this.saveForm().then().then(() => { return this.Growl = growl; });
      });
    }



    updateCriteriaOptionTypes() {
      let set = this.criteriaTypeDef.getOptionsForTypes();
      this.$scope.criteriaOptionTypes.length = 0;
      for (var opt of Array.from(set)) {
        this.$scope.criteriaOptionTypes.push(opt);
      }

      set = this.actionsTypeDef.getOptionsForTypes([], {dynamicOptions: this.customActions});
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
      if (!this.id) { return; }
      let loadData = null;
      const promise = this.escData.loadEditSpecialEscalation('statuses', this.id).then(data => {
        return loadData = data;
      });

      const promise2 = this.criteriaTypeDef.loadDataOptions();
      const promise3 = this.actionsTypeDef.loadDataOptions();
      const promise4 = this.Api.sendDataGet({customActions: '/ticket_triggers/get-custom-actions'}).then(result => {
        return this.customActions = result.data.customActions.action_defs;
      });

      const promises = [promise, promise2, promise3, promise4];

      return this.$q.all(promises).then(() => {
        this.updateCriteriaOptionTypes();

        this.esc  = loadData.escalation;
        this.form = loadData.form;

        if ((this.esc.actions.actions == null) || (this.esc.actions.actions.length !== 1)) {
          return this.esc.is_default_action = false;
        }
        if (('SendUserNewEmail' === this.esc.actions.actions[0].type) && ('DeskPRO:emails_user:ticket-awaiting-warn.html.twig' === this.esc.actions.actions[0].options.template)) {
          return this.esc.is_default_action = true;
        } else if ((3 === this.esc.sys_num) && ('SetStatus' === this.esc.actions.actions[0].type) && ('resolved' === this.esc.actions.actions[0].options.status)) {
          return this.esc.is_default_action = true;
        } else if (((4 === this.esc.sys_num) || (5 === this.esc.sys_num)) && ('SetStatus' === this.esc.actions.actions[0].type) && ('archived' === this.esc.actions.actions[0].options.status)) {
          return this.esc.is_default_action = true;
        } else {
          return this.esc.is_default_action = false;
        }
      });
    }



    saveForm() {
      if ((this.$scope.form_props != null) && !this.$scope.form_props.$valid) { return; }

      const promise = this.escData.saveFormModel(this.esc, this.form);
      this.startSpinner('saving');
      return promise.then(() => {
        this.stopSpinner('saving', true).then(() => this.Growl.success("Saved"));
        return this.skipDirtyState();
      });
    }
  }
  Admin_TicketEscalations_Ctrl_EditStatuses.initClass();


  return Admin_TicketEscalations_Ctrl_EditStatuses.EXPORT_CTRL();
});