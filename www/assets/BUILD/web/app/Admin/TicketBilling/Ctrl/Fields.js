define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketBilling_Ctrl_Fields extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketBilling_Ctrl_Fields'
    @CTRL_AS = 'ListCtrl'
    @DEPS    = []



    init: ->
      @fieldDataService = @DataService.get('BillingFields')
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

      data = @$state.current.data
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
      promise.then (list) => @custom_fields = list

      return promise



  Admin_TicketBilling_Ctrl_Fields.EXPORT_CTRL()