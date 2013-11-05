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
			@esc = null

			@criteraTypeDef      = @dpObTypesDefTicketFilter
			@criteriaOptionTypes = @criteraTypeDef.getOptionsForTypes()

			@actionsTypeDef    = @dpObTypesDefTicketActions
			@actionOptionTypes = @actionsTypeDef.getOptionsForTypes()

		initialLoad: ->
			promise = @escData.loadEditEscalationData(@$stateParams.id || null).then( (data) =>
				@esc  = data.escalation
				@form = data.form
			)
			return promise

		saveForm: ->
			is_new = !!@esc.id

			promise = @escData.saveFormModel(@esc, @form)

			@startSpinner('saving')
			promise.then( =>
				@stopSpinner('saving')

				@skipDirtyState()
				if is_new
					@$state.go('tickets.escalations.gocreate')
			)

	Admin_TicketEscalations_Ctrl_Edit.EXPORT_CTRL()