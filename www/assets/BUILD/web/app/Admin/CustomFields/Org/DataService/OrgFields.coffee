define [
  'Admin/Main/DataService/BaseListEdit',
  'Admin/CustomFields/FieldFormMapper',
], (
  BaseListEdit,
  FieldFormMapper
)  ->
  class OrgFields extends BaseListEdit
    @$inject = ['Api', '$q']

    init: ->

    _doLoadList: ->

      deferred = @$q.defer()

      @Api.sendDataGet([
        '/org_fields'
      ]).then( (res) =>

        custom_fields = []
        for f in res.data.api_org_fields.custom_fields
          custom_fields.push(f)

        deferred.resolve(custom_fields)
      )

      return deferred.promise


    ###
    # Update display orders
    #
    # @param {Array} Array of IDs in order
    # @return {promise}
    ###
    saveDisplayOrder: (display_orders) ->
      @Api.sendPostJson('/org_fields/display-order', {display_orders: display_orders})


    ###
      # Remove a field
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    deleteFieldById: (id) ->
      promise = @Api.sendDelete('/org_fields/' + id).then(=>
        @removeListModelById(id)
      )
      return promise


    ###
      # Get all data needed for the edit field page
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditFieldData: (id) ->
      deferred = @$q.defer()

      if id
        @Api.sendGet("/org_fields/#{id}").then( (result) =>
          data = {}
          data.field = result.data.field
          data.field_type = result.data.field.type_name
          data.form = @getFormMapper().getFormFromModel(data.field)
          deferred.resolve(data)
        )
      else
        data = {
          field: {},
          field_type: '0',
          form: @getFormMapper().getFormFromModel(null)
        }
        deferred.resolve(data)

      return deferred.promise


    ###
      # Get the form mapper
      #
      # @return {FieldFormMapper}
    ###
    getFormMapper: ->
      if @formMapper then return @formMapper
      @formMapper = new FieldFormMapper()
      return @formMapper


    ###
      # Saves a form model and applies the form model to the field model
      # once finished.
      #
      # @param {Object} fieldModel The field model
      # @param {Object} formModel  The model representing the form
      # @return {promise}
    ###
    saveFormModel: (fieldModel, formModel) ->
      mapper = @getFormMapper()
      postData = mapper.getPostDataFromForm(fieldModel.type_name, formModel)

      if fieldModel.id
        promise = @Api.sendPostJson('/org_fields/' + fieldModel.id, postData)
      else
        promise = @Api.sendPutJson('/org_fields', postData).success( (data) ->
          fieldModel.id = data.field_id
        )

      promise.success(=>
        mapper.applyFormToModel(fieldModel, formModel)
        @mergeDataModel(fieldModel)
      )

      return promise