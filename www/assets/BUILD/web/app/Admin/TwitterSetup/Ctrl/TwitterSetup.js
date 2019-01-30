define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_TwitterSetup_Ctrl_TwitterSetup extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TwitterSetup_Ctrl_TwitterSetup'
    @CTRL_AS   = 'TwitterSetup'
    @DEPS      = []

    init: ->
      @setup = null

      @$scope.times = [
        {id: 86400, label: "1 day"},
        {id: 259200, label: "3 days"},
        {id: 432000, label: "5 days"},
        {id: 604800, label: "1 week"},
        {id: 1209600, label: "2 weeks"},
        {id: 1814400, label: "3 weeks"},
        {id: 2592000, label: "1 month"},
        {id: 5184000, label: "2 months"},
        {id: 7776000, label: "3 months"},
        {id: 15552000, label: "6 months"},
        {id: 23328000, label: "9 months"},
        {id: 31536000, label: "1 year"},
        {id: 63072000, label: "2 years"}
      ]

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'twitter_setup': '/twitter_setup'
      }).then( (res) =>
        @$scope.setup = res.data.twitter_setup.twitter_setup
        @setup = angular.copy(@$scope.setup)
      )

      return @$q.all([data_promise])

    isDirtyState: ->
      if not @setup then return false
      if not angular.equals(@setup, @$scope.setup)
        return true
      else
        return false

    save: ->

      if not @$scope.form_props.$valid
        return

      postData = {
        twitter_setup: @$scope.setup
      }

      @startSpinner('saving')
      promise = @Api.sendPostJson('/twitter_setup', postData).success( =>
        @setup = angular.copy(@$scope.setup)

        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_setup'))
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_TwitterSetup_Ctrl_TwitterSetup.EXPORT_CTRL()