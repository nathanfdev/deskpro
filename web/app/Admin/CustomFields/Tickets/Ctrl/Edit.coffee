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
				return @Api.sendGet('/ticket_layouts/ticket_field_' + @field_id).success( (data) =>
					@layout_edited = false
					@user_layouts  = data.user_layouts
					@agent_layouts = data.agent_layouts
				)
			else
				return null

		postSave: ->
			return null if @layout_edited

			postData = {
				enable_user_layouts: [],
				enable_agent_layouts: []
			}

			for l in @user_layouts
				if l.enabled
					postData.enable_agent_layouts.push(if l.department then l.department.id else 0)
			for l in @agent_layouts
				if l.enabled
					postData.enable_agent_layouts.push(if l.department then l.department.id else 0)

			return @Api.sendPostJson('/ticket_layouts/{field_id}', postData)

		getDataService: ->
			return @DataService.get('TicketFields')

		getBaseRouteName: ->
			return "tickets.fields"

	Admin_CustomFields_Tickets_Ctrl_Edit.EXPORT_CTRL()