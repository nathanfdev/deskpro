define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketMacros_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketMacros_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@CTRL_TYPE = 'page'
		@DEPS      = ['dpObTypesDefTicketActions', '$stateParams']

		init: ->
			@macroData = @DataService.get('TicketMacros')
			@macro = null

			@macro_criteria = {}
			@actionsTypeDef    = @dpObTypesDefTicketActions
			@actionOptionTypes = @actionsTypeDef.getOptionsForTypes()

		initialLoad: ->
			if @$stateParams.id
				promise = @macroData.loadEditMacroData(@$stateParams.id).then( (data) =>
					@macro = data.macro
					@form = @getFormFromModel(@macro)
				)
				return promise
			else
				@macro = {}
				@form = @getFormFromModel(@macro)
				return null

		getFormFromModel: (macroModel) ->
			form = {}
			form.title = macroModel.title || ''

			return form

	Admin_TicketMacros_Ctrl_Edit.EXPORT_CTRL()