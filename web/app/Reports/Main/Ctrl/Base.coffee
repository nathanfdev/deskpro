define [
	'DeskPRO/Main/Ctrl/Base'
], (
	DeskPROBaseCtrl
) ->
	class Reports_Ctrl_Base extends DeskPROBaseCtrl
		@CTRL_AS   = null
		@CTRL_ID   = 'Reports_Main_Ctrl_Base'
		@DEPS      = []

		###*
		* Get the URL to the template
		*
		* @return {String}
		###
		getTemplatePath: (path) ->
			return DP_BASE_REPORTS_URL + '/load-view/' + path