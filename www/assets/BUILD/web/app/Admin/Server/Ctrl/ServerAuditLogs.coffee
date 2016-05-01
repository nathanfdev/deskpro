define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerAuditLogs_Ctrl_ServerAuditLogs extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_ServerAuditLogs_Ctrl_ServerAuditLogs'
    @CTRL_AS   = 'ServerAuditLogs'
    @DEPS      = ['Api2']

    init: ->
      @logs = []

    initialLoad: ->
      @Api2.sendGet('/audit_logs').then((response) => @logs = response.data.data)


  Admin_ServerAuditLogs_Ctrl_ServerAuditLogs.EXPORT_CTRL()