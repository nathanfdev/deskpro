define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Labels_Ctrl_Settings extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Labels_Ctrl_Settings'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['Growl']



		init: ->
			@settings = null
			@service = @DataService.get 'LabelSettings'
			@type = null



		initialLoad: ->
			@type = @$scope.$parent.LabelsList.type
			throw '[Labels_Ctrl_Settings] Can\'t get type from parent' if !@type

			@service.get(@type).then (settings) =>
				@settings = settings



		save: ->
			@startSpinner('saving')
			@service.set(@type).then(
				() =>
					@stopSpinner 'saving'
					@Growl.success @getRegisteredMessage 'saved_settings'
				(res) =>
					@stopSpinner 'saving', true
					@applyErrorResponseToView res.info
			)

	Admin_Labels_Ctrl_Settings.EXPORT_CTRL()