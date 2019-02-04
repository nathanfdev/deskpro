define([
  'Admin/TicketTriggers/DataService/BaseTriggers'
], (
  BaseTriggers
) => {
  class Admin_TicketTriggers_DataService_TriggersUpdate extends BaseTriggers {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    init() {
      return this.type = 'update';
    }
  }
  Admin_TicketTriggers_DataService_TriggersUpdate.initClass();
  return Admin_TicketTriggers_DataService_TriggersUpdate;
});
