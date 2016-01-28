define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_TicketMacros_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketMacros_Ctrl_Edit'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['dpObTypesDefTicketActions', '$stateParams']

    init: ->
      @macroData  = @DataService.get('TicketMacros')
      @$scope.me_id = window.DP_PERSON_ID

      @macroId = parseInt(@$stateParams.id)
      @macro  = null
      @agents = null
      @form   = null

      @actionsTypeDef    = @dpObTypesDefTicketActions
      @actionOptionTypes = @actionsTypeDef.getOptionsForTypes()

      @$scope.$watch('EditCtrl.form.person_id', (id) =>
        id = parseInt(id)
        name = 'unknown agent'
        if id && !isNaN(id)
          a = @agents.filter((a) -> a.id == id)
          if a[0] then name = a[0].display_name

        @$scope.for_agent_name = name
      )

    initialLoad: ->
      promise = @macroData.loadEditMacroData(@macroId || null).then( (data) =>
        @macro  = data.macro
        @agents = data.agents
        @form   = data.form

        console.log(@form)
      )
      return promise

    saveForm: ->
      @form.agents = @agents
      promise = @macroData.saveFormModel(@macro, @form)

      @startSpinner('saving')
      promise.then( =>
        @stopSpinner('saving')

        @skipDirtyState()
        if !@macroId
          @$state.go('tickets.macros.gocreate')
      )

  Admin_TicketMacros_Ctrl_Edit.EXPORT_CTRL()