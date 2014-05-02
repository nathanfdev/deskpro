define ['AdminStart/Ctrl/StartBase'], (StartBase) ->
	class AdminStart_Ctrl_Cron extends StartBase
		@CTRL_ID   = 'AdminStart_Ctrl_Cron'

		init: ->
			#@$scope.card_loaded = false
			return

	AdminStart_Ctrl_Cron.EXPORT_CTRL()