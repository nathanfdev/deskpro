define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_CustomFields_Base_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_CustomFields_Base_Ctrl_Edit'
    @CTRL_AS = 'EditCtrl'
    @DEPS    = []

    init: ->
      @field_id = parseInt(@$stateParams.id || 0)
      @field_type = '0'
      @field_type_chooser = 'text'
      @currencies = []

      @showFieldType = true
      @showEnabled = true
      @showAgentOnly = true

      @fieldDataService = @getDataService()
      return

    postLoad: ->
      return

    initialLoadExtra: ->
      return

    initialLoad: ->
      promises = []
      promises.push @Api2.sendGet('/currencies').then (response) => @currencies = response.data.data
      promises.push @fieldDataService.loadEditFieldData(@$stateParams.id || null).then( (data) =>
        @field      = data.field
        @field_type = data.field_type
        @form       = data.form
        @postLoad(data)
      )

      p = @initialLoadExtra()
      if p
        promises.push p

      return @$q.all(promises)

    getDataService: ->
      throw new Error("Not implemented")

    getBaseRouteName: ->
      throw new Error("Not implemented")

    postSave: ->
      return

    saveForm: ->
      is_new = !@field.id

      @field.type_name = @field_type
      promise = @fieldDataService.saveFormModel(@field, @form)

      @startSpinner('saving')

      successFn = =>
        @stopSpinner('saving', true).then(=>
          @Growl.success('Saved')
        )

        @skipDirtyState()

        if is_new
          @$state.go(@getBaseRouteName() + ".gocreate")

      promise.success( (data) =>

        if not @field_id
          @field.id = data.field_id
          @field_id = data.field_id

        v = @postSave()
        if v and v.then
          v.then(-> successFn())
        else
          successFn()
      )

      promise.error((info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
        if info.error_message and info.error_message
          @Growl.error info.error_message
      )

    startDelete: ->
      if @field.choices?.length
        message = @getRegisteredMessage 'remove_choices'
        return @$modal.open(
          templateUrl: @getTemplatePath 'Index/modal-alert.html'
          controller:  ['$scope', '$modalInstance', '$state', ($scope, $modalInstance, $state) ->
            $scope.message = message
            $scope.dismiss = -> $modalInstance.dismiss()
          ]
        )

      doDelete = =>
        @fieldDataService.deleteFieldById(@field_id)

      baseRouteName = @getBaseRouteName()
      @$modal.open({
        templateUrl: @getTemplatePath('CustomField/delete-modal.html'),
        controller: ['$scope', '$modalInstance', '$state', ($scope, $modalInstance, $state) ->
          $scope.confirm = ->
            $scope.is_loading =
            doDelete().then(->
              baseParts = baseRouteName.split '.'
              $state.go baseParts[0] + '.' + baseParts[1]
              $modalInstance.dismiss()
            )

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

    type: ->

