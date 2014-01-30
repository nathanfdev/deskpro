define [
	'AdminUpgrade/Main/Ctrl/UpgradeBase',
	'DeskPRO/Util/Strings'
], (
	UpgradeBase,
	Strings
) ->
	class AdminUpgrade_Main_Ctrl_UpgradeWatch extends UpgradeBase
		@CTRL_ID   = 'AdminUpgrade_Main_Ctrl_UpgradeWatch'
		@CTRL_AS   = 'Watch'
		@DEPS      = ['$interval', '$http']

		init: ->
			window.WATCH = this

			@startTime = null
			@initialPollTime = false

			@$scope.step = {
				waiting:          { on: true,  complete: false },
				started:          { on: false, complete: false },
				checks:           { on: false, complete: false },
				download:         { on: false, complete: false },
				backup_files:     { on: false, complete: false },
				disable_helpdesk: { on: false, complete: false },
				backup_database:  { on: false, complete: false },
				install_files:    { on: false, complete: false },
				install_database: { on: false, complete: false },
				enable_helpdesk:  { on: false, complete: false },
				done:             { on: false, complete: false },
			}

			@stepId = 0;
			@steps = [
				'waiting',
				'started',
				'checks',
				'download',
				'backup_files',
				'disable_helpdesk',
				'backup_database',
				'install_files',
				'install_database',
				'enable_helpdesk',
				'done'
			]

			@Api.sendDataGet({
				updateStatus: '/server/updates/auto'
			}).then((result) =>
				if not result.data.updateStatus.is_scheduled
					@$location.path('/')
					return

				@startTime = parseInt(result.data.updateStatus.start_time)

				@pollRunning = null
				@pollTimer = @$interval(=>
					if not @$scope.step.waiting.complete
						@pollCheckStarted()
					else
						@pollStatus()
				, 2000)
			)
			return

		finishStep: (stepName) ->
			id = @steps.indexOf(stepName)

			for i in [0..id]
				name = @steps[i]
				@$scope.step[name].on = false
				@$scope.step[name].complete = true

			name = @steps[id+1]
			@$scope.step[name].on = true

		beginStep: (stepName) ->
			id = @steps.indexOf(stepName)

			for i in [0..id-1]
				name = @steps[i]
				@$scope.step[name].on = false
				@$scope.step[name].complete = true

			name = @steps[id]
			@$scope.step[name].on = true

		finishAll: ->
			for name in @steps
				@$scope.step[name].on = false
				@$scope.step[name].complete = true

			@$scope.step.done.on = true
			@$scope.step.done.complete = false

		pollCheckStarted: ->
			return null if @pollRunning

			@pollRunning = @Api.sendDataGet({
				updateStatus: '/server/updates/auto'
			}).then((result) =>
				@pollRunning = null

				if result.data.updateStatus.with_perm_error
					@handleError('error_write_perm')
				else if result.data.updateStatus.is_started
					@finishStep('waiting')
			, =>
				@pollRunning = null
			)

		pollStatus: ->
			return null if @pollRunning

			url = DP_BASE_URL;
			url = url.replace(/\/index\.php\//, '/');
			url += 'auto-update-status.php'

			@pollRunning = @$http({
				method: 'GET',
				url: url,
				cache: false,
				responseType: 'text'
			}).success( (content) =>
				content = Strings.trim(content)
				lines = content.split(/\n+/)

				if content.length == 0 then return

				last_time = @initialPollTime || @startTime
				restart_timer = true
				for last in lines
					m = /^STATUS\((.*?)\)@([0-9\.]+)#(.*?)$/.exec(last)
					if m
						time = parseFloat(m[2])
						if time >= last_time
							updateStatus(m[1], m[3], m[2])

						if m[1] == 'done' or m[1].indexOf('error_') == 0
							restart_timer = false

				if not restart_timer
					@$interval.cancel(@pollTimer)
			).then( =>
				@pollRunning = null
			, =>
				@pollRunning = null
			)

		updateStatus: (code, message, time) ->
			if code.indexOf('error_') == 0
				@handleError(code, message)
				return

			switch code
				when 'runner_start'
					@finishStep('waiting')
				when 'start'
					@finishStep('waiting')
				when 'basic_checks_start'
					@beginStep('checks')
				when 'helpdesk_offline'
					@beginStep('disable_helpdesk')
				when 'helpdesk_online'
					@beginStep('enable_helpdesk')
				when 'file_backup_start'
					@beginStep('backup_files')
				when 'file_backup_copy_start'
					@beginStep('backup_files')
				when 'file_backup_zip_start'
					@beginStep('backup_files')
				when 'file_backup_cleanup_start'
					@beginStep('backup_files')
				when 'database_backup_start'
					@beginStep('backup_database')
				when 'downloading_update_start'
					@beginStep('download')
				when 'installing_files_start'
					@beginStep('install_files')
				when 'updating_db_start'
					@beginStep('install_database')
				when 'updating_db_start'
					@beginStep('install_database')
				when 'file_backup_loc'
					@$scope.file_backup_loc = message
				when 'database_backup_loc'
					@$scope.db_backup_loc = message
				when 'done'
					@finishAll()

		handleError: (code, message) ->
			@$scope.is_error = true
			@$scope.error = {}

			code_short = code.replace(/^error_/, '')
			@$scope.error[code_short] = true
			@$scope.error_message = message

	AdminUpgrade_Main_Ctrl_UpgradeWatch.EXPORT_CTRL()