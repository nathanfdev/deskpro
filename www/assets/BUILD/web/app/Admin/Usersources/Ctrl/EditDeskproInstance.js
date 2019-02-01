define(['Admin/Usersources/Ctrl/EditInstance', 'DeskPRO/Util/Util']
, (Admin_Usersources_Ctrl_EditInstance, Util) => {
  class Admin_Usersources_Ctrl_EditDeskproInstance extends Admin_Usersources_Ctrl_EditInstance {
    static initClass() {
      this.CTRL_ID   = 'Admin_Usersources_Ctrl_EditDeskproInstance';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$http', 'dpTemplateManager'];
    }

    getApp2Id() { return this.instanceId; }
    initialLoad() {
      this.is_local = true;
      const p = super.initialLoad();
      return this.$q.all([p, this.loadPasswordSettings(), this.loadRegSettings()]).then(() => this.usersource.is_disabled = !this.usersource.is_enabled);
    }

    doSaveUsersource() {
      this.usersource.is_enabled = !this.usersource.is_disabled;
      if (this.usersource.options.reg_enabled) {
        this.usersource.is_enabled = true;
      }
      const p = super.doSaveUsersource();
      return this.$q.all([p, this.savePolicySettings(), this.saveRegSettings()]);
    }

    loadPasswordSettings() {
      return this.Api.sendGet('/password_settings', { rate_limit_context: 'user' }).then((res) => {
        this.$scope.policy_settings = {
          sessions_lifetime:              res.data.settings.sessions_lifetime,
          session_keepalive_require_page: res.data.settings.session_keepalive_require_page,
          ip_security_enabled:            res.data.settings.ip_security_enabled,
          ip_security_mode:               res.data.settings.ip_security_mode || 'admins',
          ip_security_whitelist_lifetime: `${res.data.settings.ip_security_whitelist_lifetime}`,
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
      return this.Api.sendGet('/registration_settings', { rate_limit_context: 'user' }).then((res) => {
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
        rate_limit_context:  'agent'
      };

      return this.Api.sendPostJson('/password_settings', post);
    }

    saveRegSettings() {
      const postData = {
        registration_settings: this.$scope.settings,
        rate_limit_settings:   this.$scope.rate_limit_settings,
        rate_limit_context:    'user'
      };

      return this.Api.sendPostJson('/registration_settings', postData);
    }

    /*
     * SHow delete modal
     */
    startDelete($event) {
      if ($event) {
        $event.preventDefault();
      }

      const doDelete = () => this.Api2.sendDelete(`user_sources/${this.usersourceType}/${this.getApp2Id()}`).success(() => {
          // If we are viewing with the parent list, we need to remove this
          // app from the list
        this.listCtrl().refresh();

          // close this view
        return this.$state.go('^');
      });

      return this.$modal.open({
        templateUrl: this.getTemplatePath('Usersources/deskpro-delete-modal.html'),
        controller:  ['app', '$scope', '$modalInstance', function (app, $scope, $modalInstance) {
          $scope.app = app;
          $scope.dismiss = () => $modalInstance.close();

          return $scope.confirm = function () {
            $scope.is_loading = true;
            return doDelete().then(() => $modalInstance.close());
          };
        }
        ],
        resolve: {
          app: () => this.app
        }
      });
    }
  }
  Admin_Usersources_Ctrl_EditDeskproInstance.initClass();

  return Admin_Usersources_Ctrl_EditDeskproInstance.EXPORT_CTRL();
});
