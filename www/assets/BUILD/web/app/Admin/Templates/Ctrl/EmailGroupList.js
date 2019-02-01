define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Templates_Ctrl_EmailGroupList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Templates_Ctrl_EmailGroupList';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    initialLoad() {
      const promises = [];
      promises.push(this.Api.sendDataGet({
        info: '/email-templates-info'
      }).then(res => this.templateInfo = res.data.info.list));
      if (window.DP_HAS_NEW_EMAILS) {
        promises.push(this.loadLegacyTemplates());
      }

      return this.$q.all(promises);
    }

    loadLegacyTemplates() {
      return this.Api2.sendGet('/email_templates/legacy_templates').then(
        res => this.$scope.toUpgradeTemplates = res.data);
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

    revertTemplate(id, triggers) {
      const message = triggers ? 'Are you sure you want to revert this template? The associated triggers will send the default template.' :
        'Are you sure you want to revert this template? Your changes will be completely lost and the template will be returned to the default.';
      return this.showConfirm(message).result.then(() => this.Api2.sendGet(`/email_templates/revert_legacy_template/${id}`).then(
          () => this.loadLegacyTemplates()));
    }

    deleteTemplate(id, triggers) {
      const message = triggers ? 'Are you sure you want to delete this template? The associated triggers actions will be also deleted.' :
        'Are you sure you want to delete this template? Your changes will be completely lost.';
      return this.showConfirm(message).result.then(() => this.Api2.sendDelete(`/email_templates/legacy_template/${id}`).then(
          () => this.loadLegacyTemplates()));
    }
  }
  Admin_Templates_Ctrl_EmailGroupList.initClass();

  return Admin_Templates_Ctrl_EmailGroupList.EXPORT_CTRL();
});
