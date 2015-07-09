define [
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Util'
], (
  Admin_Ctrl_Base,
  Util
) ->
  class Admin_TicketFilters_Ctrl_EditView extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketFilters_Ctrl_EditView'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['dpObTypesDefTicketFilter', '$stateParams', '$timeout']

    init: ->
      @filterViewId = parseInt(@$stateParams.id || 0)
      @filterViewData = @DataService.get('TicketFilterViews')
      @filterView = null

    initialLoad: ->
      console.log "initialLoad " + @filterId
      p = @filterViewData.loadEditFilterSetData(@filterId).then( (data) =>
        @filterView = data
      )

  Admin_TicketFilters_Ctrl_EditView.EXPORT_CTRL()
