/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus extends Admin_Ctrl_Base {
    static initClass() {
  
      this.CTRL_ID   = 'Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus';
      this.CTRL_AS   = 'ServerMysqlStatus';
      this.DEPS      = [];
    }

    init() {
      return this.$scope.server_mysql_status = null;
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/server_mysql_status').then( res => {

        return this.$scope.server_mysql_status = res.data.server_mysql_status;
      });

      return this.$q.all([data_promise]);
    }
  }
  Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.initClass();

  return Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus.EXPORT_CTRL();
});