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

    isEulaFieldExist() {
      return this.fieldDataService ? this.fieldDataService.isEulaFieldExist() : false;
    }

    editEulaClickCallback(event, category) {
      event.stopPropagation();
      event.preventDefault();

      let editCategory = {
        eula: category.hasOwnProperty('eula') ? category.eula : '',
        eula_format: category.hasOwnProperty('eula_format') ? category.eula_format : 'html',
      };
      this.$modal.open({
        templateUrl: this.getTemplatePath('CustomFields/Download/edit-eula-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.category = editCategory;
          $scope.doSave = () => {
            category.eula = editCategory.eula;
            category.eula_format = editCategory.eula_format;
            $modalInstance.dismiss();
          }
        }]
      });
    }
  }
  Admin_CustomFields_Download_Ctrl_Edit.initClass();

  return Admin_CustomFields_Download_Ctrl_Edit.EXPORT_CTRL();
});
