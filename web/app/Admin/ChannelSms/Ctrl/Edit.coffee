define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_ChannelSms_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_ChannelSms_Ctrl_Edit'
		@CTRL_AS = 'ChannelSmsEdit'
		@DEPS    = ['Api', 'Growl', 'SmsAccountsData', '$stateParams', '$modal', 'dpObTypesDefTicketActions']

		init: ->
			@accountId = parseInt(@$stateParams.id || 0)

		initialLoad: ->
			list_promise = @SmsAccountsData.loadList().then((recs) =>
				@accounts = recs.values()

				if @$state.current.name == 'tickets.channel_sms'
					if @accounts[0]
						@$state.go('tickets.channel_sms.edit', {id: @accounts[0].id})
					else
						@$state.go('tickets.channel_sms.create')

				@addManagedListener(@SmsAccountsData.recs, 'changed', =>
					@accounts = @SmsAccountsData.recs.values()
					console.log @accounts
					@ngApply()
				)
			)

			return @$q.all([list_promise]);

	Admin_ChannelSms_Ctrl_Edit.EXPORT_CTRL()
