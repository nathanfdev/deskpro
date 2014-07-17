define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Labels_Ctrl_Settings extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Labels_Ctrl_Settings'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['Growl']

		init: ->
			@settings = null
			@service = @DataService.get 'LabelSettings'

		initialLoad: ->
			@service.get().then (settings) =>
				@settings = settings



		save: ->
			@startSpinner('saving')
			@service.set().then(
				() =>
					@stopSpinner 'saving'
					@Growl.success @getRegisteredMessage 'saved_settings'
				(info) =>
					@stopSpinner 'saving', true
					@applyErrorResponseToView info
			)

	Admin_Labels_Ctrl_Settings.EXPORT_CTRL()