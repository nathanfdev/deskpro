define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerReportFile_Ctrl_ServerReportFile extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerReportFile_Ctrl_ServerReportFile'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->

			@report_link = window.DP_BASE_API_URL + '/server_report_file?API-TOKEN=' + window.DP_API_TOKEN

	Admin_ServerReportFile_Ctrl_ServerReportFile.EXPORT_CTRL()