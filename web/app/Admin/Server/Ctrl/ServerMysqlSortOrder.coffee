define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder'
		@CTRL_AS   = 'ServerMysqlSortOrder'
		@DEPS      = []

		init: ->
			@$scope.server_mysql_sort_order = null
			@$scope.all_collations = null
			@$scope.current_sort_order = 'General Purpose (Default)'
			@$scope.update_started = false

		initialLoad: ->
			data_promise = @Api.sendGet('/server_mysql_sort_order').then( (res) =>

				@$scope.server_mysql_sort_order = res.data.server_mysql_sort_order
				@$scope.all_collations = res.data.all_collations

				if @$scope.all_collations[@$scope.server_mysql_sort_order.db_collation]?
						@$scope.current_sort_order = @$scope.all_collations[@$scope.server_mysql_sort_order.db_collation]
			)

			return @$q.all([data_promise])

		save: ->

			if not @$scope.form_props.$valid
				return

			postData = {
				server_mysql_sort_order: @$scope.server_mysql_sort_order
			}

			@startSpinner('saving')

			@Api.sendPostJson('/server_mysql_sort_order', postData).success( =>

				@server_mysql_sort_order = angular.copy(@$scope.server_mysql_sort_order)

				@stopSpinner('saving').then(=>

					@$scope.current_sort_order = @$scope.all_collations[@$scope.server_mysql_sort_order.db_collation]
					@$scope.update_started = true

					@Growl.success('Update of sort order started')
				)
			).error( (info, code) =>

				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

	Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.EXPORT_CTRL()