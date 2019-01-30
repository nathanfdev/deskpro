define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_OrgFields_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_OrgFields_Ctrl_List'
    @CTRL_AS = 'ListCtrl'
    @DEPS    = []

    init: ->
      @fieldDataService = @DataService.get('OrgFields')
      @custom_fields = []
      @sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')
          displayOrders = []

          $list.find('li').each(->
            displayOrders.push(parseInt($(this).data('id')))
          )

          @fieldDataService.saveDisplayOrder(displayOrders)
          @pingElement('display_orders')
      }

      @specific_org_custom_fields = []
      data = @$state.current.data
      @service = @DataService.get 'CustomFields', data.owner, data.context
      @customFieldListOptions =
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest 'ul'
          displayOrders = []

          $list.find('li').each -> displayOrders.push parseInt $(this).data('id')

          @service.saveDisplayOrder displayOrders
          @pingElement 'display_orders'

      return

    initialLoad: ->
      promise = @fieldDataService.loadList()
      promise.then( (list) =>
        @custom_fields = list
      )
      @service.all().then (list) => @specific_org_custom_fields = list

      return promise

  Admin_OrgFields_Ctrl_List.EXPORT_CTRL()