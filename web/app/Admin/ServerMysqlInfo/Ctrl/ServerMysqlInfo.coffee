define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo'
		@CTRL_AS   = 'ServerMysqlInfo'
		@DEPS      = []

		init: ->
			@$scope.server_mysql_info = null

		initialLoad: ->
			data_promise = @Api.sendGet('/server_mysql_info').then( (res) =>

				@$scope.server_mysql_info = res.data.server_mysql_info
			)

			return @$q.all([data_promise])

	Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.EXPORT_CTRL()