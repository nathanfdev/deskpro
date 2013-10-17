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
		@DEPS      = ['em', '$stateParams']

		init: ->
			@trigger = null
			@triggerId = @$stateParams.id
			return

		###
		# Load the trigger
		###
		initialLoad: ->
			promise = @Api.sendGet("/ticket_triggers/#{@triggerId}").success( (data) =>
				@trigger = data.trigger
				@form = {
					title: @trigger.title
				}
			)

			return promise

	Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL()