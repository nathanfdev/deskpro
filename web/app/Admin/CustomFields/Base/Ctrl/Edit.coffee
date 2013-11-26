define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_CustomFields_Base_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_CustomFields_Base_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []

		init: ->
			@field_type = '0'
			@field_type_chooser = 'text'
			@fieldDataService = @getDataService()
			return

		initialLoad: ->
			promise = @fieldDataService.loadEditFieldData(@$stateParams.id || null).then( (data) =>
				@field      = data.field
				@field_type = data.field_type
				@form       = data.form
			)
			return promise

		getDataService: ->
			throw new Error("Not implemented")

		getBaseRouteName: ->
			throw new Error("Not implemented")

		saveForm: ->

			if not @$scope.form_props.$valid
				return

			is_new = !@field.id

			@field.type_name = @field_type
			promise = @fieldDataService.saveFormModel(@field, @form)

			@startSpinner('saving')

			promise.success( =>

				@stopSpinner('saving', true).then(=>
					@Growl.success('Saved')
				)

				@skipDirtyState()

				if is_new
					@$state.go(@getBaseRouteName() + ".gocreate")
				else
					@$state.go(@getBaseRouteName())
			)

			promise.error((info, code) =>

				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)