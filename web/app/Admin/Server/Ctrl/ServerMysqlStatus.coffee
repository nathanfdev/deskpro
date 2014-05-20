define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus'
		@CTRL_AS   = 'ServerMysqlStatus'
		@DEPS      = []

		init: ->
			@$scope.server_mysql_status = null

		initialLoad: ->
			data_promise = @Api.sendGet('/server_mysql_status').then( (res) =>

				@$scope.server_mysql_status = res.data.server_mysql_status
			)

			return @$q.all([data_promise])

	Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.EXPORT_CTRL()