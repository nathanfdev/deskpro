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
	class Admin_TicketTriggers_Ctrl_EditDepartmentTrigger extends Admin_TicketTriggers_Ctrl_EditBase
		@CTRL_ID   = 'Admin_TicketTriggers_Ctrl_EditDepartmentTrigger'
		@CTRL_AS   = 'TicketTriggersEdit'
		@DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions']

		customInit: ->
			@triggerId = 0
			@depId = @$stateParams.id.replace(/^department\-(\d+)$/, '$1')

			@$scope.triggerType = @$stateParams.type
			@$scope.triggerId   = 0
			@$scope.depId       = @depId

		###
		# Load the trigger
		###
		initialLoad: ->
			get = {
				customActions: '/ticket_triggers/get-custom-actions',
				depInfo:       "/ticket_deps/#{@depId}",
				trigger:       "/ticket_triggers/departments/#{@depId}"
			}

			promise = @Api.sendDataGet(get).then( (result) =>

				@customActions = result.data.customActions.action_defs

				if result.data?.trigger?.trigger?
					@trigger = result.data.trigger.trigger
					@triggerId = @trigger.id
				else
					@trigger = {}
					@triggerId = 0

				@dep = result.data.depInfo.department
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


	Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.EXPORT_CTRL()