define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Util, Arrays) {
  class Admin_TicketSettings_Ctrl_FwdSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketSettings_Ctrl_FwdSettings';
      this.CTRL_AS   = 'Fwd';
      this.DEPS      = [];
    }

    init() {
      this.default_fwd_regex = '/^(FW|FWD|VL|WG|FS|VB|RV|VS|TR):/i';
      this.email_accounts = [];
      return this.$scope.$watch('settings.use_account', accId => {
        accId = parseInt(accId);
        const a = Arrays.find(this.email_accounts, a => a.id === accId);
        return this.$scope.use_account_address = a ? a.address : "noreply@example.com";
      });
    }

    initialLoad() {
      const data_promise = this.Api.sendDataGet({
        'settings': '/ticket_settings/fwd',
        'accounts': '/email_accounts'
      }).then( res => {
        this.email_accounts = res.data.accounts.email_accounts;
        this.$scope.settings = res.data.settings.ticket_fwd_settings;

        if (this.$scope.settings.agent_fwd_subject_regex) {
          this.$scope.use_agent_fwd_subject_regex = true;
        } else {
          this.$scope.use_agent_fwd_subject_regex = false;
          this.$scope.settings.agent_fwd_subject_regex = this.default_fwd_regex;
        }

        this.settings = Util.clone(this.$scope.settings);

        return this.$scope.settings.use_account = (this.$scope.settings.use_account || 0)+"";
      });

      return this.$q.all([data_promise]);
    }

    isDirtyState() {
      return false;
      if (!this.settings) { return false; }
      if (!Util.equals(this.settings, this.$scope.settings)) {
        return true;
      } else {
        return false;
      }
    }

    save() {

      let promise;
      const postData = {
        ticket_fwd_settings: Util.clone(this.$scope.settings)
      };

      if (!this.$scope.use_agent_fwd_subject_regex || (this.$scope.settings.agent_fwd_subject_regex === this.default_fwd_regex)) {
        postData.ticket_fwd_settings.agent_fwd_subject_regex = null;
      }

      this.startSpinner('saving');
      return promise = this.Api.sendPostJson('/ticket_settings/fwd', postData).success( () => {
        this.settings = Util.clone(this.$scope.settings);

        return this.stopSpinner('saving').then(() => {
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        });
      }).error( (info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_TicketSettings_Ctrl_FwdSettings.initClass();

  return Admin_TicketSettings_Ctrl_FwdSettings.EXPORT_CTRL();
});