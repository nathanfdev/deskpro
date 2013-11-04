define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketEscalations_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketEscalations_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@CTRL_TYPE = 'page'
		@DEPS      = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams']

		init: ->
			@escData = @DataService.get('TicketEscalations')
			@filter = null

			@escalation_criteria = {}
			@escalation_actions  = {}

			@criteraTypeDef      = @dpObTypesDefTicketFilter
			@criteriaOptionTypes = @criteraTypeDef.getOptionsForTypes()

			@actionsTypeDef    = @dpObTypesDefTicketActions
			@actionOptionTypes = @actionsTypeDef.getOptionsForTypes()

		initialLoad: ->
			if @$stateParams.id
				promise = @escData.loadEditEscalationData(@$stateParams.id).then( (data) =>
					@esc = data.esc
					@form = @getFormFromModel(@esc)
				)
				return promise
			else
				@esc = {}
				@form = @getFormFromModel(@esc)
				return null

		getFormFromModel: (escModel) ->
			form = {}
			form.title = escModel.title || ''

			return form

	Admin_TicketEscalations_Ctrl_Edit.EXPORT_CTRL()