define [
  'Admin/TicketEscalations/Ctrl/Edit'
], (
  Admin_TicketEscalations_Ctrl_Edit
) ->
  class Admin_TicketEscalations_Ctrl_EditSatisfaction extends Admin_TicketEscalations_Ctrl_Edit
    @CTRL_ID   = 'Admin_TicketEscalations_Ctrl_EditSatisfaction'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams']



    init: ->
      @escData = @DataService.get 'TicketEscalations'
      @esc = null

      @criteriaTypeDef     = @dpObTypesDefTicketFilter
      @actionsTypeDef      = @dpObTypesDefTicketActions
      @$scope.criteriaOptionTypes = []
      @$scope.actionOptionTypes   = []

      @criteriaTypeDef.setVar 'object_type', 'escalation'
      @actionsTypeDef.setVar 'object_type', 'escalation'

      @criteriaTypeDef.setVar 'object_type', 'escalation'
      @actionsTypeDef.setVar 'object_type', 'escalation'

      growl = @Growl
      # we need to suppress alerts when process initiated by this event
      @$scope.$on 'trigger.save', =>
        @Growl =
          success: =>
          error: =>
            # right, double 'then'
        @saveForm().then().then => @Growl = growl



    updateCriteriaOptionTypes: ->
      set = @criteriaTypeDef.getOptionsForTypes()
      @$scope.criteriaOptionTypes.length = 0
      for opt in set
        @$scope.criteriaOptionTypes.push(opt)

      set = @actionsTypeDef.getOptionsForTypes([], {dynamicOptions: @customActions})
      @$scope.actionOptionTypes.length = 0
      for opt in set
        @$scope.actionOptionTypes.push(opt)



    initialLoad: ->
      loadData = null
      promise = @escData.loadEditSpecialEscalation('satisfaction', 0).then (data) =>
        loadData = data

      promise2 = @criteriaTypeDef.loadDataOptions()
      promise3 = @actionsTypeDef.loadDataOptions()
      promise4 = @Api.sendDataGet({customActions: '/ticket_triggers/get-custom-actions'}).then (result) =>
        @customActions = result.data.customActions.action_defs
      promises = [promise, promise2, promise3, promise4]

      @$q.all(promises).then =>
        @$timeout(=>
          @updateCriteriaOptionTypes()
          @$timeout(=>
            @esc  = loadData.escalation
            @form = loadData.form

            @$scope.$watch(
              =>
                @$scope.settings?.satisfaction_enabled && @esc.is_enabled
              (val) =>
                return if undefined == val
                @escData.saveEnabledStateById @esc.id, @$scope.$parent?.settings?.satisfaction_enabled && @esc.is_enabled
            )
          )
        )



  Admin_TicketEscalations_Ctrl_EditSatisfaction.EXPORT_CTRL()