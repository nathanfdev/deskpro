define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_TicketFields_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_List'
		@CTRL_AS = 'TicketFieldsList'
		@DEPS    = []

		init: ->
			@custom_fields = []
			@field_enabled = {}
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet([
					'/ticket_fields'
			]).then( (res) =>
				for f in res.data.api_ticket_fields.custom_fields
					@custom_fields.push(f)

				for f in ['category', 'priority', 'workflow', 'product']
					@field_enabled[f] = false
					if res.data.api_ticket_fields[f + '_enabled']
						@field_enabled[f] = true
			)

			return @$q.all([data_promise])

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