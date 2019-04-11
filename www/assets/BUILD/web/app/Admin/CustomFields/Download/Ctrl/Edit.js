define([
  'Admin/CustomFields/Base/Ctrl/Edit',
], (
  Admin_CustomFields_Base_Ctrl_Edit
) => {
  class Admin_CustomFields_Download_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomFields_Download_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = [];
    }

    getDataService() {
      return this.DataService.get('DownloadFields');
    }

    getBaseRouteName() {
      return 'portal.download_custom_fields';
    }

    type() {
      return 'download';
    }
  }
  Admin_CustomFields_Download_Ctrl_Edit.initClass();

  return Admin_CustomFields_Download_Ctrl_Edit.EXPORT_CTRL();
});
