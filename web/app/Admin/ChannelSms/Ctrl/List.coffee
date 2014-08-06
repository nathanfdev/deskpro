define ['Admin/Main/Ctrl/Base'], (Admin_Main_Ctrl_Base) ->
	class Admin_ChannelSms_Ctrl_List extends Admin_Main_Ctrl_Base
		@CTRL_ID = 'Admin_ChannelSms_Ctrl_List'
		@CTRL_AS = 'ChannelSmsList'
		@CTRL_TYPE = 'list'
		@DEPS = ['SmsAccountsData']

		init: ->
			@accounts = []

		initialLoad: ->
			list_promise = @SmsAccountsData.loadList().then((recs) =>
				console.log recs
				@accounts = recs.values()
				console.log @accounts

				if @$state.current.name == 'tickets.channel_sms'
					console.log 'yes, in correct state name'
#					if @accounts[0]
#						@$state.go('tickets.ticket_accounts.edit', {id: @accounts[0].id})
#					else
#						@$state.go('tickets.ticket_accounts.create')

				@addManagedListener(@SmsAccountsData.recs, 'changed', =>
					@accounts = @SmsAccountsData.recs.values()
					@ngApply()
				)
			)

			return @$q.all([list_promise]);

	Admin_ChannelSms_Ctrl_List.EXPORT_CTRL()
