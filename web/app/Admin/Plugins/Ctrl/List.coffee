define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Plugins_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Plugins_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@pluginDefs = []

		initialLoad: ->
			promise = @Api.sendGet('/plugins/defs').then( (result) =>
				@pluginDefs = result.data.plugin_defs
			)
			return promise

	Admin_Plugins_Ctrl_List.EXPORT_CTRL()