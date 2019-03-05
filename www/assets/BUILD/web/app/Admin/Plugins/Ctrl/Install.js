define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Plugins_Ctrl_Install extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Plugins_Ctrl_Install';
      this.CTRL_AS = 'InstallCtrl';
    }

    init() {
      return this.package = [];
    }

    initialLoad() {
      const promise = this.Api.sendGet(`/plugins/package/${this.$stateParams.name}/installer`).then(result => this.package = result.data.plugin_def);
      return promise;
    }
  }
  Admin_Plugins_Ctrl_Install.initClass();

  return Admin_Plugins_Ctrl_Install.EXPORT_CTRL();
});
