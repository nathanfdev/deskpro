define([
  'Admin/Main/Ctrl/Base'
], function(Admin_Ctrl_Base) {
  class Admin_TicketApprovals_Ctrl_TemplateEdit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketApprovals_Ctrl_TemplateEdit';
      this.CTRL_AS = 'TicketApprovalsTemplateEdit';
      this.DEPS    = ['$scope', '$state', '$stateParams', 'DataService', 'Growl'];
    }

    init() {
      this.dataService = this.DataService.get('TicketApprovals');

      this.$scope.templateId = (this.$stateParams.id)
        ? parseInt(this.$stateParams.id.replace(/^template-(\d+)$/, '$1'))
        : null;

      this.$scope.setDescription = false;

      this.$scope.people = [];

      this.$scope.form = {
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

      // define search funtion for ui-select2
      this.$scope.searchTerm = function (query) {

        this.dataService.searchPeople(query.term)
          .then(({ people }) => {

            let selected = this.$scope.people.map(item => item.id);
            let result = { results: [] };
            result.results = Object.values(people)
              .filter(person => selected.indexOf(person.id) === -1)
              .map(person => ({ ...person, text: `${person.first_name} ${person.last_name}` }));

            query.callback(result);
          });

      }.bind(this);

      this.$scope.selectedUser = null;
      this.$scope.$watch('selectedUser', (person) => {
        if (person === null || person.length < 1) {
          return;
        }

        // tick user
        person.value = true;

        this.$scope.people.push(person);
        this.$scope.selectedUser = null;
      });

    }

    initialLoad() {
      let promises = [];

      if (this.$scope.templateId) {
        let promise = this.dataService.loadApprovalTemplates(this.$scope.templateId)
          .then(data => {
            // set description flag
            if (data.description.length > 0) {
              this.$scope.setDescription = true;
            }

            // merge agents and users into people array
            [
              ...data.approver_criteria.users,
              ...data.approver_criteria.agents,
            ].forEach(id => {
              this.dataService.getPerson(id)
                .then(result => {
                  result.person.value = true;
                  this.$scope.people.push(result.person);
                });
            });

            // assign data to form
            this.$scope.form = data;
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

      this.$scope.form.approver_criteria.agents = [];
      this.$scope.form.approver_criteria.users  = [];

      this.$scope.people.forEach(person => {
        if (person.value === true) {
          if (person.is_agent === true) {
            this.$scope.form.approver_criteria.agents.push(person.id);
          } else {
            this.$scope.form.approver_criteria.users.push(person.id);
          }
        }
      });

      // start spinner
      this.startSpinner('saving');

      // perform api call via data service
      this.dataService.saveApprovalTemplate(this.$scope.form, this.templateId)
        .then(response => {

          // get List controller
          const listController = this.$scope['TicketApprovalsList'];

          // add or update approval type
          switch (true) {
            case response.status === 201:
              listController.addTemplate(response.data.data);
              break;
            case response.status === 204:
              listController.updateTemplateById(this.templateId, this.$scope.form);
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
