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
      @permType = 'global'
      @macro  = null
      @agents = null
      @departments = null
      @form   = null
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
      promises = []
      promise = @macroData.loadEditMacroData(@macroId || null).then( (data) =>
        @macro  = data.macro
        @agents = data.agents
        @form   = data.form

        switch
          when @form.is_global then @permType = 'global'
          when @form.person_id then @permType = 'agent'
          when @form.department_id then @permType = 'department'
      )
      promises.push(promise)

      promise = @Api2.sendGet('/ticket_departments?selectable=1')
      promise.then (res) =>
        @departments = res.data.data
      promises.push(promise)

      return @$q.all(promises)

    changePermType: ->
      @form.is_global = 0
      @form.person_id = null
      @form.department_id = null

      switch @permType
        when 'global' then @form.is_global = 1
        when 'agent' then @form.person_id = @agents[0].id
        when 'department' then @form.department_id = @departments[0].id

    saveForm: ->
      @form.agents = @agents
      @form.departments = @departments
      promise = @macroData.saveFormModel(@macro, @form)

      @startSpinner('saving')
      promise.then( =>
        @stopSpinner('saving')

        @skipDirtyState()
        if !@macroId
          @$state.go('tickets.macros.gocreate')
      )

  Admin_TicketMacros_Ctrl_Edit.EXPORT_CTRL()