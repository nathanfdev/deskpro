define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketStatuses_Ctrl_EditArchived extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditArchived'
    @CTRL_AS = 'TicketStatusEdit'
    @DEPS = []

    init: ->
      @$scope.getCount = => @$scope.$parent.TicketStatusesList?.getStatusCount('archived')
      @$scope.settings = {
        enabled: false,
        auto_archive_time: 2419200
      }
      return

    initialLoad: ->
      promise = @Api.sendGet("/ticket_statuses/archived").success( (data) =>
        @$scope.settings.enabled = data.archived_info.enabled
        @$scope.settings.auto_archive_time = parseInt(data.archived_info.auto_archive_time)
      );

      return promise

    saveSettings: ->
      @startSpinner('saving_settings')
      promise = @Api.sendPostJson('/ticket_statuses/archived/settings', @$scope.settings).then( =>
        @stopSpinner('saving_settings')
      )
      return promise

    resetSearchTables: ->
      @startSpinner('is_resetting')
      @Api.sendPost('/ticket_statuses/archived/reset-search-tables').then(=>
        @$scope.reset_done = true
        @stopSpinner('is_resetting')
      , =>
        @stopSpinner('is_resetting')
      )

  Admin_TicketStatuses_Ctrl_EditArchived.EXPORT_CTRL()