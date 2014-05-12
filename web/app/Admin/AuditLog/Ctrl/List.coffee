define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_AuditLog_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AuditLog_Ctrl_List'
		@CTRL_AS   = 'List'
		@DEPS      = []

		init: ->
			return

		initialLoad: ->
			@Api.sendGet('/audit_log').then((res) =>
				@logs      = res.data.logs
				@total     = res.data.total
				@num_pages = res.data.num_pages
			)

	Admin_AuditLog_Ctrl_List.EXPORT_CTRL()