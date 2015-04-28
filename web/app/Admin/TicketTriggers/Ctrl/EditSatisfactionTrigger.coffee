define [
  'DeskPRO/Util/Arrays'
  'Admin/Main/Ctrl/Base',
  'Admin/TicketTriggers/TriggerEditFormMapper',
  'Admin/TicketTriggers/Ctrl/EditBase',
], (
  Arrays,
  Admin_Ctrl_Base,
  TriggerEditFormMapper,
  Admin_TicketTriggers_Ctrl_EditBase
) ->
  class Admin_TicketTriggers_Ctrl_EditSatisfactionTrigger extends Admin_TicketTriggers_Ctrl_EditBase
    @CTRL_ID   = 'Admin_TicketTriggers_Ctrl_EditSatisfactionTrigger'
    @CTRL_AS   = 'TicketTriggersEdit'
    @DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions']

    init: ->
      @$scope.triggerType = @triggerType = 'update'
      if @$stateParams.id then @id = @$stateParams.id.replace 'satisfaction-', ''

      @trigger     = null
      @$scope.triggerId = @triggerId   = 0
      @options     = {}
      @editFormMapper = new TriggerEditFormMapper()
      @mode = null
      @appTriggerEvents = []

      @$scope.form = @editFormMapper.getFormFromModel({})

      @dpTriggers = @DataService.get 'TriggersUpdate'
      @criteraTypeDef = @dpObTypesDefTicketCriteria
      @actionsTypeDef = @dpObTypesDefTicketActions

      @$scope.criteriaOptionTypes = []
      @$scope.actionOptionTypes = []

      @criteraTypeDef.setVar 'object_type', 'trigger'
      @actionsTypeDef.setVar 'object_type', 'trigger'

      @$scope.types =
        0: 'negative'
        1: 'neutral'
        2: 'positive'

      @$scope.setCtrlParams = (id) =>
        @id = id
        @initialLoad()

      growl = @Growl
      # we need to suppress alerts when process initiated by this event
      @$scope.$on 'trigger.save', =>
        @Growl =
          success: =>
          error: =>
        # right, double 'then'
        @saveTrigger().then().then => @Growl = growl


    ###
    # Load the trigger
    ###
    initialLoad: ->
      return if !@id?
      get =
        customActions: '/ticket_triggers/get-custom-actions',
        trigger:       "/ticket_triggers/satisfaction/#{@id}"

      promise = @Api.sendDataGet(get).then (result) =>
        @customActions = result.data.customActions.action_defs

        if result.data?.trigger?.trigger?
          @trigger = result.data.trigger.trigger
          @triggerId = @trigger.id
          @$scope.$watch(
            => @trigger.is_enabled
            (val) =>
              return if undefined == val
              @dpTriggers.saveEnabledStateById @trigger.id, @trigger.is_enabled
          )
        else
          @trigger = {}
          @triggerId = 0

        @$scope.form = @editFormMapper.getFormFromModel(@trigger)

      promise2 = @actionsTypeDef.loadDataOptions()

      @$q.all([promise, promise2]).then => @updateCriteriaOptionTypes()



  Admin_TicketTriggers_Ctrl_EditSatisfactionTrigger.EXPORT_CTRL()