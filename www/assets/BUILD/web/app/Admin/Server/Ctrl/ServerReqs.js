define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_ServerReqs_Ctrl_ServerReqs extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerReqs_Ctrl_ServerReqs';
      this.CTRL_AS   = 'ServerReqs';
      this.DEPS      = [];
    }

    init() {
      return this.$scope.server_reqs = null;
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/server_reqs').then( res => {
        return this.$scope.check_requirements_url = res.data.check_requirements_url;
      });

      return this.$q.all([data_promise]);
    }
  }
  Admin_ServerReqs_Ctrl_ServerReqs.initClass();

  return Admin_ServerReqs_Ctrl_ServerReqs.EXPORT_CTRL();
});
