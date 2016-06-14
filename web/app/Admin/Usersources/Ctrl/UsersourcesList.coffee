define [
  'Admin/Main/Ctrl/Base',
  'Admin/Usersources/Helper/UsersourceTypeDecider',
  'moment'
], (
  Admin_Ctrl_Base,
  Admin_Usersources_Helper_UsersourceTypeDecider,
  moment
) ->
  class Admin_Usersources_Ctrl_UsersourcesList extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Usersources_Ctrl_UsersourcesList'
    @CTRL_AS = 'ListCtrl'
    @DEPS = ['$state', 'Growl', '$q', '$interval']

    init: ->
      @usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state)
      @usersourcesDataService = @DataService.get('Usersources')
      @show_sync_section = false
      @show_url = if @usersourceType == 'user' then 'crm.usersources.id' else 'agents.usersources.id'
      @sync_url = if @usersourceType == 'user' then 'crm.usersources.sync' else 'agents.usersources.sync'
      @new_url = if @usersourceType == 'user' then 'crm.usersources.new' else 'agents.usersources.new'
      @sync_status = null
      @sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')
          displayOrders = []
          $list.find('li').each(->
            displayOrders.push(parseInt($(this).data('id')))
          )
          @usersourcesDataService.saveDisplayOrder(displayOrders)
          @pingElement('display_orders')
      }
      @$scope.$on '$destroy', => @interval && @$interval.cancel(@interval)

    initialLoad: ->
      promise = @refresh()

      @interval = @$interval(() =>
        @refresh()
      , 10000)

      return promise

    updateAppTitle: (id, title) ->
      @usersources.filter((x) -> x.app?.id == id).map((x) -> x.usersource.title = title)
      @refresh()

    refresh: ->
      d = @$q.defer()

      @Api.sendDataGet({
        us: '/usersources/' + @usersourceType,
        sync_status: '/usersources/sync/status'
      }).then((result) =>
        @sync_status = result.data.sync_status
        if @sync_status.next_sync
          @sync_next_text = moment(@sync_status.next_sync).format('MMM D, YYYY @ HH:mm')
        else
          @sync_next_text = 'scheduling'
        @usersources = result.data.us.usersources
        @show_sync_section = true
        count_syncing = 0
        for us in @usersources
          if us.usersource.sync_enabled
            count_syncing++
        if count_syncing
          @show_sync_section = true
        else
          @show_sync_section = false

        d.resolve();
      )

      return d.promise

    startSync: ->
      @Api.sendPost('/usersources/sync/start').then((result) =>
        if result.data.success
          @refresh()
          @Growl.success(@getRegisteredMessage('usersource_sync_starting') || 'Starting sync job. It will begin shortly.')
      )

    stopSync: ->
      @Api.sendPost('/usersources/sync/stop').then((result) =>
        if result.data.success
          @refresh()
          @Growl.success(@getRegisteredMessage('usersource_sync_stopping') || 'Aborted sync jobs.')
      )

  Admin_Usersources_Ctrl_UsersourcesList.EXPORT_CTRL()