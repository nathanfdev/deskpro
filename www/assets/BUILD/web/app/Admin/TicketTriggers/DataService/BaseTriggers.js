// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit'
], function(
  BaseListEdit,
)  {
  let Admin_TicketTriggers_DataService_BaseTriggers;
  return Admin_TicketTriggers_DataService_BaseTriggers = (function() {
    Admin_TicketTriggers_DataService_BaseTriggers = class Admin_TicketTriggers_DataService_BaseTriggers extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      _doLoadList() {
        const deferred = this.$q.defer();

        this.Api.sendGet(`/ticket_triggers/${this.type}`).success( data => {
          const models = data.triggers;
          this.department_triggers_enabled   = data.department_triggers_enabled || false;
          this.emailaccount_triggers_enabled = data.emailaccount_triggers_enabled || false;
          return deferred.resolve(models);
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }

      /*
        * Get all data needed for the edit filter page
        *
        * @param {Integer} id Filter id
        * @return {promise}
      */
      loadEditTriggerData(id) {

        const deferred = this.$q.defer();

        this.Api.sendGet(`/ticket_triggers/${id}`).then( result =>
          deferred.resolve({
            trigger: result.data.trigger
          })
        
        , () => deferred.reject());

        return deferred.promise;
      }


      /*
        * Save the enabled state of a trigger
        *
        * @param {Integer} triggerId
        * @param {bool} isEnabled
        * @return {promise}
      */
      saveEnabledStateById(triggerId, isEnabled) {
        if (isEnabled) {
          return this.Api.sendPost(`/ticket_triggers/${triggerId}/enable`);
        } else {
          return this.Api.sendPost(`/ticket_triggers/${triggerId}/disable`);
        }
      }


      /*
        * Deletes a trigger
        *
        * @param {Integer} triggerId
        * @return {promise}
      */
      deleteTriggerById(triggerId) {
        this.removeListModelById(triggerId);
        return this.Api.sendDelete(`/ticket_triggers/${triggerId}`);
      }

      /*
        * Save order of triggers
        *
        * @param {Array} orders Array of IDs, in order
        * @return {promise}
      */
      saveRunOrder(orders) {
        for (let idx = 0; idx < orders.length; idx++) {
          const id = orders[idx];
          const model = this.findListModelById(id);
          if (model) {
            model.display_order = idx;
          }
        }

        const promise = this.Api.sendPostJson('/ticket_triggers/run_order', { run_orders: orders });
        return promise;
      }


      /*
        * Save the enabled state of a trigger
        *
        * @param {Integer} triggerId
        * @param {bool} isEnabled
        * @return {promise}
      */
      saveGroupEnabledState(type, isEnabled) {
        let promise;
        const verb = isEnabled ? 'enable' : 'disable';

        if ((type === 'departments') && (this.type === 'newticket')) {
          promise = this.Api.sendPost(`/ticket_triggers/departments/${verb}`);
          this.department_triggers_enabled = isEnabled;
        }
        if ((type === 'departments') && (this.type === 'update')) {
          promise = this.Api.sendPost(`/ticket_triggers/departments_changed/${verb}`);
          this.department_triggers_enabled = isEnabled;
        }
        if ((type === 'email_accounts') && (this.type === 'newticket')) {
          promise = this.Api.sendPost(`/ticket_triggers/email_accounts/${verb}`);
          this.emailaccount_triggers_enabled = isEnabled;
        }

        return promise;
      }
    };
    Admin_TicketTriggers_DataService_BaseTriggers.initClass();
    return Admin_TicketTriggers_DataService_BaseTriggers;
  })();
});