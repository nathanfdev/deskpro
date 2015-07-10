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
        filterset_title: null
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
        
        @filterset = data
        @$scope.formstate.filterset_title = data.title
      )

    saveFilterSet: ->
      @filterset.title = @$scope.formstate.filterset_title
      @filterSetData.saveTicketFilterSet(@filterset).then(=>
        @$scope.formstate.editingFilterSetTitle = false
      )

  Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL()
