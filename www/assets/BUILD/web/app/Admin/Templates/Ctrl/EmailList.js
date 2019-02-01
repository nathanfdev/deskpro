define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Templates_Ctrl_EmailList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Templates_Ctrl_EmailList';
      this.CTRL_AS   = 'ListCtrl';
    }

    init() {
      const parts = this.$stateParams.groupName.split(':');
      this.typeId = parts.shift();
      return this.groupId = parts.shift() || this.typeId;
    }


    initialLoad() {
      const promise = this.Api.sendDataGet({
        info: '/email-templates-info'
      }).then(res => this.templates = res.data.info.list[this.typeId].groups[this.groupId].templates);
      return promise;
    }

    /*
     * Open an editor
     */
    openEditor(tpl) {
      let modalInstance;
      if (!tpl.type || (tpl.type === 'email')) {
        modalInstance = this.$modal.open({
          templateUrl: this.getTemplatePath('Templates/modal-email-editor.html'),
          controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
          resolve:     {
            templateName() {
              return tpl.name;
            }
          }
        }).result.then((info) => {
          if (info.mode === 'custom') {
            return tpl.is_custom = true;
          } else if (info.mode === 'revert') {
            tpl.is_custom = false;

            // if this is a custom template group, then revert means delete
            if ((this.typeId === 'custom') && (this.groupId === 'custom')) {
              return this.templates = this.templates.filter(x => x !== tpl);
            }
          }
        });
      } else {
        modalInstance = this.$modal.open({
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
      }

      return modalInstance;
    }

    /*
      * Opens email editor in 'new' mode
    */
    createNewEmailTemplate() {
      const modalInstance = this.$modal.open({
        templateUrl: this.getTemplatePath('Templates/modal-email-editor.html'),
        controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
        resolve:     {
          templateName() {
            return null;
          }
        }
      }).result.then((info) => {
        const title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
        const tpl = {
          typeId:  this.typeId,
          groupId: this.groupId,
          name:    info.templateName,
          title
        };

        return this.templates.push(tpl);
      });

      return modalInstance;
    }
  }
  Admin_Templates_Ctrl_EmailList.initClass();

  return Admin_Templates_Ctrl_EmailList.EXPORT_CTRL();
});
