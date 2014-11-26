define [
  'Admin/Main/Ctrl/Base',
  'Admin/Usersources/Helper/UsersourceTypeDecider'
], (
  Admin_Ctrl_Base,
  Admin_Usersources_Helper_UsersourceTypeDecider
) ->
  class Admin_Usersources_Ctrl_UsersourcesList extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Usersources_Ctrl_UsersourcesList'
    @CTRL_AS = 'ListCtrl'
    @DEPS = ['$state']

    init: ->
      @usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state)
      @usersourcesDataService = @DataService.get('Usersources')
      @show_url = if @usersourceType == 'user' then 'crm.usersources.id' else 'agents.usersources.id'
      @new_url = if @usersourceType == 'user' then 'crm.usersources.new' else 'agents.usersources.new'
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

    initialLoad: ->
      promise = @refresh()

      return promise

    updateAppTitle: (id, title) ->
      @usersources.filter((x) -> x.app?.id == id).map((x) -> x.usersource.title = title)
      @refresh()

    refresh: ->
      @Api.sendGet('/usersources/' + @usersourceType).then((result) =>
        @usersources = result.data.usersources
      )

  Admin_Usersources_Ctrl_UsersourcesList.EXPORT_CTRL()