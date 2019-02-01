define([
  'Admin/TicketTriggers/DataService/BaseTriggers'
], function(
  BaseTriggers,
)  {
  class Admin_TicketTriggers_DataService_TriggersReply extends BaseTriggers {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    init() {
      return this.type = 'newreply';
    }
  }
  Admin_TicketTriggers_DataService_TriggersReply.initClass();
  return Admin_TicketTriggers_DataService_TriggersReply;
});