define([
  'Admin/CustomFields/Base/Ctrl/Edit',
], function(
  Admin_CustomFields_Base_Ctrl_Edit
) {
  class Admin_CustomFields_Chat_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomFields_Chat_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = [];
    }

    getDataService() {
      return this.DataService.get('ChatFields');
    }

    getBaseRouteName() {
      return "chat.fields";
    }

    type() {
      return 'chats';
    }
  }
  Admin_CustomFields_Chat_Ctrl_Edit.initClass();

  return Admin_CustomFields_Chat_Ctrl_Edit.EXPORT_CTRL();
});