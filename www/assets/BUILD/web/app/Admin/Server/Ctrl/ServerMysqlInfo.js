// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo extends Admin_Ctrl_Base {
    static initClass() {
  
      this.CTRL_ID   = 'Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo';
      this.CTRL_AS   = 'ServerMysqlInfo';
      this.DEPS      = [];
    }

    init() {
      return this.$scope.is_loading_schemadiff = true;
    }


    initialLoad() {
      this.Api.sendGet('/server_mysql_info/schema-diff').then( res => {
        this.$scope.schema_diff = res.data.mysql_schema_diff;
        return this.$scope.is_loading_schemadiff = false;
      });

      return this.Api.sendGet('/server_mysql_info').then( res => {
        return this.$scope.server_mysql_info = res.data.server_mysql_info;
      });
    }
  }
  Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.initClass();

  return Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.EXPORT_CTRL();
});