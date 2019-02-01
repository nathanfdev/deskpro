define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_AgentAuditLogs_Ctrl_AuditLogsView extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_AgentAuditLogs_Ctrl_AuditLogsView';
      this.CTRL_AS   = 'AuditLogsView';
      this.DEPS      = ['Api2'];
    }

    init() {
      return this.log = {};
    }

    initialLoad() {
      return this.Api2.sendGet(`/audit_logs/${this.$stateParams.id}`).then(
        response => {
          return this.log = response.data.data;
      });
    }

    getChangeSet() {
      return angular.toJson(this.log.data, true);
    }
  }
  Admin_AgentAuditLogs_Ctrl_AuditLogsView.initClass();


  return Admin_AgentAuditLogs_Ctrl_AuditLogsView.EXPORT_CTRL();
});