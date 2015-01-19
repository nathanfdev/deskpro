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



    updateCriteriaOptionTypes: ->
      set = @criteriaTypeDef.getOptionsForTypes()
      @$scope.criteriaOptionTypes.length = 0
      for opt in set
        @$scope.criteriaOptionTypes.push(opt)

      set = @actionsTypeDef.getOptionsForTypes()
      @$scope.actionOptionTypes.length = 0
      for opt in set
        @$scope.actionOptionTypes.push(opt)



    initialLoad: ->
      loadData = null
      promise = @escData.loadEditSatisfactionEscalation().then (data) =>
        loadData = data

      promise2 = @criteriaTypeDef.loadDataOptions()
      promise3 = @actionsTypeDef.loadDataOptions()

      promises = [promise, promise2, promise3]

      @$q.all(promises).then =>
        @$timeout(=>
          @updateCriteriaOptionTypes()
          @$timeout(=>
            @esc  = loadData.escalation
            @form = loadData.form
          )
        )



    saveForm: ->
      return if not @$scope.form_props.$valid

      is_new = !@esc.id

      promise = @escData.saveFormModel(@esc, @form)

      @startSpinner('saving')
      promise.then( =>
        @stopSpinner('saving', true).then(=>
          @Growl.success("Saved")
        )

        @skipDirtyState()
        if is_new
          @$state.go('tickets.ticket_escalations.gocreate')
      )



  Admin_TicketEscalations_Ctrl_EditSatisfaction.EXPORT_CTRL()