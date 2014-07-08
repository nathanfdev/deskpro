define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_Tasks_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Tasks_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams']

		init: ->
			@data = @DataService.get('Tasks')




		initialLoad: ->
			@data.load().then( (settings) => @$scope.settings = settings )

		save: ->
			@startSpinner('saving')
			@data.save().then(
				=>
					console.log 'success'
					@stopSpinner('saving')
				=>
					console.log 'fail'
					@stopSpinner('saving')
			)



	Admin_Tasks_Ctrl_Edit.EXPORT_CTRL()