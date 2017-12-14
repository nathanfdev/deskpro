define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
  class Admin_EmailStatus_Ctrl_ViewSend extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSend'
    @CTRL_AS = 'ViewSource'
    @DEPS    = ['$state', '$modal', 'DpDateService', '$sce']

    init: ->
      @sendmailId = parseInt(@$stateParams.id)
      @$scope.ds = @DpDateService
      @$scope.render_type = 'raw'
      @rendered = {
        summary_loaded: false,
        rendered_loaded: false
      }

      @$scope.$watch('render_type', => @updateRenderType())

      return

    initialLoad: ->
      @Api.sendGet("/email_status/sendmail/#{@sendmailId}?with_raw=1").then( (res) =>
        @sendmail     = res.data.sendmail
        @sendmail_raw = res.data.sendmail_raw
        @statuses     = res.data.statuses
        @log          = res.data.sendmail_log
        @sendmail.date_created = @DpDateService.local @sendmail.date_created
        if @sendmail.date_sent
          @sendmail.date_sent = @DpDateService.local @sendmail.date_sent
        if @sendmail.date_next_attempt
          @sendmail.date_next_attempt = @DpDateService.local @sendmail.date_next_attempt
      )

    updateRenderType: ->
      type = @$scope.render_type
      @$scope.loading_render_type = false

      switch type
        when 'raw' then return
        when 'summary'
          return if @rendered.summary_loaded
          @$scope.loading_render_type = true
          @Api.sendGet("/email_status/sendmail/#{@sendmailId}/summary").success( (data) =>
            @$scope.loading_render_type = false
            @rendered.summary_loaded = true
            @rendered.summary = data.summary
          )
        when 'rendered'
          return if @rendered.rendered_loaded
          @$scope.loading_render_type = true
          @Api.sendGet("/email_status/sendmail/#{@sendmailId}/rendered").success( (data) =>
            @$scope.loading_render_type  = false
            @rendered.rendered_loaded    = true
            @rendered.text               = data.text || null
            @rendered.html               = if data.html then @$sce.trustAsHtml(data.html) else null
          )

    delete: ->
      @Api.sendDelete("/email_status/sendmail/#{@sendmailId}")

    resend: ->
      @Api.sendPost("/email_status/sendmail/#{@sendmailId}/resend")

    startDelete: ->
      doDelete = =>
        @delete().then( =>
          @$state.go('emails.ticket_accounts.sendmailqueue')
        )

      @$modal.open({
        templateUrl: @getTemplatePath('EmailStatus/sendmail-delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.doDelete = ->
            $scope.is_loading = true
            doDelete().then(-> $modalInstance.dismiss())
        ]
      });

    startResend: ->
      doResend = =>
        @resend().then( =>
          @$state.go('emails.ticket_accounts.gosendmailview', {id: @sendmailId})
        )

      @$modal.open({
        templateUrl: @getTemplatePath('EmailStatus/sendmail-resend-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.doResend = ->
            $scope.is_loading = true
            doResend().then(-> $modalInstance.dismiss())
        ]
      });

  Admin_EmailStatus_Ctrl_ViewSend.EXPORT_CTRL()