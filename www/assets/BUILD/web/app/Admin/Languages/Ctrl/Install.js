define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Languages_Ctrl_Install extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Languages_Ctrl_Install';
      this.CTRL_AS = 'EditCtrl';
    }

    init() {
      return this.id = this.$stateParams.id.replace(/^install\-/, '');
    }

    initialLoad() {
      const promise = this.Api.sendGet(`/langs/${this.id}`).then((result) => {
        if (result.data.language) {
          this.$state.go('setup.languages.edit', { id: this.id });
          return;
        }

        this.pack = result.data.pack;
        return this.lang = result.data.language;
      });
      return promise;
    }

    doInstall() {
      this.startSpinner('saving');
      return this.$scope.$parent.ListCtrl.installLang(this.id).then(() => {
        this.stopSpinner('saving', true);
        return this.$state.go('setup.languages.edit', { id: this.id });
      });
    }
  }
  Admin_Languages_Ctrl_Install.initClass();

  return Admin_Languages_Ctrl_Install.EXPORT_CTRL();
});
