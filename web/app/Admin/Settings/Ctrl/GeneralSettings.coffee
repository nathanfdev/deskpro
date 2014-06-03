define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Settings_Ctrl_GeneralSettings extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Settings_Ctrl_GeneralSettings'
		@CTRL_AS   = 'Settings'
		@DEPS      = []

		init: ->
			@settings = {
				default_timezone: 'UTC',
				task_reminder_time: '09:30',
				attach_agent_must_exts: [],
				attach_agent_not_exts: [],
				attach_user_must_exts: [],
				attach_user_not_exts: []
			}
			@$scope.settings = @settings

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'settings': '/general_settings',
				'email_accounts':  '/email_accounts',
			}).then( (res) =>
				@$scope.settings = res.data.settings.general_settings
				@$scope.maxUploadSize = res.data.settings.max_filesize
				@settings = angular.copy(@$scope.settings)

				@$scope.email_accounts = res.data.email_accounts.email_accounts
				@$scope.email_accounts = @$scope.email_accounts.filter( (x) -> x.outgoing_account_type != null)

				if @$scope.email_accounts.length
					if not @$scope.settings.default_from_email or not @$scope.email_accounts.filter((x) => x.address == @$scope.settings.default_from_email).length
						@$scope.settings.default_from_email = @$scope.email_accounts[0].address
				
				if @settings.attach_user_must_exts.length
					@$scope.attach_user_exts_limitmode = 'allow'
				else if @settings.attach_user_not_exts.length
					@$scope.attach_user_exts_limitmode = 'disallow'
				else
					@$scope.attach_user_exts_limitmode = 'any'

				if @settings.attach_agent_must_exts.length
					@$scope.attach_agent_exts_limitmode = 'allow'
				else if @settings.attach_agent_not_exts.length
					@$scope.attach_agent_exts_limitmode = 'disallow'
				else
					@$scope.attach_agent_exts_limitmode = 'any'
			)

			return @$q.all([data_promise])

		isDirtyState: ->
			if not @settings then return false
			if not angular.equals(@settings, @$scope.settings)
				return true
			else
				return false

		save: ->
			if @$scope.attach_user_exts_limitmode == 'allow'
				@$scope.settings.attach_user_not_exts = []
			else if @$scope.attach_user_exts_limitmode == 'disallow'
				@$scope.settings.attach_user_must_exts = []
			else
				@$scope.settings.attach_user_not_exts = []
				@$scope.settings.attach_user_must_exts = []

			if @$scope.attach_agent_exts_limitmode == 'allow'
				@$scope.settings.attach_agent_not_exts = []
			else if @$scope.attach_agent_exts_limitmode == 'disallow'
				@$scope.settings.attach_agent_must_exts = []
			else
				@$scope.settings.attach_agent_not_exts = []
				@$scope.settings.attach_agent_must_exts = []
			
			postData = {
				general_settings: @$scope.settings
			}

			@startSpinner('saving')
			promise = @Api.sendPostJson('/general_settings', postData).success( =>
				@settings = angular.copy(@$scope.settings)

				@stopSpinner('saving').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			).error( (info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

	Admin_Settings_Ctrl_GeneralSettings.EXPORT_CTRL()