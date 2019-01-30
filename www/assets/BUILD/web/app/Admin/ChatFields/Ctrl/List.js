define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ChatFields_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ChatFields_Ctrl_List'
    @CTRL_AS = 'ChatFieldsList'
    @DEPS    = []

    init: ->
      @fieldDataService = @DataService.get('ChatFields')
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
      return

    initialLoad: ->
      promise = @fieldDataService.loadList()
      promise.then( (list) =>
        @custom_fields = list
      )

      return promise

  Admin_ChatFields_Ctrl_List.EXPORT_CTRL()