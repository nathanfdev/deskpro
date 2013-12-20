define ['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) ->
	class Admin_Templates_Ctrl_EmailList extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Templates_Ctrl_EmailList'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		###
		# Open an editor
		###
		openEditor: (tpl) ->
			modalInstance = @$modal.open({
				templateUrl: 'Templates/modal-email-editor.html',
				controller: 'Admin_Templates_Ctrl_EmailEditor',
				resolve: {
					templateName: ->
						return tpl.name
				}
			}).result.then( (info) =>
				if info.mode == 'custom'
					tpl.is_custom = true
				else if info.mode == 'revert'
					tpl.is_custom = false
			)

			return modalInstance

	Admin_Templates_Ctrl_EmailList.EXPORT_CTRL()