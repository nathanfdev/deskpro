define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_Server_Ctrl_ServerEnc extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Server_Ctrl_ServerEnc';
      this.CTRL_AS   = 'ServerEnc';
      this.DEPS      = [];
    }

    init() {
    }

    loadStatus() {
      return this.Api.sendGet('/server/encryption/status').then(res => this.$scope.status = res.data);
    }

    reloadStatus() {
      this.startSpinner('saving');
      return this.loadStatus().then(() => this.stopSpinner('saving', true));
    }

    enable() {
      this.startSpinner('saving');
      this.$scope.error_info = null;
      return this.Api.sendPost('/server/encryption/enable').success(res => this.loadStatus().then(() => this.stopSpinner('saving', true))).error((info, code) => {
        this.stopSpinner('saving', true);
        if (info.error_code) {
          return this.$scope.error_info = info;
        }
      });
    }

    disable() {
      this.startSpinner('saving');
      this.$scope.error_info = null;
      return this.Api.sendPost('/server/encryption/disable').success(res => this.loadStatus().then(() => this.stopSpinner('saving', true))).error((info, code) => {
        this.stopSpinner('saving', true);
        if (info.error_code) {
          return this.$scope.error_info = info;
        }
      });
    }

    initialLoad() {
      return this.loadStatus();
    }
  }
  Admin_Server_Ctrl_ServerEnc.initClass();

  return Admin_Server_Ctrl_ServerEnc.EXPORT_CTRL();
});
