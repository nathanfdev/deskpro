define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketApprovals_Ctrl_TemplateEdit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketApprovals_Ctrl_TemplateEdit';
      this.CTRL_AS = 'TicketApprovalsTemplateEdit';
      this.DEPS    = ['$scope', '$state', '$stateParams', 'DataService', 'Growl'];
    }

    init() {
      this.dataService = this.DataService.get('TicketApprovals');

      this.templateId = (this.$stateParams.id)
        ? parseInt(this.$stateParams.id.replace(/^template-(\d+)$/, '$1'))
        : null;

      this.setDescription = false;
      this.agents = [];

      this.form = {
        name: '',
        type: '',
        description: '',
        required_approvals: 1,
        required_rejections: 1,
        can_approvers_view_subject: false,
        approver_criteria: {
          can_choose_approvers: false,
          agents: [1,2,3,4,5],
          number_of_approvers: null,
          ticket_user: false,
          organization_manager: false,
          all_agents: false,
          actions_on_approved: []
        }
      };
    }

    initialLoad() {
      let promises = [];

      if (this.templateId) {
        let promise = this.dataService.loadApprovalTemplates(this.templateId)
          .then(data => {
            this.form.name = data.name;
            this.form.type = data.type;
            this.form.description = data.description;
            this.form.description = data.description;
            this.form.required_approvals = data.required_approvals;
            this.form.required_rejections = data.required_rejections;
          });

        promises.push(promise);
      }

      return this.$q.all(promises);
    }

    /**
     * Save approval template form.
     */
    saveForm() {
      // Growl messages
      let msgSuccess = this.getRegisteredMessage('approval_template_save_success');
      let msgFailure = this.getRegisteredMessage('approval_template_save_failure');

      // start spinner
      this.startSpinner('saving');

      // perform api call via data service
      this.dataService.saveApprovalTemplate(this.form, this.templateId)
        .then(response => {

          // get List controller
          const listController = this.$scope['TicketApprovalsList'];

          // add or update approval type
          switch (true) {
            case response.status === 201:
              listController.addTemplate(response.data.data);
              break;
            case response.status === 204:
              listController.updateTemplateById(this.templateId, this.form);
              break;
          }

          // stop spinner and show growl message
          this.stopSpinner('saving', true)
            .then(() => {
              this.Growl.success(msgSuccess);

              // push state back to "list"
              if (this.$state.current.name === 'tickets.approvals.template_edit') {
                return this.$state.go('tickets.approvals');
              }

              if (this.$state.current.name === 'tickets.approvals.template_create') {
                return this.$state.go('tickets.approvals.template_edit', { id: `template-${response.data.data.id}` });
              }
            });

        })
        .catch(() => {

          // stop spinner and show growl message
          this.stopSpinner('saving', true)
            .then(() => this.Growl.error(msgFailure));

        });
    }

    /**
     * Delete selected approval template.
     *
     * @param {integer} id
     */
    deleteType(id) {
      // Growl messages
      let msgSuccess = this.getRegisteredMessage('approval_template_delete_success');
      let msgFailure = this.getRegisteredMessage('approval_template_delete_failure');

      // start spinner
      this.startSpinner('deleting');

      // perform api call via data service
      this.dataService.deleteApprovalTemplate(id)
        .then(() => {

          // get List controller
          const listController = this.$scope['TicketApprovalsList'];

          // remove type from list
          listController.removeTemplateById(id);

          // stop spinner and show growl message
          this.stopSpinner('deleting', true)
            .then(() => this.Growl.success(msgSuccess));

          // push state back to "list"
          if (this.$state.current.name === 'tickets.approvals.template_edit') {
            return this.$state.go('tickets.approvals');
          }

        })
        .catch(() => {

          // stop spinner and show growl message
          this.stopSpinner('deleting', true)
            .then(() => this.Growl.error(msgFailure));

        });
    }
  }
  Admin_TicketApprovals_Ctrl_TemplateEdit.initClass();

  return Admin_TicketApprovals_Ctrl_TemplateEdit.EXPORT_CTRL();
});
