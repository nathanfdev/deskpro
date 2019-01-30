/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
  class Admin_Settings_Ctrl_PasswordSettings extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.initPolicy = this.initPolicy.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_Settings_Ctrl_PasswordSettings';
      this.CTRL_AS   = 'Settings';
      this.DEPS      = ['$upload', '$http'];
    }

    init() {
      return this.$scope.password_settings = {};
    }

    initialLoad() {
      this.Api.sendGet('/general_settings/blob').then(res => {
        return this.$scope.logo_blob = res.data;
      });

      const data_promise = this.Api.sendGet('/password_settings', {rate_limit_context: 'agent'}).then( res => {
        return this.initPolicy(res.data);
      });

      this.$scope.ip_white_listing_times = [
        {id: 86400, label: "1 day"},
        {id: 259200, label: "3 days"},
        {id: 432000, label: "5 days"},
        {id: 604800, label: "1 week"},
        {id: 1209600, label: "2 weeks"},
        {id: 1814400, label: "3 weeks"},
        {id: 2592000, label: "1 month"},
        {id: 5184000, label: "2 months"},
        {id: 7776000, label: "3 months"},
        {id: 15552000, label: "6 months"},
        {id: 23328000, label: "9 months"},
        {id: 31536000, label: "1 year"},
        {id: 63072000, label: "2 years"}
      ];

      return data_promise;
    }


    initPolicy(data) {
      this.$scope.settings = {
        sessions_lifetime:              data.settings.sessions_lifetime,
        session_keepalive_require_page: data.settings.session_keepalive_require_page,
        ip_security_enabled:            data.settings.ip_security_enabled,
        ip_security_mode:               data.settings.ip_security_mode || 'admins',
        ip_security_whitelist_lifetime: data.settings.ip_security_whitelist_lifetime + "",
        disable_notifications:          data.settings.disable_notifications,
        enable_agent_rememberme:        data.settings.enable_agent_rememberme,
        enable_user_rememberme:         data.settings.enable_user_rememberme,
        agent_enable_kb_shortcuts: data.settings.agent_enable_kb_shortcuts,
      };

      this.$scope.rate_limit_settings = data.rate_limit_settings;

      this.$scope.agent = data.settings.agent;
      this.$scope.user  = data.settings.user;

      this.$scope.agent.standard_policy = (this.$scope.agent.min_length === 5) &&
          !this.$scope.agent.max_age &&
          !this.$scope.agent.forbid_reuse &&
          !this.$scope.agent.require_num_uppercase &&
          !this.$scope.agent.require_num_lowercase &&
          !this.$scope.agent.require_num_number &&
          !this.$scope.agent.require_num_symbol;

      return this.$scope.user.standard_policy = (this.$scope.user.min_length === 5) &&
          !this.$scope.user.max_age &&
          !this.$scope.user.forbid_reuse &&
          !this.$scope.user.require_num_uppercase &&
          !this.$scope.user.require_num_lowercase &&
          !this.$scope.user.require_num_number &&
          !this.$scope.user.require_num_symbol;
    }

    saveSettings() {
      if (this.$scope.form_props.$invalid) { return; }
      this.startSpinner('saving');

      const { settings } = this.$scope;
      settings.agent = Util.clone(this.$scope.agent, true);
      settings.user  = Util.clone(this.$scope.user, true);

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

      return this.Api.sendPostJson('/password_settings', post).success(data => {
        return this.stopSpinner('saving').then(() => {
          this.initPolicy(data);
          const message = this.getRegisteredMessage('saved_settings');
          if (message && message.length) { return this.Growl.success(message); }
        });
      }).error( (info, code) => {
        return this.stopSpinner('saving', true);
      });
    }


    onFileSelect(files) {
      this.$scope.logo_uploading = true;
      const file = files[0];

      return this.$upload.upload({
        url: this.$http.formatApiUrl('/blobs'),
        file
      }).success( data => {
        this.$scope.logo_uploading = false;
        return this.Api.sendPost('/general_settings/blob', {blob_id: data.blob.id}).then(res => {
          return this.$scope.logo_blob = res.data;
      });
      }).error( data => {
        this.$scope.logo_uploading = false;
        return this.Growl.error((data != null ? data.error_message : undefined) || 'Error');
      });
    }
  }
  Admin_Settings_Ctrl_PasswordSettings.initClass();

  return Admin_Settings_Ctrl_PasswordSettings.EXPORT_CTRL();
});