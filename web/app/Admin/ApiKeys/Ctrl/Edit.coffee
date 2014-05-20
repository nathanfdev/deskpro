define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_ApiKeys_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_ApiKeys_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams']

		init: ->
			@keyData = @DataService.get('ApiKeys')
			@api_key = null

		###
 	#
 	###

		initialLoad: ->
			promise = @keyData.loadEditApiKeyData(@$stateParams.id || null).then( (data) =>

				@api_key  = data.api_key
				@form = data.form
			)
			return promise

		###
		#
 	###

		saveForm: ->

			if not @$scope.form_props.$valid
				return

			is_new = !@api_key.id

			promise = @keyData.saveFormModel(@api_key, @form)

			@startSpinner('saving')
			promise.then( =>
				@stopSpinner('saving', true).then(=>
					@Growl.success("Saved")
				)

				@skipDirtyState()
				if is_new
					@$state.go('apps.api_keys.gocreate')
			)

		###
 	#
		###

		regenerateApiKey: ->

			@keyData.regenerateApiKey(@api_key, @form).success( =>

				@Growl.success("API Key regenerated")
			)

	Admin_ApiKeys_Ctrl_Edit.EXPORT_CTRL()