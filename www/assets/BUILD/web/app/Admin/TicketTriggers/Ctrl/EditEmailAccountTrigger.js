// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Arrays',
  'Admin/Main/Ctrl/Base',
  'Admin/TicketTriggers/TriggerEditFormMapper',
  'Admin/TicketTriggers/Ctrl/EditBase',
], function(
  Arrays,
  Admin_Ctrl_Base,
  TriggerEditFormMapper,
  Admin_TicketTriggers_Ctrl_EditBase
) {
  class Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger extends Admin_TicketTriggers_Ctrl_EditBase {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger';
      this.CTRL_AS   = 'TicketTriggersEdit';
      this.DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];
    }

    customInit() {
      this.triggerId = 0;
      this.accountId = this.$stateParams.id.replace(/^emailaccount\-(\d+)$/, '$1');

      this.$scope.triggerType = this.$stateParams.type;
      this.$scope.triggerId   = 0;
      return this.$scope.acountId    = this.accountId;
    }

    /*
     * Load the trigger
     */
    initialLoad() {
      const get = {
        customActions: '/ticket_triggers/get-custom-actions',
        accInfo:       `/email_accounts/${this.accountId}`,
        trigger:       `/ticket_triggers/email_accounts/${this.accountId}`
      };

      const promise = this.Api.sendDataGet(get).then( result => {

        this.customActions = result.data.customActions.action_defs;

        if (__guard__(result.data != null ? result.data.trigger : undefined, x => x.trigger) != null) {
          this.trigger = result.data.trigger.trigger;
          this.triggerId = this.trigger.id;
        } else {
          this.trigger = {};
          this.triggerId = 0;
        }

        this.account = result.data.accInfo.email_account;
        return this.$scope.form = this.editFormMapper.getFormFromModel(this.trigger);
      });

      const promise2 = this.criteraTypeDef.loadDataOptions();
      const promise3 = this.actionsTypeDef.loadDataOptions();

      const promises = [promise, promise2, promise3];

      return this.$q.all(promises).then(() => {
        this.updateCriteriaOptionTypes();

        return this.$scope.$watch('form.typeForm', () => {
          return this.updateCriteriaOptionTypes();
        }
        , true);
      });
    }
  }
  Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.initClass();


  return Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}