define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_ServerPhpInfo_Ctrl_ServerPhpInfo extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerPhpInfo_Ctrl_ServerPhpInfo';
      this.CTRL_AS   = 'ServerPhpInfo';
      this.DEPS      = [];
    }

    init() {
      return this.$scope.server_php_info = null;
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/server_php_info').then(res => this.$scope.server_php_info = res.data.server_php_info);

      return this.$q.all([data_promise]);
    }
  }
  Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.initClass();

  return Admin_ServerPhpInfo_Ctrl_ServerPhpInfo.EXPORT_CTRL();
});
