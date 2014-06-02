define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'DeskPRO/Util/Arrays'], (Admin_Ctrl_Base, Util, Arrays) ->
	class Admin_TicketSettings_Ctrl_FwdSettings extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketSettings_Ctrl_FwdSettings'
		@CTRL_AS   = 'Fwd'
		@DEPS      = []

		init: ->
			@email_accounts = []
			@$scope.$watch('settings.use_account', (accId) =>
				accId = parseInt(accId)
				a = Arrays.find(@email_accounts, (a) -> a.id == accId)
				@$scope.use_account_address = if a then a.address else "noreply@example.com"
			)

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'settings': '/ticket_settings/fwd',
				'accounts': '/email_accounts'
			}).then( (res) =>
				@email_accounts = res.data.accounts.email_accounts
				@$scope.settings = res.data.settings.ticket_fwd_settings
				@settings = Util.clone(@$scope.settings)

				@$scope.settings.use_account = (@$scope.settings.use_account || 0)+""
			)

			return @$q.all([data_promise])

		isDirtyState: ->
			if not @settings then return false
			if not equals.equals(@settings, @$scope.settings)
				return true
			else
				return false

		save: ->
			postData = {
				ticket_fwd_settings: @$scope.settings
			}

			@startSpinner('saving')
			promise = @Api.sendPostJson('/ticket_settings/fwd', postData).success( =>
				@settings = Util.clone(@$scope.settings)

				@stopSpinner('saving').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			).error( (info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

	Admin_TicketSettings_Ctrl_FwdSettings.EXPORT_CTRL()