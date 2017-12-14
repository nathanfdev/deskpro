define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
  class Admin_EmailStatus_Ctrl_ViewSource extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSource'
    @CTRL_AS = 'ViewSource'
    @DEPS    = ['$state', '$modal', 'DpDateService', '$sce']

    init: ->
      @sourceId = parseInt(@$stateParams.id)
      @$scope.ds = @DpDateService
      @$scope.render_type = 'raw'
      @rendered = {
        summary_loaded: false,
        rendered_loaded: false
      }

      @$scope.$watch('render_type', => @updateRenderType())

      @$scope.showStatusHelp = =>
        @$modal.open({
          templateUrl: @getTemplatePath('EmailStatus/emailsource-status-code-modal.html'),
          controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
            $scope.dismiss = ->
              $modalInstance.dismiss()
          ]
        })

      return

    initialLoad: ->
      @Api.sendGet("/email_status/sources/#{@sourceId}?with_raw=1").then( (res) =>
        @source         = res.data.source
        @source_raw     = res.data.source_raw
        @source_log     = res.data.source_log
        @source_info    = res.data.source_info
        @ticket         = res.data.ticket
        @ticket_message = res.data.ticket_message
      )

    updateRenderType: ->
      type = @$scope.render_type
      @$scope.loading_render_type = false

      switch type
        when 'raw' then return
        when 'summary'
          return if @rendered.summary_loaded
          @$scope.loading_render_type = true
          @Api.sendGet("/email_status/sources/#{@sourceId}/summary").success( (data) =>
            @$scope.loading_render_type = false
            @rendered.summary_loaded = true
            @rendered.summary = data.summary
          )
        when 'rendered'
          return if @rendered.rendered_loaded
          @$scope.loading_render_type = true
          @Api.sendGet("/email_status/sources/#{@sourceId}/rendered").success( (data) =>
            @$scope.loading_render_type  = false
            @rendered.rendered_loaded    = true
            @rendered.text               = data.text || null
            @rendered.html               = if data.html then @$sce.trustAsHtml(data.html) else null
          )

    delete: ->
      @Api.sendDelete("/email_status/sources/#{@sourceId}")

    reprocess: ->
      @Api.sendPost("/email_status/sources/#{@sourceId}/reprocess")

    startDelete: ->
      doDelete = =>
        @delete().then( =>
          @$state.go('emails.ticket_accounts.emailsources')
        )

      @$modal.open({
        templateUrl: @getTemplatePath('EmailStatus/emailsource-delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.doDelete = ->
            $scope.is_loading = true
            doDelete().then(-> $modalInstance.dismiss())
        ]
      });

    startReprocess: ->
      doReprocess = =>
        @reprocess().then( =>
          @$state.go('emails.ticket_accounts.goemailsourcesview', {id: @sourceId})
        )

      @$modal.open({
        templateUrl: @getTemplatePath('EmailStatus/emailsource-reprocess-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.doReprocess = ->
            $scope.is_loading = true
            doReprocess().then(-> $modalInstance.dismiss())
        ]
      });

  Admin_EmailStatus_Ctrl_ViewSource.EXPORT_CTRL()