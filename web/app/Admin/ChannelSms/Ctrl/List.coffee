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
				@accounts = recs.values()

#				if @$state.current.name == 'tickets.channel_sms'
#					if @accounts[0]
#						@$state.go('tickets.channel_sms.edit', {id: @accounts[0].id})
#					else
#						@$state.go('tickets.channel_sms.create')

				@addManagedListener(@SmsAccountsData.recs, 'changed', =>
					@accounts = @SmsAccountsData.recs.values()
					@ngApply()
				)
			)

			return @$q.all([list_promise]);

	Admin_ChannelSms_Ctrl_List.EXPORT_CTRL()
