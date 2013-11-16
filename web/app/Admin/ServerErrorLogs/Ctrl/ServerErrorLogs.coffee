define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerErrorLogs_Ctrl_ServerErrorLogs extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerErrorLogs_Ctrl_ServerErrorLogs'
		@CTRL_AS   = 'ServerErrorLogs'
		@DEPS      = []

		init: ->
			@$scope.server_error_logs = null
			@$scope.logs_size = 0

		initialLoad: ->
			data_promise = @Api.sendGet('/server_error_logs').then( (res) =>

				@$scope.server_error_logs = res.data.server_error_logs
				@$scope.logs_size = _.size(@$scope.server_error_logs.logs)
			)

			return @$q.all([data_promise])

	Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.EXPORT_CTRL()