define [
	'Admin/CustomFields/Base/Ctrl/Edit',
], (
	Admin_CustomFields_Base_Ctrl_Edit
) ->
	class Admin_CustomFields_Tickets_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit
		@CTRL_ID = 'Admin_CustomFields_Tickets_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []

		initialLoadExtra: ->
			if @field_id
				return @Api.sendGet('/ticket_layouts/fields/ticket_field_' + @field_id).success( (data) =>
					@layout_edited = false
					@user_layouts  = data.user_layouts
					@agent_layouts = data.agent_layouts
				)
			else
				return null

		postSave: ->
			postData = {
				enable_user_layouts: [],
				enable_agent_layouts: []
			}

			for own k,l of @user_layouts
				if l.enabled
					postData.enable_user_layouts.push(if l.department then l.department.id else 0)
			for own k,l of @agent_layouts
				if l.enabled
					postData.enable_agent_layouts.push(if l.department then l.department.id else 0)

			return @Api.sendPostJson('/ticket_layouts/fields/ticket_field_' + @field_id, postData)

		getDataService: ->
			return @DataService.get('TicketFields')

		getBaseRouteName: ->
			return "tickets.fields"

	Admin_CustomFields_Tickets_Ctrl_Edit.EXPORT_CTRL()