define [
  'Admin/Main/DataService/BaseListEdit',
  'Admin/TicketMacros/MacroEditFormMapper',
], (
  BaseListEdit,
  MacroEditFormMapper
)  ->
  class Admin_TicketFilters_DataService_TicketMacros extends BaseListEdit
    @$inject = ['Api', '$q']

    _doLoadList: ->
      deferred = @$q.defer()

      @Api.sendGet('/ticket_macros').success( (data) =>
        models = data.macros
        deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise


    ###
      # Remove a filter
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    deleteMacroById: (id) ->
      promise = @Api.sendDelete('/ticket_macros/' + id).then(=>
        @removeListModelById(id)
      )
      return promise


    ###
      # Get all data needed for the edit filter page
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditMacroData: (id) ->

      deferred = @$q.defer()
      @Api.sendDataGet({
        'macro': (if id then '/ticket_macros/' + id else null)
        'agents': '/agents'
      }).then( (result) =>
        data = {}

        if result.data.macro?.macro?
          data.macro = result.data.macro.macro
          data.macro.person_id = if data.macro.person then data.macro.person.id + "" else result.data.agents.agents[0].id + ""
        else
          data.macro = {
            id: null,
            title: '',
            is_global: true
            person_id: result.data.agents.agents[0].id + ""
          }

        data.agents = result.data.agents.agents
        data.form = @getFormMapper().getFormFromModel(data.macro)
        deferred.resolve(data)
      , ->
        deferred.reject()
      )

      return deferred.promise


    ###
      # Get the form mapper
      #
      # @return {MacroEditFormMapper}
    ###
    getFormMapper: ->
      if @formMapper then return @formMapper
      @formMapper = new MacroEditFormMapper()
      return @formMapper


    ###
      # Saves a form model and applies the form model to the macro model
      # once finished.
      #
      # @param {Object} macroModel The macro model
      # @param {Object} formModel  The model representing the form
      # @return {promise}
    ###
    saveFormModel: (macroModel, formModel) ->
      mapper = @getFormMapper()

      postData = mapper.getPostDataFromForm(formModel)

      if macroModel.id
        promise = @Api.sendPostJson('/ticket_macros/' + macroModel.id, postData)
      else
        promise = @Api.sendPutJson('/ticket_macros', postData).success( (data) ->
          macroModel.id = data.macro_id
        )

      promise.success(=>

        macroModel.title = formModel.title
        macroModel.is_global = formModel.is_global
        macroModel.person = if parseInt(formModel.person_id) then formModel.agents.filter( (x) -> x.id == parseInt(formModel.person_id) )[0] else null
        macroModel.department = if parseInt(formModel.department_id) then formModel.departments.filter( (x) -> x.id == parseInt(formModel.department_id) )[0] else null

        mapper.applyFormToModel(macroModel, formModel)
        @mergeDataModel(macroModel)
      )

      return promise