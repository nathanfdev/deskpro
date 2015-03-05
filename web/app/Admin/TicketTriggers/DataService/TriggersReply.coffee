define [
  'Admin/TicketTriggers/DataService/BaseTriggers'
], (
  BaseTriggers,
)  ->
  class Admin_TicketTriggers_DataService_TriggersReply extends BaseTriggers
    @$inject = ['Api', '$q']

    init: ->
      @type = 'newreply'