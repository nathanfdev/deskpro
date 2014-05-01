define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Settings_Ctrl_ElasticSearch extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Settings_Ctrl_ElasticSearch'
		@CTRL_AS   = 'Settings'

		init: ->
			return

		initialLoad: ->
			@Api.sendDataGet({
				'settings': '/elastic-search/settings'
			}).then( (res) =>
				@$scope.settings = res.data.settings.elastic_settings
				@$scope.was_on = @$scope.settings.enabled
			)

		saveSettings: ->
			@startSpinner('saving')
			postData = { elastic_settings: @$scope.settings }
			@Api.sendPostJson('/elastic-search/settings', postData).success( =>
				@settings = angular.copy(@$scope.settings)

				@stopSpinner('saving').then(=>
					if @$scope.settings.enabled and !@$scope.was_on
						@$scope.settings.requires_reset = true

					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			).error( (info, code) =>
				@stopSpinner('saving', true)
			)

		###
    	# Show the test account modal
		###
		testSettingsModal: ->
			loadAccountTest = =>
				postData = {
					host: @$scope.settings.host,
					port: @$scope.settings.port
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