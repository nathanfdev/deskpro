define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], (Admin_Ctrl_Base, Strings) ->
	class Admin_Apps_Ctrl_InstallProgress extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Apps_Ctrl_InstallProgress'
		@CTRL_AS   = 'EmailTemplateEditor'
		@DEPS      = ['$modalInstance', '$timeout', 'Api', 'pack', 'setting_values']

		init: ->
			@$scope.pack = @pack
			@isDone = false
			@info = null

			@step = 0
			@steps = [
				{step: -1, percent: 0, timeout: 0},
				{step: 0, percent: 2, timeout: 1200},
				{step: 1, percent: 12, timeout: 1500},
				{step: 2, percent: 35, timeout: 800},
				{step: 3, percent: 60, timeout: 1500, wait: true},
				{step: 4, percent: 95, timeout: 1000 },
				{step: 5, percent: 100},
			]

			@stepTimeout = null
			@incrementStep()

			@$scope.done = =>
				@closeForSuccess(@info)

			url = "/apps/packages/#{@pack.name}"
			@Api.sendPutJson(url, {settings: @setting_values}).success( (info) =>
				@markAsDone(info)
			, (info) =>
				@closeForError(info)
			)

		incrementStep: ->
			currentStep = @steps[@step]
			if currentStep.wait and !@isDone
				@beginStepTimeout(100)
				return

			@step += 1
			if not @steps[@step]
				@$scope.stepsDone = true
				return

			nextStep = @steps[@step]
			@$scope.stepId = nextStep.step
			@$scope.perc   = nextStep.percent
			@beginStepTimeout(nextStep.timeout)

		beginStepTimeout: (ms) ->
			@$timeout.cancel(@stepTimeout) if @stepTimeout
			@stepTimeout = @$timeout(=>
				@stepTimeout = null
				@incrementStep()
			, ms)

		markAsDone: (info) ->
			@isDone = true
			@info = info

		closeForError: (info) ->
			@$timeout.cancel(@stepTimeout) if @stepTimeout
			@$modalInstance.dismiss(info)

		closeForSuccess: (info) ->
			@$timeout.cancel(@stepTimeout) if @stepTimeout
			@$modalInstance.close(info)

	Admin_Apps_Ctrl_InstallProgress.EXPORT_CTRL()