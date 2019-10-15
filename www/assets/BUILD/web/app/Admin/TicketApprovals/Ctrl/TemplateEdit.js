define([
  'Admin/Main/Ctrl/Base'
], function(Admin_Ctrl_Base) {
  class Admin_TicketApprovals_Ctrl_TemplateEdit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketApprovals_Ctrl_TemplateEdit';
      this.CTRL_AS = 'TicketApprovalsTemplateEdit';
      this.DEPS    = ['$scope', '$state', '$stateParams', 'DataService', 'Growl', 'dpObTypesDefTicketActions'];
    }

    init() {
      this.dataService = this.DataService.get('TicketApprovals');

      this.$scope.templateId = (this.$stateParams.id)
        ? parseInt(this.$stateParams.id.replace(/^template-(\d+)$/, '$1'))
        : null;

      this.$scope.setDescription = false;

      this.$scope.people = [];
      this.$scope.can_choose_approvers = 'n';

      this.$scope.form = {
        type: null,
        name: null,
        description: null,
        required_approvals: 0,
        required_rejections: 0,
        can_approvers_view_subject: false,
        can_choose_approvers: false,
        selected_approvers: {
          has_ticket_user: false,
          has_organization_managers: false,
          has_all_agents: false,
          people: []
        },
        approver_selection_criteria: {
          can_select_ticket_user: false,
          can_select_organization_managers: false,
          can_select_from_all_agents: false,
          select_from_people: [],
          min_number_of_approvers: 1,
        },
        actions_on_create: [],
        actions_on_partial_approval_response: [],
        actions_on_partial_rejection_response: [],
        actions_on_cancel: [],
        actions_on_approved: [],
        actions_on_rejected: []
      };

      // define search funtion for ui-select2
      this.$scope.searchTerm = function (query) {
        const excludeAgents =
          (this.$scope.form.selected_approvers && this.$scope.form.selected_approvers.has_all_agents) ||
          (this.$scope.form.approver_selection_criteria && this.$scope.form.approver_selection_criteria.can_select_from_all_agents);

        this.dataService.searchPeople(query.term, excludeAgents)
          .then(({ data }) => {
            let selected = this.$scope.people.map(item => item.id);
            let result = { results: [] };
            result.results = data
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

      this.$scope.$watch('form', () => {
        if (this.$scope.form_props.custom_error) {
          this.$scope.form_props.$invalid = false;
          this.$scope.form_props.custom_error = '';
        }
      }, true);
      this.$scope.$watch('can_choose_approvers', () => {
        if (this.$scope.form.selected_approvers) {
          this.$scope.form.selected_approvers.has_ticket_user = '';
          this.$scope.form.selected_approvers.has_organization_managers = '';
          this.$scope.form.selected_approvers.has_all_agents = '';
        }
      });

      this.actionsTypeDef = this.dpObTypesDefTicketActions;
      // this.actionsTypeDef.setVar('object_type', 'trigger');
      this.$scope.actionOptionTypes = [];
    }

    updateCriteriaOptionTypes() {
      let set = this.actionsTypeDef.getOptionsForTypes([], {});
      this.$scope.actionOptionTypes.length = 0;
      return (() => {
        const result = [];
        for (let opt of Array.from(set)) {
          result.push(this.$scope.actionOptionTypes.push(opt));
        }
        return result;
      })();
    }

    initialLoad() {
      let promises = [
        this.actionsTypeDef.loadDataOptions()
      ];

      if (this.$scope.templateId) {
        let promise = this.dataService.loadApprovalTemplates(this.$scope.templateId)
          .then(data => {
            // set description flag
            if (data.description.length > 0) {
              this.$scope.setDescription = true;
            }

            let people = [];
            this.$scope.form.can_choose_approvers = data.can_choose_approvers;
            if (data.can_choose_approvers) {
              this.$scope.can_choose_approvers = 'y';
              people = data.approver_selection_criteria.select_from_people;
            } else {
              this.$scope.can_choose_approvers = 'n';
              people = data.selected_approvers.people;
            }

            // merge agents and users into people array
            people.forEach(id => {
              this.dataService.getPerson(id)
                .then(result => {
                  result.person.value = true;
                  this.$scope.people.push(result.person);
                });
            });

            // Correct actions structure
            data.actions_on_create = data.actions_on_create.actions || [];
            data.actions_on_partial_approval_response = data.actions_on_partial_approval_response.actions || [];
            data.actions_on_partial_rejection_response = data.actions_on_partial_rejection_response.actions || [];
            data.actions_on_cancel = data.actions_on_cancel.actions || [];
            data.actions_on_approved = data.actions_on_approved.actions || [];
            data.actions_on_rejected = data.actions_on_rejected.actions || [];

            // Remove redundant fields
            delete data.id;
            delete data.created_at;

            // assign data to form
            this.$scope.form = data;
          });

        promises.push(promise);
      }

      return this.$q.all(promises).then(() => this.$timeout(() => {
        this.updateCriteriaOptionTypes();
      }));
    }

    rebaseActions(form, actionSet) {
      let actions = [];
      for (const _x of Object.keys(form[actionSet] || {})) {
        actions.push(form[actionSet][_x]);
      }
      form[actionSet] = actions;

      return form;
    }

    /**
     * Save approval template form.
     */
    saveForm() {
      // Growl messages
      let msgSuccess = this.getRegisteredMessage('approval_template_save_success');
      let msgFailure = this.getRegisteredMessage('approval_template_save_failure');

      this.$scope.form.can_choose_approvers = this.$scope.can_choose_approvers === 'y';

      if (this.$scope.form.can_choose_approvers) {
        delete this.$scope.form.selected_approvers;

        this.$scope.form.approver_selection_criteria.select_from_people = this.$scope.people.map(person => person.id);
      } else {
        delete this.$scope.form.approver_selection_criteria;

        this.$scope.form.selected_approvers.people = this.$scope.people.map(person => person.id);
      }

      // start spinner
      this.startSpinner('saving');

      const actionSets = [
        'actions_on_create',
        'actions_on_partial_approval_response',
        'actions_on_partial_rejection_response',
        'actions_on_approved',
        'actions_on_rejected',
        'actions_on_cancel'
      ];

      for (const set of actionSets) {
        this.$scope.form = this.rebaseActions(this.$scope.form, set);
      }

      // perform api call via data service
      this.dataService.saveApprovalTemplate(this.$scope.form, this.$scope.templateId)
        .then((response) => {
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
                this.$state.go('tickets.approvals');
              }

              if (this.$state.current.name === 'tickets.approvals.template_create') {
                this.$state.go('tickets.approvals.template_edit', { id: `template-${response.data.data.id}` });
              }
            });
        })
        .catch((response) => {
          // stop spinner and show growl message
          this.stopSpinner('saving', true)
            .then(() => {
              if (response && response.data && response.data.errors) {
                const errors = response.data.errors;

                if (errors.errors && errors.errors[0] && errors.errors[0].message) {
                  msgFailure = response.data.errors.errors[0].message;
                }
                if (errors.fields && errors.fields.approver_selection_criteria && errors.fields.approver_selection_criteria.fields) {
                  const selectionErrors = errors.fields.approver_selection_criteria.fields;
                  if (selectionErrors.min_number_of_approvers) {
                    msgFailure = 'You must provide a minimum number of approvers value between 1 and 100.';
                  }
                }
              }

              this.$scope.form_props.$invalid = true;
              this.$scope.form_props.custom_error = msgFailure;
            });

        });
    }

    /**
     * Delete selected approval template.
     *
     * @param {integer} id
     */
    deleteTemplate(id) {
      // Growl messages
      const msgSuccess = this.getRegisteredMessage('approval_template_delete_success');
      const msgFailure = this.getRegisteredMessage('approval_template_delete_failure');

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
            this.$state.go('tickets.approvals');
          }
        })
        .catch((response) => {
          // stop spinner and show growl message
          this.stopSpinner('deleting', true)
            .then(() => this.Growl.error(response && response.data && response.data.message ? response.data.message : msgFailure));
        });
    }
  }
  Admin_TicketApprovals_Ctrl_TemplateEdit.initClass();

  return Admin_TicketApprovals_Ctrl_TemplateEdit.EXPORT_CTRL();
});
