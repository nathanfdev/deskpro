define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerErrorLogs_Ctrl_View extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerErrorLogs_Ctrl_View'
		@CTRL_AS   = 'ServerErrorLogsView'
		@DEPS      = []

		init: ->

			@error_log = {}

		initialLoad: ->
			data_promise = @Api.sendGet('/server_error_logs/' + @$stateParams.id).then( (res) =>

				@error_log = res.data.server_error_log
			)

			return @$q.all([data_promise])

	Admin_ServerErrorLogs_Ctrl_View.EXPORT_CTRL()