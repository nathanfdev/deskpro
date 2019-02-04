define([
  'Admin/Main/Ctrl/Base',
  'Admin/Apps/Ctrl/EditInstance',
  'Admin/Usersources/Helper/UsersourceTypeDecider'
], (
  Admin_Ctrl_Base, Admin_Apps_Ctrl_EditInstance, Admin_Usersources_Helper_UsersourceTypeDecider
) => {
  class Admin_Usersources_Ctrl_Edit extends Admin_Apps_Ctrl_EditInstance {
    static initClass() {
      this.CTRL_ID = 'Admin_Usersources_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS = ['$stateParams'];
    }

    init() {
      this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
      this.usersourcesDataService = this.DataService.get('Usersources');
      return this.usersourceId = this.$stateParams.id;
    }

    initialLoad() {
      const promise = this.Api.sendGet(`/usersources/${this.usersourceType}/${this.usersourceId}`).then(result => this.usersource = result.data.usersource);
      return promise;
    }
  }
  Admin_Usersources_Ctrl_Edit.initClass();

  return Admin_Usersources_Ctrl_Edit.EXPORT_CTRL();
});
