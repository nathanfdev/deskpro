// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  var Admin_TicketAccounts_Ctrl_Settings = (function() {
    let _url = undefined;
    Admin_TicketAccounts_Ctrl_Settings = class Admin_TicketAccounts_Ctrl_Settings extends Admin_Ctrl_Base {
      static initClass() {
        this.CTRL_ID   = 'Admin_TicketAccounts_Ctrl_Settings';
        this.CTRL_AS   = 'Settings';
        this.DEPS      = [];
  
        _url = '/email_accounts/settings';
      }

      init() {
        return this.$scope.settings = null;
      }

      initialLoad() {
        return this.Api.sendGet(_url).then(res => {
          this.$scope.settings = res.data.email_settings;
          this.$scope.maxUploadSize = res.data.max_filesize;

          if (this.$scope.settings.attach_user_must_exts.length) {
            this.$scope.attach_user_exts_limitmode = 'allow';
          } else if (this.$scope.settings.attach_user_not_exts.length) {
            this.$scope.attach_user_exts_limitmode = 'disallow';
          } else {
            this.$scope.attach_user_exts_limitmode = 'any';
          }

          if (this.$scope.settings.attach_agent_must_exts.length) {
            this.$scope.attach_agent_exts_limitmode = 'allow';
          } else if (this.$scope.settings.attach_agent_not_exts.length) {
            this.$scope.attach_agent_exts_limitmode = 'disallow';
          } else {
            this.$scope.attach_agent_exts_limitmode = 'any';
          }

          if (this.$scope.settings.core_tickets_reject_spf_level) {
            this.$scope.settings.core_tickets_reject_fail_spf = true;
          }

          if (this.$scope.settings.core_tickets_reject_dkim_level) {
            return this.$scope.settings.core_tickets_reject_fail_dkim = true;
          }
        });
      }



      save() {
        if (this.$scope.attach_user_exts_limitmode === 'allow') {
          this.$scope.settings.attach_user_not_exts = [];
        } else if (this.$scope.attach_user_exts_limitmode === 'disallow') {
          this.$scope.settings.attach_user_must_exts = [];
        } else {
          this.$scope.settings.attach_user_not_exts = [];
          this.$scope.settings.attach_user_must_exts = [];
        }

        if (this.$scope.attach_agent_exts_limitmode === 'allow') {
          this.$scope.settings.attach_agent_not_exts = [];
        } else if (this.$scope.attach_agent_exts_limitmode === 'disallow') {
          this.$scope.settings.attach_agent_must_exts = [];
        } else {
          this.$scope.settings.attach_agent_not_exts = [];
          this.$scope.settings.attach_agent_must_exts = [];
        }

        if (!this.$scope.settings.core_tickets_reject_fail_spf) {
          this.$scope.settings.core_tickets_reject_spf_level = null;
        }

        if (!this.$scope.settings.core_tickets_reject_spf_level) {
          this.$scope.settings.core_tickets_reject_fail_spf = false;
        }

        if (!this.$scope.settings.core_tickets_reject_fail_dkim) {
          this.$scope.settings.core_tickets_reject_dkim_level = null;
        }

        if (!this.$scope.settings.core_tickets_reject_dkim_level) {
          this.$scope.settings.core_tickets_reject_fail_dkim = false;
        }

        const postData = {
          settings: this.$scope.settings
        };

        this.startSpinner('saving');
        return this.Api.sendPutJson(_url, postData).success( () => {
          this.stopSpinner('saving');
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        }).error( (info, code) => {
          this.stopSpinner('saving', true);
          return this.applyErrorResponseToView(info);
        });
      }
    };
    Admin_TicketAccounts_Ctrl_Settings.initClass();
    return Admin_TicketAccounts_Ctrl_Settings;
  })();



  return Admin_TicketAccounts_Ctrl_Settings.EXPORT_CTRL();
});
