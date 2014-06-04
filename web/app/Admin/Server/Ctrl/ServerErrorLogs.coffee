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
				if @$scope.server_error_logs?.logs
					@$scope.server_error_logs.logs = @$scope.server_error_logs.logs.reverse()
				@$scope.logs_size = _.size(@$scope.server_error_logs.logs)
			)

			return @$q.all([data_promise])

		###
		# Show the clear dlg
		###
		startClearAll: ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('Server/server-error-logs-delete-modal.html'),
				controller: ['$scope', '$modalInstance',  ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close()

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

			inst.result.then( () =>
				@clearAll()
			)

		###
		# Actually do the clear
		###
		clearAll: ->
			@Api.sendDelete('/server_error_logs/').success( =>
				@$scope.server_error_logs.logs = null
			)

	Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.EXPORT_CTRL()