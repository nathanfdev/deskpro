define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerCron_Ctrl_Logs extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerCron_Ctrl_Logs'
		@CTRL_AS   = 'LogsCtrl'
		@DEPS      = []

		init: ->
			@server_cron_logs = null
			@filter = {
				job_id: '',
				priority: '',
			}

		initialLoad: ->

			return @loadResults()

		loadResults: ->

			data_promise = @Api.sendGet('/server_cron/logs', {job_id: @filter.job_id, priority: @filter.priority}).then((res) =>

				@server_cron_logs = res.data.server_cron_logs
			)

			return @$q.all([data_promise])

		updateFilter: ->

			@loadResults()

	Admin_ServerCron_Ctrl_Logs.EXPORT_CTRL()