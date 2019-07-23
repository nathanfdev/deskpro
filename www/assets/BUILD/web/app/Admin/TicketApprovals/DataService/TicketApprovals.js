define(['Admin/Main/DataService/BaseListEdit'], (BaseListEdit) => {
  class Admin_TicketApprovals_DataService_TicketApprovals extends BaseListEdit {
    static initClass() {
      this.$inject = ['$q', 'Api2'];
    }

    /**
     * Load all approval type(s).
     *
     * @param {integer|null|undefined} id
     * @returns {Promise}
     */
    loadApprovalTypes(id = null) {
      const deferred = this.$q.defer();
      const endpoint = (id == null)
        ? '/approval_types'
        : `/approval_types/${id}`;

      this.Api2.sendGet(endpoint).then(
        ({ data }) => deferred.resolve(data.data),
        () => deferred.reject()
      );

      return deferred.promise;
    }

    /**
     * Update/Create approval type.
     *
     * @param {Object} data
     * @param {integer|null|undefined} id
     * @returns {Promise}
     */
    saveApprovalType(data, id = null) {
      const deferred = this.$q.defer();
      const response = (id == null)
        ? this.Api2.sendPostJson('/approval_types', data)
        : this.Api2.sendPutJson(`/approval_types/${id}`, data);

      response.then(
        (response) => deferred.resolve(response),
        (response) => deferred.reject(response)
      );

      return deferred.promise;
    }

    /**
     * Delete approval type.
     *
     * @param {integer} id
     * @returns {Promise}
     */
    deleteApprovalType(id) {
      const deferred = this.$q.defer();

      this.Api2.sendDelete(`/approval_types/${id}`).then(
        () => deferred.resolve(),
        () => deferred.reject()
      );

      return deferred.promise;
    }

    /**
     * Load approval template(s).
     *
     * @param {integer|null|undefined} id
     * @returns {Promise}
     */
    loadApprovalTemplates(id = null) {
      const deferred = this.$q.defer();
      const endpoint = (id == null)
        ? '/approval_templates'
        : `/approval_templates/${id}`;

      this.Api2.sendGet(endpoint).then(
        ({ data }) => deferred.resolve(data.data),
        () => deferred.reject()
      );

      return  deferred.promise;
    }

  }
  Admin_TicketApprovals_DataService_TicketApprovals.initClass();
  return Admin_TicketApprovals_DataService_TicketApprovals;
});
