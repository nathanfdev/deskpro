define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketEscalations_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketEscalations_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams']

		init: ->
			@escData = @DataService.get('TicketEscalations')
			@esc = null

			@criteriaTypeDef     = @dpObTypesDefTicketFilter
			@criteriaOptionTypes = @criteriaTypeDef.getOptionsForTypes()

			@actionsTypeDef    = @dpObTypesDefTicketActions
			@actionOptionTypes = @actionsTypeDef.getOptionsForTypes()

		initialLoad: ->
			promise = @escData.loadEditEscalationData(@$stateParams.id || null).then( (data) =>
				@esc  = data.escalation
				@form = data.form
			)
			return promise

		saveForm: ->

			if not @$scope.form_props.$valid
				return

			is_new = !@esc.id

			promise = @escData.saveFormModel(@esc, @form)

			@startSpinner('saving')
			promise.then( =>
				@stopSpinner('saving', true).then(=>
					@Growl.success("Saved")
				)

				@skipDirtyState()
				if is_new
					@$state.go('tickets.ticket_escalations.gocreate')
			)

	Admin_TicketEscalations_Ctrl_Edit.EXPORT_CTRL()