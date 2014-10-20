define [
	'Admin/CustomFields/Base/Ctrl/Edit',
], (
	Admin_CustomFields_Base_Ctrl_Edit
) ->
	class Admin_CustomFields_Entity_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit
		@CTRL_ID = 'Admin_CustomFields_Entity_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []

		getDataService: ->
			return @DataService.get('EntityFields')

		getBaseRouteName: ->
			return "crm.user_fields.specific"

	Admin_CustomFields_Entity_Ctrl_Edit.EXPORT_CTRL()