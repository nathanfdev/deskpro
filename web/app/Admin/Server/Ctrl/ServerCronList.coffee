define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerCron_Ctrl_List extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerCron_Ctrl_List'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		init: ->
			@server_cron = null

		initialLoad: ->
			data_promise = @Api.sendGet('/server_cron').then( (res) =>

				@server_cron = res.data.server_cron
			)

			return @$q.all([data_promise])

	Admin_ServerCron_Ctrl_List.EXPORT_CTRL()