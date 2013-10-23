define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketFields_Ctrl_EditCategories extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_EditCategories'
		@CTRL_AS = 'TicketCats'
		@DEPS    = []
		@CTRL_TYPE = 'page'

		init: ->
			@cats           = []
			@default_id     = 0
			@agent_required = false
			@user_required  = false
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'info': '/ticket_cats'
			}).then( (res) =>
				@cats           = res.data.info.categories
				@default_id     = res.data.info.default_id
				@agent_required = res.data.info.agent_required
				@user_required  = res.data.info.user_required

				@builder_model = {}
			)

			return data_promise

	Admin_TicketFields_Ctrl_EditCategories.EXPORT_CTRL()