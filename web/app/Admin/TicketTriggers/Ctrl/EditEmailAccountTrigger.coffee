define [
	'DeskPRO/Util/Arrays'
	'Admin/Main/Ctrl/Base',
	'Admin/TicketTriggers/TriggerEditFormMapper',
	'Admin/TicketTriggers/Ctrl/EditBase',
], (
	Arrays,
	Admin_Ctrl_Base,
	TriggerEditFormMapper,
	Admin_TicketTriggers_Ctrl_EditBase
) ->
	class Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger extends Admin_TicketTriggers_Ctrl_EditBase
		@CTRL_ID   = 'Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger'
		@CTRL_AS   = 'TicketTriggersEdit'
		@DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions']

		customInit: ->
			@triggerId = 0
			@accountId = @$stateParams.id.replace(/^emailaccount\-(\d+)$/, '$1')

			@$scope.triggerType = @$stateParams.type
			@$scope.triggerId   = 0
			@$scope.acountId    = @accountId

		###
		# Load the trigger
		###
		initialLoad: ->
			get = {
				customActions: '/ticket_triggers/get-custom-actions',
				accInfo:       "/email_accounts/#{@accountId}",
				trigger:       "/ticket_triggers/email_accounts/#{@accountId}"
			}

			promise = @Api.sendDataGet(get).then( (result) =>

				@customActions = result.data.customActions.action_defs

				if result.data?.trigger?.trigger?
					@trigger = result.data.trigger.trigger
					@triggerId = @trigger.id
				else
					@trigger = {}
					@triggerId = 0

				@account = result.data.accInfo.email_account
				@$scope.form = @editFormMapper.getFormFromModel(@trigger)
			)

			promise2 = @criteraTypeDef.loadDataOptions()
			promise3 = @actionsTypeDef.loadDataOptions()

			promises = [promise, promise2, promise3]

			return @$q.all(promises).then(=>
				@updateCriteriaOptionTypes()

				@$scope.$watch('form.typeForm', =>
					@updateCriteriaOptionTypes()
				, true)
			)


	Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.EXPORT_CTRL()