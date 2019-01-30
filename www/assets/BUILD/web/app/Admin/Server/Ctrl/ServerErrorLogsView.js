// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
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
      const data_promise = this.Api.sendGet(`/server_error_logs/${this.$stateParams.id}`).then( res => {

        return this.error_log = res.data.server_error_log;
      });

      return this.$q.all([data_promise]);
    }
  }
  Admin_ServerErrorLogs_Ctrl_View.initClass();

  return Admin_ServerErrorLogs_Ctrl_View.EXPORT_CTRL();
});