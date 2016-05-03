define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_ServerAuditLogs_Ctrl_ServerAuditLogsView extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_ServerAuditLogs_Ctrl_ServerAuditLogsView'
    @CTRL_AS   = 'ServerAuditLogsView'
    @DEPS      = ['Api2']

    init: ->
      @log = {}

    initialLoad: ->
      @Api2.sendGet('/audit_logs/' + @$stateParams.id).then(
        (response) =>
          @log = response.data.data
      )

    getChangeSet: ->
      return angular.toJson(@log.data, true)


  Admin_ServerAuditLogs_Ctrl_ServerAuditLogsView.EXPORT_CTRL()