define [
	'Admin/Main/Ctrl/Base',
	'Admin/Main/Model/DepAgentPermMatrix'
], (
	Admin_Ctrl_Base,
	Admin_Main_Model_DepAgentPermMatrix
) ->
	class Admin_TicketTriggers_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketTriggers_Ctrl_Edit'
		@CTRL_AS   = 'TicketTriggersEdit'
		@CTRL_TYPE = 'page'
		@DEPS      = ['em', '$stateParams', 'dpObTypesDefTicketCriteria']

		init: ->
			@triggerType = @$stateParams.type
			@trigger     = null
			@triggerId   = @$stateParams.id
			@options     = {}

			@$scope.triggerType = @$stateParams.type
			@$scope.triggerId   = @$stateParams.id

			@criteraTypeDef = @dpObTypesDefTicketCriteria

			return

		###
		# Load the trigger
		###
		initialLoad: ->
			if @triggerId
				promise = @Api.sendGet("/ticket_triggers/#{@triggerId}").success( (data) =>
					@trigger = data.trigger
					@form = {
						title: @trigger.title
					}
				)

				return promise

			@trigger = {}

			return null

	Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL()