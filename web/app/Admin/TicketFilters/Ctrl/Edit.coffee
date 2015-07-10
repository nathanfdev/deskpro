define [
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Util'
], (
  Admin_Ctrl_Base,
  Util
) ->
  class Admin_TicketFilters_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketFilters_Ctrl_Edit'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['dpObTypesDefTicketFilter', '$stateParams', '$timeout']

    init: ->
      @filterId = parseInt(@$stateParams.id || 0)
      @filterSetData = @DataService.get('TicketFilterSets')
      @filterData = @DataService.get('TicketFilters')
      @filterset = null
      @filter_criteria = {}
      @criteriaTypeDef = @dpObTypesDefTicketFilter
      @criteriaOptionTypes = @criteriaTypeDef.getOptionsForTypes()
      @$scope.formstate = {
        editingFilterSetTitle: false,
        filterset: {
          title: null,
          is_default: false
        }
      }
      
      @sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')

          orders = []
          $list.find('li').each(->
            id = parseInt($(this).data('id'))

            if id
              orders.push(id)
          )

          @filterData.saveDisplayOrder(orders).then( =>
            @pingElement('display_orders')
          )
      }

    initialLoad: ->
      p = @filterSetData.loadEditFilterSetData(@filterId).then( (data) =>
        if data.filters.length == 0
          @$state.go('tickets.ticket_filters.edit.single_filter', { id: data.id, filter_id: 0 })
        
        console.log data
        
        @filterset = data
        @$scope.formstate.filterset.title = data.title
        @$scope.formstate.filterset.is_default = data.is_default
      )
      
    changedFilterDefault: ->
      @filterset.title = @$scope.formstate.filterset.title
      # This event fires before the custom control changes the model.
      @filterset.is_default = !@$scope.formstate.filterset.is_default
      
      @filterSetData.saveTicketFilterSet(@filterset).then(=>
        # Nothing
      )

    saveFilterSet: ->
      @filterset.title = @$scope.formstate.filterset.title
      @filterset.is_default = @$scope.formstate.filterset.is_default
      
      @filterSetData.saveTicketFilterSet(@filterset).then(=>
        @$scope.formstate.editingFilterSetTitle = false
      )

  Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL()
