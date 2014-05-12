define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_AuditLog_Ctrl_View extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AuditLog_Ctrl_View'
		@CTRL_AS   = 'View'
		@DEPS      = []

		init: ->
			@logId = parseInt(@$stateParams.id)
			return

		initialLoad: ->
			@Api.sendGet('/audit_log/' + @logId).then((res) =>
				@log = res.data.log
			)

	Admin_AuditLog_Ctrl_View.EXPORT_CTRL()