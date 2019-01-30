// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'Admin/Usersources/Helper/UsersourceTypeDecider'
], function(
  Admin_Ctrl_Base,
  Admin_Usersources_Helper_UsersourceTypeDecider
) {
  class Admin_Usersources_Ctrl_New extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Usersources_Ctrl_New';
      this.CTRL_AS = 'NewCtrl';
      this.DEPS = ['$state'];
    }

    init() {
      this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
      this.$scope.install_url = this.usersourceType === 'user' ? 'crm.usersources.install' : 'agents.usersources.install';
      return this.$scope.install_deskpro_url = this.usersourceType === 'user' ? 'crm.usersources.install_deskpro' : 'agents.usersources.install_deskpro';
    }

    initialLoad() {
      const url = `/usersources/available/app-packages/${this.usersourceType}`;

      return this.Api.sendGet(url).then(res => {
        return this.packages = res.data;
      });
    }
  }
  Admin_Usersources_Ctrl_New.initClass();

  return Admin_Usersources_Ctrl_New.EXPORT_CTRL();
});