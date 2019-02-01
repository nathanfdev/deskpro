define(() => {
  class AdminUpdateWatcher_Ctrl_Main {
    static initClass() {
      this.CTRL_AS   = 'Ctrl';
      this.CTRL_ID   = 'AdminUpdateWatcher_Ctrl_Main';
      this.DEPS      = ['$q', '$timeout', '$scope', '$http', '$interval'];
    }

    static EXPORT_CTRL() {
      const ctrl_def = this.DEPS.slice(0);
      ctrl_def.push(this);
      if (!window.DP_CTRL_REG) {
        window.DP_CTRL_REG = [];
      }

      window.DP_CTRL_REG.push([this.CTRL_ID, ctrl_def]);
      return this;
    }

    constructor(...args) {
      this.ctrl_is_loading = true;
      if (this.constructor.DEPS.length !== args.length) {
        console.error('Dependencies are not the same as passed args: %o != %o', this.constructor.DEPS, args);
        return;
      }

      for (let i = 0; i < args.length; i++) {
        const arg = args[i];
        const arg_name = this.constructor.DEPS[i];
        if (arg_name) {
          this[arg_name] = arg;
        }
      }

      if (this.constructor.CTRL_AS) {
        this.$scope[this.constructor.CTRL_AS] = this;
      }


      this.has_init = false;
      this.init();
      this.has_init = true;
    }

    init() {
      this.$scope.showFinishedNextInfo = false;
      this.$scope.logUrl = `${window.DP_BASE_URL}/__serverinfo/logs/updater?auth=${window.DP_SERVERINFO_AUTH}`;
      this.refreshStatus().then(() => this.$scope.initDone = true);

      return this.timeId = this.$interval(() => this.refreshStatus()
      , 3500);
    }

    refreshStatus() {
      return this.$http.get(`${window.DP_BASE_URL}/admin/updater-status/${window.DP_SERVERINFO_AUTH}?status`).then((res) => {
        // if the status starts on anything but finished, then
        // the UI should not show the 'next' notice
        if ((this.$scope.info != null ? this.$scope.info.status : undefined) !== 'finished') {
          this.$scope.showFinishedNextInfo = true;
        }

        return this.$scope.info = res.data;
      });
    }
  }
  AdminUpdateWatcher_Ctrl_Main.initClass();

  return AdminUpdateWatcher_Ctrl_Main.EXPORT_CTRL();
});
