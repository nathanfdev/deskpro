/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/CustomFields/Base/Ctrl/Edit',
], function(
  Admin_CustomFields_Base_Ctrl_Edit
) {
  class Admin_CustomFields_User_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomFields_User_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = [];
    }

    initialLoadExtra() {
      const promise = this.Api.sendGet('/apps?tags=usersources').then( result => {
        return this.us_apps = result.data.apps.filter(x => !x.package.is_custom);
      });
      return promise;
    }

    getDataService() {
      return this.DataService.get('UserFields');
    }

    getBaseRouteName() {
      return "crm.user_fields";
    }

    type() {
      return 'people';
    }
  }
  Admin_CustomFields_User_Ctrl_Edit.initClass();

  return Admin_CustomFields_User_Ctrl_Edit.EXPORT_CTRL();
});