define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_ServerErrorLogs_Ctrl_View extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerErrorLogs_Ctrl_View';
      this.CTRL_AS   = 'ServerErrorLogsView';
      this.DEPS      = [];
    }

    init() {
      return this.error_log = {};
    }

    initialLoad() {
      const data_promise = this.Api.sendGet(`/server_error_logs/${this.$stateParams.id}`).then(res => this.error_log = res.data.server_error_log);

      return this.$q.all([data_promise]);
    }
  }
  Admin_ServerErrorLogs_Ctrl_View.initClass();

  return Admin_ServerErrorLogs_Ctrl_View.EXPORT_CTRL();
});
