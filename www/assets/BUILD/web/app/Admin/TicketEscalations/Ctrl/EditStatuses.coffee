define [
  'Admin/TicketEscalations/Ctrl/Edit'
], (
  Admin_TicketEscalations_Ctrl_Edit
) ->
  class Admin_TicketEscalations_Ctrl_EditStatuses extends Admin_TicketEscalations_Ctrl_Edit
    @CTRL_ID   = 'Admin_TicketEscalations_Ctrl_EditStatuses'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams']



    init: ->
      @escData = @DataService.get 'TicketEscalations'
      @esc = null
      @id = @$stateParams.id

      @criteriaTypeDef     = @dpObTypesDefTicketFilter
      @actionsTypeDef      = @dpObTypesDefTicketActions
      @$scope.criteriaOptionTypes = []
      @$scope.actionOptionTypes   = []

      @criteriaTypeDef.setVar 'object_type', 'escalation'
      @actionsTypeDef.setVar 'object_type', 'escalation'

      @criteriaTypeDef.setVar 'object_type', 'escalation'
      @actionsTypeDef.setVar 'object_type', 'escalation'

      @$scope.initWith = (id) =>
        @id = id
        @initialLoad()

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
      return if !@id
      loadData = null
      promise = @escData.loadEditSpecialEscalation('statuses', @id).then (data) =>
        loadData = data

      promise2 = @criteriaTypeDef.loadDataOptions()
      promise3 = @actionsTypeDef.loadDataOptions()
      promise4 = @Api.sendDataGet({customActions: '/ticket_triggers/get-custom-actions'}).then (result) =>
        @customActions = result.data.customActions.action_defs

      promises = [promise, promise2, promise3, promise4]

      @$q.all(promises).then =>
        @updateCriteriaOptionTypes()

        @esc  = loadData.escalation
        @form = loadData.form

        if !@esc.actions.actions? || @esc.actions.actions.length != 1
          return @esc.is_default_action = false
        if 'SendUserNewEmail' == @esc.actions.actions[0].type && 'DeskPRO:emails_user:ticket-awaiting-warn.html.twig' == @esc.actions.actions[0].options.template
          @esc.is_default_action = true
        else if 3 == @esc.sys_num && 'SetStatus' == @esc.actions.actions[0].type && 'resolved' == @esc.actions.actions[0].options.status
          @esc.is_default_action = true
        else if (4 == @esc.sys_num || 5 == @esc.sys_num) && 'SetStatus' == @esc.actions.actions[0].type && 'archived' == @esc.actions.actions[0].options.status
          @esc.is_default_action = true
        else
          @esc.is_default_action = false



    saveForm: ->
      return if @$scope.form_props? and not @$scope.form_props.$valid

      promise = @escData.saveFormModel @esc, @form
      @startSpinner 'saving'
      promise.then =>
        @stopSpinner('saving', true).then => @Growl.success("Saved")
        @skipDirtyState()


  Admin_TicketEscalations_Ctrl_EditStatuses.EXPORT_CTRL()