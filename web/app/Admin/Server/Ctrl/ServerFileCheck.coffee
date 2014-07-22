define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerFileCheck_Ctrl_ServerFileCheck extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_ServerFileCheck_Ctrl_ServerFileCheck'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->
			@server_file_check = null

			@total_checks = 0
			@current_check = 0
			@current_percentage = 0

			@check_started = false
			@check_in_progress = false
			@has_errors = false

			@show_log = false
			@show_log_text = 'Show Log'
			@logs = []
			@error_logs = []

		initialLoad: ->
			data_promise = @Api.sendGet('/server_file_check').then( (res) =>
				@server_file_check = res.data.server_file_check
				@total_checks = @server_file_check.count

				# the case when we have '/app/sys/Resources/distro-checksums.php' deleted
				if @total_checks == 1
					@current_check = -1
					@doNextRequest()
			)

			return @$q.all([data_promise])


		###
 		# Starting the process of integrity file check
		###
		startCheck: ->
			@current_check = 0
			@current_percentage = 0
			@check_started = true
			@check_in_progress = true
			@has_errors = false
			@logs = []
			@error_logs = []

			@doNextRequest()


		###
		# Execute AJAX request to next batch of files
		###
		doNextRequest: ->
			@current_check++

			if @current_check <= @total_checks
				@Api.sendGet('/server_file_check/' + (@current_check-1)).then( (res) =>
					data = res.data.server_file_check

					if data.okay
						@logs.push 'Batch ' + @current_check + ' of ' + @total_checks + ': ' + data.okay.length + ' files verified'
					if data.added
						for file in data.added
							@logs.push 'Batch ' + @current_check + ' of ' + @total_checks + ': ' + ' File added: ' + file

					if data.changed and data.changed.length
						for file in data.changed
							@logs.push 'Batch ' + @current_check + ' of ' + @total_checks + ': ' + ' File changed: ' + file
							@error_logs.push 'CHANGED: ' + file
							@has_errors = true
					if data.removed and data.removed.length
						for file in data.removed
							@logs.push 'Batch ' + @current_check + ' of ' + @total_checks + ': ' + ' Missing: ' + file
							@error_logs.push 'MISSING: ' + file
							@has_errors = true

					@current_percentage = Math.ceil @current_check / @total_checks * 100
					@doNextRequest()
				)
			else
				@check_in_progress = false
				@current_percentage = 100

		###
		# Show / hide 'show log' button
		###
		toggleLog: ->
			@show_log = !@show_log
			@show_log_text = if @show_log then 'Hide Log' else 'Show Log'


	Admin_ServerFileCheck_Ctrl_ServerFileCheck.EXPORT_CTRL()