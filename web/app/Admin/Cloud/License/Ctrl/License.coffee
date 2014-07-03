define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Cloud_License_Ctrl_License extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Cloud_License_Ctrl_License'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$window']

		init: -> null

		initialLoad: ->
			console.log("Done")
			return null

	Admin_Cloud_License_Ctrl_License.EXPORT_CTRL()