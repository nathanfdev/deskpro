define [
	'DeskPRO/Main/Ctrl/Base'
], (
	DeskPROBaseCtrl
) ->
	class Admin_Ctrl_Base extends DeskPROBaseCtrl
		@CTRL_AS   = null
		@CTRL_ID   = 'Admin_Main_Ctrl_Base'
		@DEPS      = []



		###*
		* Get the URL to the template
		*
		* @return {String}
		###
		getTemplatePath: (path) ->
			return DP_BASE_ADMIN_URL+'/load-view/' + path