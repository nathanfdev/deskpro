define([
  'Admin/CustomFields/Base/Ctrl/Edit',
], (
  Admin_CustomFields_Base_Ctrl_Edit
) => {
  class Admin_CustomFields_Kb_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomFields_Kb_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = [];
    }

    getDataService() {
      return this.DataService.get('KbFields');
    }

    getBaseRouteName() {
      return 'portal.kb_custom_fields';
    }

    type() {
      return 'kb';
    }
  }
  Admin_CustomFields_Kb_Ctrl_Edit.initClass();

  return Admin_CustomFields_Kb_Ctrl_Edit.EXPORT_CTRL();
});
