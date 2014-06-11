define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketMacros_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketMacros_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['dpObTypesDefTicketActions', '$stateParams']

		init: ->
			@macroData  = @DataService.get('TicketMacros')

			@macroId = parseInt(@$stateParams.id)
			@macro  = null
			@agents = null
			@form   = null

			@actionsTypeDef    = @dpObTypesDefTicketActions
			@actionOptionTypes = @actionsTypeDef.getOptionsForTypes()

		initialLoad: ->
			promise = @macroData.loadEditMacroData(@macroId || null).then( (data) =>
				@macro  = data.macro
				@agents = data.agents
				@form   = data.form

				console.log(@form)
			)
			return promise

		saveForm: ->
			@form.agents = @agents
			promise = @macroData.saveFormModel(@macro, @form)

			@startSpinner('saving')
			promise.then( =>
				@stopSpinner('saving')

				@skipDirtyState()
				if !@macroId
					@$state.go('tickets.macros.gocreate')
			)

	Admin_TicketMacros_Ctrl_Edit.EXPORT_CTRL()