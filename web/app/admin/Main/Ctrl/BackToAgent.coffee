define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_BackToAgent extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_BackToAgent'
		@DEPS      = ['$scope']

		init: ->
			# Redirect back to agent
			# Unless this is in an iframe, in which case simply viewing
			# this route will cause the parent to close the iframe and
			# make the agent interface visible again
			if not window.parent
				window.location = window.DP_BASE_URL + '/agent/';

	Admin_Main_Ctrl_BackToAgent.EXPORT_CTRL()