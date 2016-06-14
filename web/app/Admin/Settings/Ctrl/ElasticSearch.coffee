define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Settings_Ctrl_ElasticSearch extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_ElasticSearch'
    @CTRL_AS   = 'Settings'
    @DEPS      = ['$timeout']

    init: ->
      @pollTimer = null
      @hasInit = false
      return

    initialLoad: ->
      return @updateStatus()

    startStatusPoller: ->
      if @pollTimer
        @$timeout.cancel(@pollTimer)
        @pollTimer = null

      @pollTimer = @$timeout(=>
        @updateStatus()
      , 1500)

    updateStatus: ->
      @Api.sendGet('/elastic-search/index-status').then (res) =>
        @$scope.status         = res.data
        @$scope.indexer_status = res.data?.indexer_status
        @$scope.indexer_log    = res.data?.indexer_log
        @$scope.info           = res.data?.info
        @startStatusPoller() if @$scope.status.is_indexing

      @Api.sendGet('/elastic-search/settings').then (res) =>
        return if @hasInit
        @$scope.settings       = res.data.elastic_settings
        @$scope.was_on         = @$scope.settings.enabled
        @hasInit = true

    saveSettings: ->
      @startSpinner('saving')
      postData = { elastic_settings: @$scope.settings }
      @Api.sendPostJson('/elastic-search/settings', postData).success( =>
        @settings = angular.copy(@$scope.settings)

        if @$scope.settings.enabled and !@$scope.was_on
          @$scope.was_on = true
          @$scope.status         = { is_indexing: true }
          @$scope.indexer_status = null
          @$scope.indexer_log    = null

        @updateStatus().then(=>
          @stopSpinner('saving', true)
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      ).error( (info, code) =>
        @Growl.error(info.error_message)
        @stopSpinner('saving', true)
        @$scope.settings.enabled = false
      )

    startReindex: ->
      @showConfirm("Are you sure you want to reset your search index? This will wipe the index and search results will not work until the re-indexing has complete.").result.then(=>
        postData = { elastic_settings: @$scope.settings }
        postData.reindex = true

        @Api.sendPostJson('/elastic-search/settings', postData).success( =>
          @settings = angular.copy(@$scope.settings)

          @$scope.status         = { is_indexing: true }
          @$scope.indexer_status = null
          @$scope.indexer_log    = null

          @updateStatus()
        ).error( (info, code) =>
          @stopSpinner('saving', true)
        )
      )

    ###
      # Show the test account modal
    ###
    testSettingsModal: ->
      loadAccountTest = =>
        postData = {
          url: @$scope.settings.url,
          tika_enabled: @$scope.settings.tika_enabled,
          tika_ip: @$scope.settings.tika_ip,
          tika_port: @$scope.settings.tika_port
        }
        return @Api.sendPostJson('/elastic-search/settings/test', postData)

      inst = @$modal.open({
        templateUrl: @getTemplatePath('ElasticSearch/test-settings-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) =>
          $scope.dismiss = =>
            $modalInstance.dismiss();

          $scope.showLog = =>
            $scope.showing_log = true

          testNow = =>
            $scope.showing_log = false
            $scope.is_testing = true

            loadAccountTest().success( (result) =>
              $scope.is_testing    = false
              $scope.is_success    = result.is_success
              $scope.log           = result.log
            ).error(=>
              $scope.showing_log   = true
              $scope.is_testing    = false
              $scope.is_success    = false
              $scope.log           = "Server Error"
            )

          testNow();

          $scope.testNow = ->
            testNow()
        ]
      });

  Admin_Settings_Ctrl_ElasticSearch.EXPORT_CTRL()