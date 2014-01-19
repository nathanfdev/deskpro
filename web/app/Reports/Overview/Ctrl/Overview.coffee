define [
	'Reports/Main/Ctrl/Base'
], (
	ReportsBaseCtrl
) ->
	class Reports_Overview_Ctrl_Overview extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Overview_Ctrl_Overview'
		@CTRL_AS   = 'Overview'

		init: ->
			return

	Reports_Overview_Ctrl_Overview.EXPORT_CTRL()