define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_ServerPhpInfo_Ctrl_ServerPhpInfo extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_ServerPhpInfo_Ctrl_ServerPhpInfo'
		@CTRL_AS   = 'ServerPhpInfo'
		@DEPS      = []

		init: ->
			@$scope.server_php_info = null

		initialLoad: ->
			data_promise = @Api.sendGet('/server_php_info').then( (res) =>

				@$scope.server_php_info = res.data.server_php_info
			)

			return @$q.all([data_promise])

	Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.EXPORT_CTRL()