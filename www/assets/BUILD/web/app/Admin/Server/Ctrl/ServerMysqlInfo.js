define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
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
      this.Api.sendGet('/server_mysql_info/schema-diff').then((res) => {
        this.$scope.schema_diff = res.data.mysql_schema_diff;
        return this.$scope.is_loading_schemadiff = false;
      });

      return this.Api.sendGet('/server_mysql_info').then(res => this.$scope.server_mysql_info = res.data.server_mysql_info);
    }
  }
  Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.initClass();

  return Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.EXPORT_CTRL();
});
