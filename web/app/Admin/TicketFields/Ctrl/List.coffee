define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_TicketFields_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_List'
		@CTRL_AS = 'TicketFieldsList'
		@DEPS    = []

		init: ->
			@ticket_fields = @DataService.get('TicketFields')
			@custom_fields = []
			@field_enabled = {}
			return

		initialLoad: ->
			promise = @ticket_fields.loadList()
			promise.then( (list) =>
				@custom_fields = list
				@field_enabled = @ticket_fields.field_enabled
			)

			return promise

		updateBuiltinFieldEnabledState: (name) ->
			if @field_enabled[name]
				val = '1'
			else
				val = '0'

			@Api.sendPost('/ticket_fields/set-enabled/' + name + '/' + val)

		updateCustomFieldEnabledState: (field) ->
			if field.is_enabled
				val = '1'
			else
				val = '0'

			@Api.sendPost('/ticket_fields/set-enabled/field_' + field.id + '/' + val)


	Admin_TicketFields_Ctrl_List.EXPORT_CTRL()