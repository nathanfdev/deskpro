define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_AgentAuditLogs_Ctrl_AuditLogsView extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_AgentAuditLogs_Ctrl_AuditLogsView'
    @CTRL_AS   = 'AuditLogsView'
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


  Admin_AgentAuditLogs_Ctrl_AuditLogsView.EXPORT_CTRL()