define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketApprovals_Ctrl_TemplateEdit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketApprovals_Ctrl_TemplateEdit';
      this.CTRL_AS = 'TicketApprovalsTemplateEdit';
      this.DEPS    = ['$scope', '$state', '$stateParams', 'DataService', 'Growl', 'Api'];
    }

    init() {
      this.dataService = this.DataService.get('TicketApprovals');

      this.templateId = (this.$stateParams.id)
        ? parseInt(this.$stateParams.id.replace(/^template-(\d+)$/, '$1'))
        : null;

      this.setDescription = false;

      this.agents = [];
      this.users = [];
      this.teams = [];

      this.form = {
        type: null,
        name: null,
        description: null,
        required_approvals: 0,
        required_rejections: 0,
        can_approvers_view_subject: false,
        approver_criteria: {
          can_choose_approvers: false,
          agents: [],
          all_agents: false,
          users: [],
          all_users: false,
          organization_managers: false,
          teams: [],
          departments: [],
          required_number_of_approvers: null,
        },
        // triggers, @fixme Ashley please change this if needed
        actions_on_create: [],
        actions_on_partial_approval_response: [],
        actions_on_partial_rejection_response: [],
        actions_on_cancel: [],
        actions_on_approved: [],
        actions_on_rejected: []
      };
    }

    initialLoad() {
      let promises = [];

      if (this.templateId) {
        let promise = this.dataService.loadApprovalTemplates(this.templateId)
          .then(data => {
            this.form = data;
            if (data.description.length > 0) {
              this.setDescription = true;
            }
          });

        promises.push(promise);
      }

      this.Api.sendDataGet({
        agents: '/agents',
        //teams:  '/agent_teams',
      })
        .then(res => {
          this.agents = res.data.agents.agents;
          this.agents.map((x) => {
            if (Array.from(this.form.approver_criteria.agents).includes(x.id)) { return x.value = true; }
          });
        });

      return this.$q.all(promises);
    }

    /**
     * Save approval template form.
     */
    saveForm() {
      // Growl messages
      let msgSuccess = this.getRegisteredMessage('approval_template_save_success');
      let msgFailure = this.getRegisteredMessage('approval_template_save_failure');

      let agents = [];
      this.agents.forEach(item=> {
        if (item.value === true) {
          agents.push(item.id);
        }
      });
      this.form.approver_criteria.agents = agents;

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
    deleteTemplate(id) {
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
