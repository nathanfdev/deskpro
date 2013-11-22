define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Plugins_Ctrl_Install extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Plugins_Ctrl_Install'
		@CTRL_AS = 'InstallCtrl'

		init: ->
			@pluginDef = []

		initialLoad: ->
			promise = @Api.sendGet("/plugins/defs/#{@$stateParams.id}/installer").then( (result) =>
				@pluginDef = result.data.plugin_def
			)
			return promise

	Admin_Plugins_Ctrl_Install.EXPORT_CTRL()