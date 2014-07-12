define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_RoundRobin_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_RoundRobin_Ctrl_List'
		@DEPS = []
		@CTRL_AS = 'ListCtrl'



		init: ->
			@service = @DataService.get 'RoundRobin'
			@robins = []
			@settings = null



		initialLoad: ->
			@service.all().then (robins) =>
				@robins = robins
			@service.getSettings().then (settings) =>
				@settings = settings



		save: ->
			@service.saveSettings()

	Admin_RoundRobin_Ctrl_List.EXPORT_CTRL()