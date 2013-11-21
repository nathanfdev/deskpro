define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_TwitterSetup_Ctrl_TwitterSetup extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TwitterSetup_Ctrl_TwitterSetup'
		@CTRL_AS   = 'TwitterSetup'
		@DEPS      = []

		init: ->
			@setup = null

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