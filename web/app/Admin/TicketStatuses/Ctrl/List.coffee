define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketStatuses_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketStatuses_Ctrl_List'
    @CTRL_AS = 'TicketStatusesList'
    @DEPS = []

    init: ->
      @stats = {}
      return

    initialLoad: ->
      @Api.sendGet('/ticket_statuses/stats').then( (res) =>
        @stats = res.data.status_stats
      )

    getStatusCount: (status) ->
      return @stats[status] || 0

  Admin_TicketStatuses_Ctrl_List.EXPORT_CTRL()