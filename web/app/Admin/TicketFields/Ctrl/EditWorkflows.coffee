define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], (Admin_Ctrl_Base, Arrays) ->
  class Admin_TicketFields_Ctrl_EditWorkflows extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketFields_Ctrl_EditWorkflows'
    @CTRL_AS = 'TicketWorks'
    @DEPS    = []

    init: ->
      @works            = []
      @default_id       = 0
      @agent_required   = false
      @user_required    = false
      return

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'info': '/ticket_works',
        'layouts': '/ticket_layouts/fields/workflow'
      }).then( (res) =>
        @works          = res.data.info.workflows
        @default_id     = res.data.info.default_id
        @agent_required = res.data.info.agent_required
        @user_required  = res.data.info.user_required
        @enabled        = res.data.info.enabled

        @user_layouts  = res.data.layouts.user_layouts
        @agent_layouts = res.data.layouts.agent_layouts
      )

      return data_promise

    save: ->
      if not @works or not @works.length
        @enabled = false

      postData = {
        workflows:      @works,
        default_id:     @default_id,
        user_required:  @user_required,
        agent_required: @agent_required,
        enabled:        @enabled
      }

      @startSpinner('saving')
      promise = @Api.sendPostJson('/ticket_works', postData).success( =>
        @$scope.$parent?.TicketFieldsList?.saveLayoutData('workflow', @user_layouts, @agent_layouts)
        @$scope.$parent?.TicketFieldsList?.setFieldEnabled('workflow', @enabled)
        @settings = angular.copy(@$scope.settings)

        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

    showConvert: (type) ->
      self = @
      @$modal.open({
        templateUrl: @getTemplatePath('TicketFields/convert-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.type = 'Workflow'
          $scope.plural_type = 'workflows'
          $scope.dismiss = -> $modalInstance.dismiss()

          $scope.doConvert = ->
            $scope.is_loading = true
            self.Api.sendPost('/ticket_fields/convert/workflows').then(
              (res) ->
                $scope.is_loading = false
                $scope.dismiss()
                if res.data?.field?.id?
                  ds = self.DataService.get 'TicketFields'
                  ds.mergeDataModel res.data.field
                  self.$state.go 'tickets.fields.edit', {id: res.data.field.id}
              ->
                $scope.is_loading = false
            )
        ]
      });

  Admin_TicketFields_Ctrl_EditWorkflows.EXPORT_CTRL()