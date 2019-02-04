define([
  'Admin/TicketTriggers/DataService/BaseTriggers'
], (
  BaseTriggers
) => {
  class Admin_TicketTriggers_DataService_TriggersNew extends BaseTriggers {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    init() {
      return this.type = 'newticket';
    }
  }
  Admin_TicketTriggers_DataService_TriggersNew.initClass();
  return Admin_TicketTriggers_DataService_TriggersNew;
});
