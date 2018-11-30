define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_TicketStatuses_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TicketStatuses_Ctrl_Edit'
    @CTRL_AS   = 'EditCtrl'
    @DEPS = []

    init: ->
      @statusData = @DataService.get('TicketStatuses')
      @status = null
      @form   = {}
      # Statuses translations
      @statusTrans = {}

    initialLoad: ->
      if @$stateParams.id
        @statusData.loadEditStatusData(@$stateParams.id).then(
          (data) =>
            @status = data.status
            @form = {
              title: @status.title
            }
        )
      else
        @status = {}
        @form = {
          title: '',
          status_type: 'awaiting_agent'
        }

    saveForm: ->
      postData = {
        title: @form.title
      }
      if !@status.id
        postData.status_type = @form.status_type

      @startSpinner('saving')
      if @status.id
        is_new = false
        promise = @Api2.sendPutJson("/ticket_statuses/#{@status.id}", postData)
      else
        is_new = true
        promise = @Api2.sendPostJson('/ticket_statuses', postData)

      promise.success( (result) =>
        if !@status.id
          @status.id = result.data.id
          @status.status_type = @form.status_type
        @status.title = @form.title

        @stopSpinner('saving', true).then(=>
          @Growl.success("Saved")
        )

        @statusData.mergeDataModel({
          id: @status.id,
          title: @status.title,
          status_type: @status.status_type
        })

        @skipDirtyState()
        if is_new
          @$state.go('tickets.statuses.edit', {id: @status.id})
      )
      promise.error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
        if info?.errors?.errors
          @Growl.error info.errors.errors[0].message
      )

      return promise

    startDelete: ->
      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketStatuses/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) =>
          $scope.status = @statusTrans[@status.status_type]
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then( =>
        @statusData.deleteStatusById(@status.id).then(=>
          if @$state.current.name == 'tickets.statuses.edit' and parseInt(@$state.params.id) == @status.id
            @$state.go('tickets.statuses')
        )
      )

  Admin_TicketStatuses_Ctrl_Edit.EXPORT_CTRL()