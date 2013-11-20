define [
	'Admin/CustomFields/Base/Ctrl/Edit',
], (
	Admin_CustomFields_Base_Ctrl_Edit
) ->
	class Admin_CustomFields_Tickets_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit
		@CTRL_ID = 'Admin_CustomFields_Tickets_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []

		getDataService: ->
			return @DataService.get('TicketFields')

		getBaseRouteName: ->
			return "tickets.fields"

	Admin_CustomFields_Tickets_Ctrl_Edit.EXPORT_CTRL()