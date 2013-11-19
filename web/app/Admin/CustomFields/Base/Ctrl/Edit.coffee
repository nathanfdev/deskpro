define [
	'Admin/Main/Ctrl/Base',
	'Admin/CustomFields/FormMapper/FieldFormMapper'
], (
	Admin_Ctrl_Base,
	FieldFormMapper
) ->
	class Admin_CustomFields_Base_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_CustomFields_Base_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []

		init: ->
			@field_type = '0'
			@field_type_chooser = 'text'
			@formMapper = new FieldFormMapper()
			return

		initialLoad: ->
			if @$stateParams.id
				promise = @getField()
				promise.then( (result) =>
					@field = result.data.field
					@field_type = @field.type_name
					@form = @formMapper.getFormFromModel(@field)
				)
				return promise
			else
				@field = {}
				@form = @formMapper.getFormFromModel(@field)
				return null