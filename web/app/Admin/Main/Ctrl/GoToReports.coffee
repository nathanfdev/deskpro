define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_GoToReports extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_GoToReports'

		init: ->
			if not window.parent || not window.parent.DP_FRAME_OVERLAYS || not window.parent.DP_FRAME_OVERLAYS.reports
				window.location.href = window.DP_BASE_URL + 'reports/';
			else
				window.parent.DP_FRAME_OVERLAYS.reports.open()

	Admin_Main_Ctrl_GoToReports.EXPORT_CTRL()