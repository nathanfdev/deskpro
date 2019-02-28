define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_TwitterSetup_Ctrl_TwitterSetup extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TwitterSetup_Ctrl_TwitterSetup';
      this.CTRL_AS   = 'TwitterSetup';
      this.DEPS      = [];
    }

    init() {
      this.setup = null;

      return this.$scope.times = [
        { id: 86400, label: '1 day' },
        { id: 259200, label: '3 days' },
        { id: 432000, label: '5 days' },
        { id: 604800, label: '1 week' },
        { id: 1209600, label: '2 weeks' },
        { id: 1814400, label: '3 weeks' },
        { id: 2592000, label: '1 month' },
        { id: 5184000, label: '2 months' },
        { id: 7776000, label: '3 months' },
        { id: 15552000, label: '6 months' },
        { id: 23328000, label: '9 months' },
        { id: 31536000, label: '1 year' },
        { id: 63072000, label: '2 years' }
      ];
    }

    initialLoad() {
      const data_promise = this.Api.sendDataGet({
        twitter_setup: '/twitter_setup'
      }).then((res) => {
        this.$scope.setup = res.data.twitter_setup.twitter_setup;
        return this.setup = angular.copy(this.$scope.setup);
      });

      return this.$q.all([data_promise]);
    }

    isDirtyState() {
      if (!this.setup) { return false; }
      if (!angular.equals(this.setup, this.$scope.setup)) {
        return true;
      }
      return false;
    }

    save() {
      let promise;
      if (!this.$scope.form_props.$valid) {
        return;
      }

      const postData = {
        twitter_setup: this.$scope.setup
      };

      this.startSpinner('saving');
      return promise = this.Api.sendPostJson('/twitter_setup', postData).success(() => {
        this.setup = angular.copy(this.$scope.setup);

        return this.stopSpinner('saving').then(() => this.Growl.success(this.getRegisteredMessage('saved_setup')));
      }).error((info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_TwitterSetup_Ctrl_TwitterSetup.initClass();

  return Admin_TwitterSetup_Ctrl_TwitterSetup.EXPORT_CTRL();
});
