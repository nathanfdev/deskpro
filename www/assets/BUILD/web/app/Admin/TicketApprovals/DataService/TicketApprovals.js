define(['Admin/Main/DataService/BaseListEdit'], (BaseListEdit) => {
  class Admin_TicketApprovals_DataService_TicketApprovals extends BaseListEdit {
    static initClass() {
      this.$inject = ['$q', 'Api', 'Api2'];
    }

    /**
     * Search people
     *
     * @param {String} term
     * @param {Boolean} excludeAgents
     */
    searchPeople(term, excludeAgents = false) {
      const deferred = this.$q.defer();
      const params = {
        search: term
      };

      if (excludeAgents) {
        params.is_agent = 0;
      }

      this.Api2.sendGet('/people', params).then(
          ({ data }) => deferred.resolve(data),
          () => deferred.reject()
        );

      return deferred.promise;
    }

    getPerson(id) {
      const deferred = this.$q.defer();

      this.Api.sendGet(`/people/${id}`).then(
          ({ data }) => deferred.resolve(data),
          () => deferred.reject()
        );

      return deferred.promise;
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
      const promise = (id == null)
        ? this.Api2.sendPostJson('/approval_types', data)
        : this.Api2.sendPutJson(`/approval_types/${id}`, data);

      promise.then(
        response => deferred.resolve(response),
        response => deferred.reject(response)
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
        response => deferred.resolve(response),
        response => deferred.reject(response)
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

      return deferred.promise;
    }

    /**
     * Update/Create approval template.
     *
     * @param {Object} data
     * @param {integer|null|undefined} id
     * @returns {Promise}
     */
    saveApprovalTemplate(data, id = null) {
      const deferred = this.$q.defer();
      const promise = (id == null)
        ? this.Api2.sendPostJson('/approval_templates', data)
        : this.Api2.sendPutJson(`/approval_templates/${id}`, data);

      promise.then(
        response => deferred.resolve(response),
        response => deferred.reject(response)
      );

      return deferred.promise;
    }

    /**
     * Delete approval template.
     *
     * @param {integer} id
     * @returns {Promise}
     */
    deleteApprovalTemplate(id) {
      const deferred = this.$q.defer();

      this.Api2.sendDelete(`/approval_templates/${id}`).then(
        response => deferred.resolve(response),
        response => deferred.reject(response)
      );

      return deferred.promise;
    }

  }
  Admin_TicketApprovals_DataService_TicketApprovals.initClass();
  return Admin_TicketApprovals_DataService_TicketApprovals;
});
