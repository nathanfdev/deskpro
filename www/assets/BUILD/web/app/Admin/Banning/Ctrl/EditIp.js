/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base', 'angular'
], function(
  Admin_Ctrl_Base, angular
) {
  class Admin_Banning_Ctrl_EditIp extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Banning_Ctrl_EditIp';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$stateParams'];
    }

    init() {
      this.banData = this.DataService.get('Bans');
      this.banData.setType('ip');
      return this.ip_ban = null;
    }

    /*
  *
  */

    initialLoad() {
      const promise = this.banData.loadEditBanData(this.$stateParams.ban || null).then( data => {

        this.ip_ban = data.ip_ban;
        return this.form = data.form;
      });
      return promise;
    }

    /*
     *
   */

    saveForm() {

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.$stateParams.ban;

      const promise = this.banData.saveFormModel(this.ip_ban, this.form);

      this.startSpinner('saving');
      return promise.then( () => {
        this.stopSpinner('saving', true).then(() => {
          this.form = angular.copy(this.ip_ban);
          return this.Growl.success("Saved");
        });

        this.skipDirtyState();
        if (is_new) {
          return this.$state.go('crm.banning.gocreate_ip');
        } else {
          return this.$state.go('crm.banning.edit_ip', {ban: this.ip_ban.id});
        }
      });
    }
  }
  Admin_Banning_Ctrl_EditIp.initClass();

  return Admin_Banning_Ctrl_EditIp.EXPORT_CTRL();
});