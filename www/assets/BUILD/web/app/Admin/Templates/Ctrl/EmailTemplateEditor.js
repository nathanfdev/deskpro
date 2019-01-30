/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
  class Admin_Templates_Ctrl_EmailTemplateEditor extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Templates_Ctrl_EmailTemplateEditor';
      this.CTRL_AS   = 'EmailTemplateEditor';
      this.DEPS      = ['$modalInstance', 'templateName', 'dpTemplateManager', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria'];
    }

    init() {

      this.$scope.is_new_email = this.templateName === null;
      if (this.$scope.is_new_email) {
        this.$scope.$watch('email.email_name', () => {
          this.$scope.email.email_name = this.$scope.email.email_name || '';
          this.$scope.email.email_name = this.$scope.email.email_name.toLowerCase();
          this.$scope.email.email_name = this.$scope.email.email_name.replace(/\s/g, '-');
          this.$scope.email.email_name = this.$scope.email.email_name.replace(/[^a-z0-9\-_\.]/g, '');
          return this.validateName();
        });
      }

      this.$scope.dismiss = () => {
        return this.$modalInstance.dismiss('cancel');
      };

      this.$scope.save = () => {
        let url;
        this.$scope.is_error = false;
        this.$scope.saving_template = true;
        const postData = {
          template: {
            subject: this.editorSubject.getValue(),
            body: this.editorMessage.getValue()
          }
        };

        if (this.$scope.is_new_email && (!this.$scope.email.email_name || (Strings.trim(this.$scope.email.email_name) === ""))) {
          this.$scope.saving_template = false;
          this.$scope.is_error = true;
          this.$scope.syntax_error  = false;
          this.$scope.error_message = 'You must specify a template name';
          return;
        }

        if (this.$scope.is_new_email) {
          url = `/templates/DeskPRO:emails_custom:${this.$scope.email.email_name}.html.twig`;
          postData.create_new = true;
        } else {
          url = `/templates/${this.templateName}`;
        }

        return this.Api.sendPostJson(url, postData).then( res => {
          this.$scope.saving_template = false;

          if (this.$scope.is_new_email) {
            const tpl = {
              name: res.data.name,
              title: res.data.name.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html')
            };
            if (this.dpObTypesDefTicketActions.options_data) {
              this.dpObTypesDefTicketActions.options_data.custom_email_tpls.push(tpl);
            }
            if (this.dpObTypesDefTicketCriteria.options_data) {
              this.dpObTypesDefTicketCriteria.options_data.custom_email_tpls.push(tpl);
            }
          }

          return this.$modalInstance.close({
            templateName: res.data.name,
            isNewEmail:   this.$scope.is_new_email,
            mode:         'custom'
          });
        }
        , result => {
          this.$scope.saving_template = false;
          this.$scope.is_error = true;
          this.$scope.syntax_error  = result.data.error_syntax || false;
          this.$scope.syntax_line   = result.data.error_line || 0;
          return this.$scope.error_message = result.data.error_message || 'Unknown';
        });
      };

      this.$scope.revert = () => {
        return this.showConfirm('Are you sure you want to revert this template? Your changes will be completely lost and the template will be returned to the default.').result.then(() => {
          this.$scope.saving_template = true;
          return this.Api.sendDelete(`/templates/${this.templateName}`).then(() => {
            this.$scope.saving_template = false;
            return this.$modalInstance.close({
              templateName: this.templateName,
              mode: 'revert'
            });
          });
        });
      };

      this.$scope.aceLoadedSubject = editor => {
        this.editorSubject = editor;
        const maxH = $(editor.container).data('max-height') || 150;
        const updateH = function() {
          let newHeight = (editor.getSession().getScreenLength() * editor.renderer.lineHeight) + editor.renderer.scrollBar.getWidth();
          if (newHeight > maxH) {
            newHeight = maxH;
          }
          if (newHeight < 18) {
            newHeight = 18;
          }

          $(editor.container).height(newHeight);
          return editor.resize();
        };

        updateH();
        editor.getSession().on('change', updateH);
        return editor.setShowPrintMargin(false);
      };

      return this.$scope.aceLoadedMessage = editor => {
        this.editorMessage = editor;
        const maxH = $(editor.container).data('max-height') || 500;
        const updateH = function() {
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

    validateName() {
      this.$scope.email_name_error = null;
      if (!this.customNames) { return; } // custom names might not be loaded yet
      if (this.customNames.indexOf(this.$scope.email.email_name + '.html') !== -1) {
        return this.$scope.email_name_error = 'exists';
      }
    }

    initialLoad() {
      let p;
      if (!this.$scope.is_new_email) {
        p = this.Api.sendGet(`/templates/${this.templateName}`).success( data => {
          return this.initTemplateData(data);
        });

        return p;
      } else {
        this.initTemplateData({
          name: null,
          email: { email_name: '', template_code: { subject: '', body: '' } }
        });
        p = this.Api.sendGet('/email-templates-info').success( data => {
          return this.initCustomNames(data.list['custom'].groups['custom'].templates);
        });
        return p;
      }
    }

    initCustomNames(templates) {
      this.customNames = templates.map(x => x.showName.replace(/^.*?\//, ''));
    }

    initTemplateData(info) {
      this.templateName = info.name;
      this.email = info;
      return this.$scope.email = this.email;
    }
  }
  Admin_Templates_Ctrl_EmailTemplateEditor.initClass();

  return Admin_Templates_Ctrl_EmailTemplateEditor.EXPORT_CTRL();
});