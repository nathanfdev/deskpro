define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_TicketEscalations_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketEscalations_Ctrl_Edit'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams', '$q']

    init: ->
      @escData = @DataService.get('TicketEscalations')
      @esc = null

      @criteriaTypeDef     = @dpObTypesDefTicketFilter
      @actionsTypeDef      = @dpObTypesDefTicketActions
      @$scope.criteriaOptionTypes = []
      @$scope.actionOptionTypes   = []

      @criteriaTypeDef.setVar('object_type', 'escalation')
      @actionsTypeDef.setVar('object_type', 'escalation')

      @criteriaTypeDef.setVar('object_type', 'escalation')
      @actionsTypeDef.setVar('object_type', 'escalation')

    updateCriteriaOptionTypes: ->
      set = @criteriaTypeDef.getOptionsForTypes()
      @$scope.criteriaOptionTypes.length = 0
      for opt in set
        @$scope.criteriaOptionTypes.push(opt)

      set = @actionsTypeDef.getOptionsForTypes([], {dynamicOptions: @customActions})
      @$scope.actionOptionTypes.length = 0
      for opt in set
        @$scope.actionOptionTypes.push(opt)

      # filter out usergroup 'Everyone'
      # doesn't make sense to use it in Escalations
      options_data = @criteriaTypeDef.options_data;
      if options_data?.usergroups
        options_data.usergroups = options_data.usergroups.filter((group) -> group.sys_name != 'everyone');

    initialLoad: ->
      loadData = null
      promise = @escData.loadEditEscalationData(@$stateParams.id || null).then (data) =>
        loadData = data

      promise2 = @criteriaTypeDef.loadDataOptions()
      promise3 = @actionsTypeDef.loadDataOptions()
      promise4 = @Api.sendDataGet({customActions: '/ticket_triggers/get-custom-actions'}).then (result) =>
        @customActions = result.data.customActions.action_defs

      promises = [promise, promise2, promise3, promise4]

      return @$q.all(promises).then(=>
        @$timeout(=>
          @updateCriteriaOptionTypes()
          @$timeout(=>
            @esc  = loadData.escalation
            @form = loadData.form
          )
        )
      )

    saveForm: ->

      if not @$scope.form_props.$valid
        return

      is_new = !@esc.id

      promise = @escData.saveFormModel(@esc, @form)

      @startSpinner('saving')
      promise.then( =>
        @stopSpinner('saving', true).then(=>
          @Growl.success("Saved")
        )

        @skipDirtyState()
        @$scope.$parent?.ListCtrl?.loadList()
        if is_new
          @$state.go('tickets.ticket_escalations.gocreate')
      )

  Admin_TicketEscalations_Ctrl_Edit.EXPORT_CTRL()