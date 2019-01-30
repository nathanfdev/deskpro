/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Usersources/Ctrl/EditDeskproInstance', 'DeskPRO/Util/Util']
, function(Admin_Usersources_Ctrl_EditDeskproInstance, Util) {
  class Admin_Usersources_Ctrl_AddDeskproInstance extends Admin_Usersources_Ctrl_EditDeskproInstance {
    static initClass() {
      this.CTRL_ID   = 'Admin_Usersources_Ctrl_AddDeskproInstance';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$http', 'dpTemplateManager'];
    }

    getInstanceId() { return null; }
    initialLoad() {
      this.usersource = {
        title: 'Deskpro',
        is_disabled: false,
        options: {
          reg_enabled: true
        }
      };

      return super.initialLoad();
    }

    loadPasswordSettings() {
      return this.Api.sendGet('/password_settings', {rate_limit_context: 'user'}).then( res => {
        this.$scope.policy_settings = {
          sessions_lifetime:              res.data.settings.sessions_lifetime,
          session_keepalive_require_page: res.data.settings.session_keepalive_require_page,
          ip_security_enabled:            res.data.settings.ip_security_enabled,
          ip_security_mode:               res.data.settings.ip_security_mode || 'admins',
          ip_security_whitelist_lifetime: res.data.settings.ip_security_whitelist_lifetime + "",
          disable_notifications:          res.data.settings.disable_notifications,
          enable_agent_rememberme:        res.data.settings.enable_agent_rememberme,
          enable_user_rememberme:         res.data.settings.enable_user_rememberme,
          agent_enable_kb_shortcuts:      res.data.settings.agent_enable_kb_shortcuts,
        };

        this.$scope.rate_limit_settings = res.data.rate_limit_settings;

        this.$scope.policy_agent = res.data.settings.agent;
        this.$scope.policy_user  = res.data.settings.user;

        this.$scope.policy_agent.standard_policy = (this.$scope.policy_agent.min_length === 5) &&
            !this.$scope.policy_agent.max_age &&
            !this.$scope.policy_agent.forbid_reuse &&
            !this.$scope.policy_agent.require_num_uppercase &&
            !this.$scope.policy_agent.require_num_lowercase &&
            !this.$scope.policy_agent.require_num_number &&
            !this.$scope.policy_agent.require_num_symbol;

        return this.$scope.policy_user.standard_policy = (this.$scope.policy_user.min_length === 5) &&
            !this.$scope.policy_user.max_age &&
            !this.$scope.policy_user.forbid_reuse &&
            !this.$scope.policy_user.require_num_uppercase &&
            !this.$scope.policy_user.require_num_lowercase &&
            !this.$scope.policy_user.require_num_number &&
            !this.$scope.policy_user.require_num_symbol;
      });
    }

    loadRegSettings() {
      return this.Api.sendGet('/registration_settings', {rate_limit_context: 'user'}).then( res => {
        this.$scope.settings = res.data.registration_settings;
        this.settings = angular.copy(this.$scope.settings);
        return this.$scope.rate_limit_settings = res.data.rate_limit_settings;
      });
    }

    savePolicySettings() {

      const settings = this.$scope.policy_settings;
      settings.agent = Util.clone(this.$scope.policy_agent, true);
      settings.user  = Util.clone(this.$scope.policy_user, true);

      if (settings.agent.standard_policy) {
        settings.agent.min_length = 5;
        settings.agent.max_age = 0;
        settings.agent.forbid_reuse = false;
        settings.agent.require_num_uppercase = 0;
        settings.agent.require_num_lowercase = 0;
        settings.agent.require_num_number = 0;
        settings.agent.require_num_symbol = 0;
      }
      if (settings.user.standard_policy) {
        settings.user.min_length = 5;
        settings.user.max_age = 0;
        settings.user.forbid_reuse = false;
        settings.user.require_num_uppercase = 0;
        settings.user.require_num_lowercase = 0;
        settings.user.require_num_number = 0;
        settings.user.require_num_symbol = 0;
      }

      delete settings.agent.standard_policy;
      delete settings.user.standard_policy;

      const post = {
        settings,
        rate_limit_settings: this.$scope.rate_limit_settings,
        rate_limit_context: 'agent'
      };

      return this.Api.sendPostJson('/password_settings', post);
    }

    saveRegSettings() {
      const postData = {
        registration_settings: this.$scope.settings,
        rate_limit_settings: this.$scope.rate_limit_settings,
        rate_limit_context: 'user'
      };

      return this.Api.sendPostJson('/registration_settings', postData);
    }

    doSaveUsersource() {
      this.usersource.is_enabled = !this.usersource.is_disabled;
      if (this.usersource.options.reg_enabled) {
        this.usersource.is_enabled = true;
      }

      const postData = {
        title: this.usersource.title || 'Deskpro',
        is_enabled: this.usersource.is_enabled,
        options: this.usersource.options,
        brands: this.$scope.usersource_detailsv2.brands,
        is_all_brands: this.$scope.usersource_detailsv2.is_all_brands
      };

      const p = this.Api2.sendPostJson(`/user_sources/${this.usersourceType}`, postData).then(
        () => {
          this.listCtrl().refresh();
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        },
        res => {
          const msg = this.getRegisteredMessage(res.data.error_code) || res.data.error_message || '';
          return this.Growl.error(msg);
      });

      return this.$q.all([p, this.savePolicySettings(), this.saveRegSettings()]);
    }
  }
  Admin_Usersources_Ctrl_AddDeskproInstance.initClass();

  return Admin_Usersources_Ctrl_AddDeskproInstance.EXPORT_CTRL();
});
