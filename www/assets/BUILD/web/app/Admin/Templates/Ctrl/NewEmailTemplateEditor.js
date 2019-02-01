define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], (Admin_Ctrl_Base, Strings) => {
  class Admin_Templates_Ctrl_NewEmailTemplateEditor extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        const thisFn = (() => this).toString();
        const thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.mountReactComponent = this.mountReactComponent.bind(this);
      this.onSave = this.onSave.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_Templates_Ctrl_NewEmailTemplateEditor';
      this.CTRL_AS   = 'NewEmailTemplateEditor';
      this.DEPS      = ['$modalInstance', 'templateName', 'dpTemplateManager', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria'];
    }

    init() {
      this.$scope.display_title = this.templateName;

      this.$scope.is_new_email = this.templateName === null;

      this.$scope.dismiss = () => this.$modalInstance.dismiss('cancel');

      return this.mountReactComponent();
    }

    mountReactComponent() {
      const element = document.getElementById('new-email-template-editor');


      if (element === null) {
        setTimeout(this.mountReactComponent, 1);
        return;
      }

      const modal = document.getElementById('new-email-template-editor-modal');

      modal.parentNode.parentNode.style.width = `${document.body.clientWidth * 0.9}px`;
      modal.parentNode.parentNode.parentNode.style.zIndex = 10;

      const backdrop = document.getElementsByClassName('modal-backdrop')[0];
      backdrop.style.zIndex = 10;
      element.style.height = `${(document.body.clientHeight * 0.9) - 51}px`;

      const reactProps = {
        routePath:   `emails/templates_editor/${this.templateName}`,
        template:    this.templateName,
        newTemplate: this.templateName === null,
        onSave:      this.onSave
      };

      window.AdminBundle.render(reactProps, element);

      return this.$scope.$on('$destroy', () => window.AdminBundle.unmount(element));
    }

    onSave(name) {
      if (this.$scope.is_new_email) {
        const tpl = {
          name,
          title: name.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html')
        };
        if (this.dpObTypesDefTicketActions.options_data) {
          this.dpObTypesDefTicketActions.options_data.custom_email_tpls.push(tpl);
        }
        if (this.dpObTypesDefTicketCriteria.options_data) {
          this.dpObTypesDefTicketCriteria.options_data.custom_email_tpls.push(tpl);
        }

        return this.$modalInstance.close({
          templateName: name,
          isNewEmail:   this.$scope.is_new_email,
          mode:         'custom'
        });
      }
      return this.$scope.dismiss();
    }
  }
  Admin_Templates_Ctrl_NewEmailTemplateEditor.initClass();


  return Admin_Templates_Ctrl_NewEmailTemplateEditor.EXPORT_CTRL();
});
