define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_Settings_Ctrl_ResetHelpdesk extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Settings_Ctrl_ResetHelpdesk';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$interval'];
    }

    init() {
      this.$scope.status = {};
      return this.$scope.$on('$destroy', () => this.interval && this.$interval.cancel(this.interval));
    }


    initialLoad() {
      return this.Api.sendGet('/reset-helpdesk/status').then(res => this.status(res.data));
    }


    status(data) {
      this.$scope.status = data;
      if (data.waiting && !this.interval) {
        const refresh = () => this.Api.sendGet('/reset-helpdesk/status').then(res => this.status(res.data));
        this.interval = this.$interval(refresh, 5000);
      }
      if (!data.waiting && this.interval) {
        this.$interval.cancel(this.interval);
        return this.interval = null;
      }
    }


    save() {
      if (!this.$scope.form_props || this.$scope.form_props.$invalid) { return; }
      const post = this.$scope.form;
      this.$scope.form = {};
      return this.Api.sendPostJson('/reset-helpdesk', post).then(res => this.status(res.data));
    }
  }
  Admin_Settings_Ctrl_ResetHelpdesk.initClass();


  return Admin_Settings_Ctrl_ResetHelpdesk.EXPORT_CTRL();
});
