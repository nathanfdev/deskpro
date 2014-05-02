define ['AdminStart/Ctrl/StartBase'], (StartBase) ->
	class AdminStart_Ctrl_Finish extends StartBase
		@CTRL_ID = 'AdminStart_Ctrl_Finish'

		init: ->
			#@$scope.card_loaded = false
			return

	AdminStart_Ctrl_Finish.EXPORT_CTRL()