define(['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) => {
  class Admin_Templates_Ctrl_TemplateList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Templates_Ctrl_TemplateList';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {
      const parts = this.$stateParams.groupName.split(':');
      this.bundle = parts.shift();
      return this.tplDir = parts.shift();
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        info: '/templates-info'
      }).then(res => this.templates = res.data.info.list[this.bundle][this.tplDir].templates);
      return promise;
    }

    /*
     * Open an editor
     */
    openEditor(tpl) {
      const modalInstance = this.$modal.open({
        templateUrl: this.getTemplatePath('Templates/modal-template-editor.html'),
        controller:  'Admin_Templates_Ctrl_TemplateEditor',
        resolve:     {
          templateName() {
            return tpl.name;
          }
        }
      }).result.then((info) => {
        if (info.mode === 'custom') {
          return tpl.is_custom = true;
        } else if (info.mode === 'revert') {
          return tpl.is_custom = false;
        }
      });

      return modalInstance;
    }
  }
  Admin_Templates_Ctrl_TemplateList.initClass();

  return Admin_Templates_Ctrl_TemplateList.EXPORT_CTRL();
});
