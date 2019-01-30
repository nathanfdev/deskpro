/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_UserRules_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_UserRules_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$stateParams', 'Api'];
    }

    init() {
      this.userRulesData = this.DataService.get('UserRules');
      this.user_rule = null;
      this.apply_log = '';
      return this.apply_started = false;
    }

    initialLoad() {
      const promise = this.userRulesData.loadEditUserRuleData(this.$stateParams.id || null).then( data => {
        this.user_rule  = data.user_rule;
        return this.form = data.form;
      });
      return promise;
    }

    saveForm() {

      const is_new = !this.user_rule.id;
      const promise = this.userRulesData.saveFormModel(this.user_rule, this.form);

      this.startSpinner('saving');
      return promise.then( () => {
        this.stopSpinner('saving', true).then(() => {
          return this.Growl.success("Saved");
        });

        this.skipDirtyState();
        if (is_new) {
          return this.$state.go('crm.rules.gocreate');
        }
      });
    }

    /*
     * Applying current user rule to all users
     */
    applyRuleToUsers() {
      let page = -1;
      this.apply_started = true;
      this.apply_page = '0';
      this.apply_num_pages = '?';

      var doRequest = () => {
        page++;
        return this.Api.sendGet(`/user_rules_apply/${this.user_rule.id}/page_${page}`).success( result => {
          if (!result.completed && result.success) {
            this.apply_page = result.page;
            this.apply_num_pages = result.num_pages;
            return doRequest();
          } else {
            this.apply_done = true;
            return this.apply_done_status = 'success';
          }
        }).error( () => {
          this.apply_done = true;
          return this.apply_done_status = 'error';
        });
      };

      return doRequest();
    }
  }
  Admin_UserRules_Ctrl_Edit.initClass();

  return Admin_UserRules_Ctrl_Edit.EXPORT_CTRL();
});