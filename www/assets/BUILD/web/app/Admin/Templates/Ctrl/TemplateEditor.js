define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Templates_Ctrl_TemplateEditor extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Templates_Ctrl_TemplateEditor';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$modalInstance', 'templateName'];
    }

    init() {
      this.$scope.dismiss = () => this.$modalInstance.dismiss('cancel');

      this.$scope.save = () => {
        this.$scope.saving_template = true;
        const postData = {
          template: {
            code: this.editor.getValue()
          }
        };
        return this.Api.sendPostJson(`/templates/${this.templateName}`, postData).then(() => {
          this.$scope.saving_template = false;
          return this.$modalInstance.close({
            templateName: this.templateName,
            mode:         'custom'
          });
        }
        , (result) => {
          this.$scope.saving_template = false;
          this.$scope.is_error = true;
          this.$scope.syntax_error  = result.data.error_syntax || false;
          this.$scope.syntax_line   = result.data.error_line || 0;
          return this.$scope.error_message = result.data.error_message || 'Unknown';
        });
      };

      this.$scope.revert = () => this.showConfirm('Are you sure you want to revert this template? Your changes will be completely lost and the template will be returned to the default.').result.then(() => {
        this.$scope.saving_template = true;
        return this.Api.sendDelete(`/templates/${this.templateName}`).then(() => {
          this.$scope.saving_template = false;
          return this.$modalInstance.close({
            templateName: this.templateName,
            mode:         'revert'
          });
        });
      });

      return this.$scope.aceLoaded = (editor) => {
        this.editor = editor;
        const maxH = $(editor.container).data('max-height') || 500;
        const updateH = function () {
          let newHeight = (editor.getSession().getScreenLength() * editor.renderer.lineHeight) + editor.renderer.scrollBar.getWidth();
          if (newHeight > maxH) {
            newHeight = maxH;
          }
          if (newHeight < 85) {
            newHeight = 85;
          }

          $(editor.container).height(newHeight);
          return editor.resize();
        };

        updateH();
        editor.getSession().on('change', updateH);
        return editor.setShowPrintMargin(false);
      };
    }

    initialLoad() {
      const promise = this.Api.sendGet(`/templates/${this.templateName}`).success((data) => {
        this.tpl = data;
        this.$scope.tpl = this.tpl;
        return this.$scope.template_code = data.template_code.code;
      });
      return promise;
    }
  }
  Admin_Templates_Ctrl_TemplateEditor.initClass();

  return Admin_Templates_Ctrl_TemplateEditor.EXPORT_CTRL();
});
