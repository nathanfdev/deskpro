define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketApprovals_Ctrl_TypeEdit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketApprovals_Ctrl_TypeEdit';
      this.CTRL_AS = 'TicketApprovalsTypeEdit';
      this.DEPS    = ['$scope', '$state', '$stateParams', 'DataService', 'Growl'];
    }

    init() {
      this.dataService = this.DataService.get('TicketApprovals');

      this.typeId = (this.$stateParams.id)
        ? this.$stateParams.id.replace(/^type-(\d+)$/, '$1')
        : null;

      this.form = {
        name: '',
        description: ''
      };
    }

    initialLoad() {
      let promises = [];

      if (this.typeId) {
        let promise = this.dataService.loadApprovalTypes(this.typeId)
          .then(data => {
            this.form.name = data.name;
            this.form.description = data.description;
          });

        promises.push(promise);
      }

      return this.$q.all(promises);
    }

    /**
     * Save approval type form.
     */
    saveForm() {
      // Growl messages
      let msgSuccess = this.getRegisteredMessage('approval_type_save_success');
      let msgFailure = this.getRegisteredMessage('approval_type_save_failure');

      // start spinner
      this.startSpinner('saving');

      // perform api call via data service
      this.dataService.saveApprovalType(this.form, this.typeId)
        .then(response => {

          // get List controller
          const listController = this.$scope['TicketApprovalsList'];

          // add or update approval type
          switch (true) {
            case response.status === 201:
              listController.addType(response.data.data);
              break;
            case response.status === 204:
              listController.updateTypeById(this.typeId, this.form);
              break;
          }

          // stop spinner and show growl message
          this.stopSpinner('saving', true)
            .then(() => this.Growl.success(msgSuccess));

          // push state back to "list"
          if (this.$state.current.name === 'tickets.approvals.type_edit') {
            return this.$state.go('tickets.approvals');
          }

        })
        .catch(() => {

          // stop spinner and show growl message
          this.stopSpinner('saving', true)
            .then(() => this.Growl.error(msgFailure));

        });
    }

    /**
     * Delete selected approval type.
     *
     * @param {integer} id
     */
    deleteType(id) {
      // Growl messages
      let msgSuccess = this.getRegisteredMessage('approval_type_delete_success');
      let msgFailure = this.getRegisteredMessage('approval_type_delete_failure');

      // start spinner
      this.startSpinner('deleting');

      // perform api call via data service
      this.dataService.deleteApprovalType(id)
        .then(() => {

          // get List controller
          const listController = this.$scope['TicketApprovalsList'];

          // remove type from list
          listController.removeTypeById(id);

          // stop spinner and show growl message
          this.stopSpinner('deleting', true)
            .then(() => this.Growl.success(msgSuccess));

          // push state back to "list"
          if (this.$state.current.name === 'tickets.approvals.type_edit') {
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
  Admin_TicketApprovals_Ctrl_TypeEdit.initClass();

  return Admin_TicketApprovals_Ctrl_TypeEdit.EXPORT_CTRL();
});
