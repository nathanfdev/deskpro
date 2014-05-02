define ['AdminStart/Ctrl/StartBase'], (StartBase) ->
	class AdminStart_Ctrl_Email extends StartBase
		@CTRL_ID = 'AdminStart_Ctrl_Email'

		init: ->
			#@$scope.card_loaded = true
			return

	AdminStart_Ctrl_Email.EXPORT_CTRL()