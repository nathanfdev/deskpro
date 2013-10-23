define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketFields_Ctrl_EditWorkflows extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_EditWorkflows'
		@CTRL_AS = 'TicketWorks'
		@DEPS    = []
		@CTRL_TYPE = 'page'

		init: ->
			@works          = []
			@default_id     = 0
			@agent_required = false
			@user_required  = false
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'info': '/ticket_works'
			}).then( (res) =>
				@works           = res.data.info.workflows
				@default_id     = res.data.info.default_id
				@agent_required = res.data.info.agent_required
				@user_required  = res.data.info.user_required

				@builder_model = {}
			)

			return data_promise

	Admin_TicketFields_Ctrl_EditWorkflows.EXPORT_CTRL()