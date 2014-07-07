define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], (Admin_Ctrl_Base, Util) ->
	class Admin_Settings_Ctrl_PasswordSettings extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Settings_Ctrl_PasswordSettings'
		@CTRL_AS   = 'Settings'

		init: ->
			@$scope.password_settings = {}
			@$scope.$on 'agent.save', => @saveSettings()

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'settings': '/password_settings'
			}).then( (res) =>
				@$scope.settings = {
					sessions_lifetime: res.data.settings.settings.sessions_lifetime,
					session_keepalive_require_page: res.data.settings.settings.session_keepalive_require_page
				}
				@$scope.agent = res.data.settings.settings.agent
				@$scope.user  = res.data.settings.settings.user

				@$scope.agent.standard_policy = @$scope.agent.min_length == 5 and
					!@$scope.agent.max_age and
					!@$scope.agent.forbid_reuse and
					!@$scope.agent.require_num_uppercase and
					!@$scope.agent.require_num_lowercase and
					!@$scope.agent.require_num_number and
					!@$scope.agent.require_num_symbol

				@$scope.user.standard_policy = @$scope.user.min_length == 5 and
					!@$scope.user.max_age and
					!@$scope.user.forbid_reuse and
					!@$scope.user.require_num_uppercase and
					!@$scope.user.require_num_lowercase and
					!@$scope.user.require_num_number and
					!@$scope.user.require_num_symbol
			)

			return data_promise

		saveSettings: ->
			@startSpinner('saving')

			settings = @$scope.settings
			settings.agent = Util.clone(@$scope.agent, true)
			settings.user  = Util.clone(@$scope.user, true)

			if settings.agent.standard_policy
				settings.agent.min_length = 5
				settings.agent.max_age = 0
				settings.agent.forbid_reuse = false
				settings.agent.require_num_uppercase = 0
				settings.agent.require_num_lowercase = 0
				settings.agent.require_num_number = 0
				settings.agent.require_num_symbol = 0
			if settings.user.standard_policy
				settings.user.min_length = 5
				settings.user.max_age = 0
				settings.user.forbid_reuse = false
				settings.user.require_num_uppercase = 0
				settings.user.require_num_lowercase = 0
				settings.user.require_num_number = 0
				settings.user.require_num_symbol = 0

			delete settings.agent.standard_policy
			delete settings.user.standard_policy

			@Api.sendPostJson('/password_settings', {settings: settings}).success( =>
				@stopSpinner('saving').then(=>
					message = @getRegisteredMessage('saved_settings')
					@Growl.success(message) if message && message.length
				)
			).error( (info, code) =>
				@stopSpinner('saving', true)
			)

	Admin_Settings_Ctrl_PasswordSettings.EXPORT_CTRL()