define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketUrgencies_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketUrgencies_Ctrl_List'
    @CTRL_AS = 'TicketUrgenciesList'
    @DEPS = []

    init: ->
      @urgency_counts = {}
      @urgencies = []
      return

    initialLoad: ->
      promise = @Api.sendGet("/ticket_urgencies").success( (data) =>
        @urgency_counts = data.urgency_counts

        for num in [1..10]
          @urgencies.push({
            num: num,
            ticket_count: if @urgency_counts[num] then @urgency_counts[num] else 0
          })
      );

      return promise

  Admin_TicketUrgencies_Ctrl_List.EXPORT_CTRL()