// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/TicketTriggers/DataService/BaseTriggers'
], function(
  BaseTriggers,
)  {
  let Admin_TicketTriggers_DataService_TriggersUpdate;
  return Admin_TicketTriggers_DataService_TriggersUpdate = (function() {
    Admin_TicketTriggers_DataService_TriggersUpdate = class Admin_TicketTriggers_DataService_TriggersUpdate extends BaseTriggers {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      init() {
        return this.type = 'update';
      }
    };
    Admin_TicketTriggers_DataService_TriggersUpdate.initClass();
    return Admin_TicketTriggers_DataService_TriggersUpdate;
  })();
});