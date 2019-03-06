define(['Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
) => {
  class Admin_TicketStatuses_DataService_TicketStatuses extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api2', '$q'];
    }

    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api2.sendGet('/ticket_statuses', []).then((response) => {
        const models = response.data.data;
        return deferred.resolve(models);
      }
      , (response, status, headers, config) => deferred.reject());

      return deferred.promise;
    }

    /*
      * @param {Integer} id
      * @return {promise}
    */
    deleteStatusById(id, set_to) {
      const promise = this.Api2.sendDelete(`/ticket_statuses/${id}`, {set_to}).then(() => this.removeListModelById(id));
      return promise;
    }

    /*
      * Get all data needed for the edit
      *
      * @param {Integer} id Filter id
      * @return {promise}
    */
    loadEditStatusData(id) {
      const deferred = this.$q.defer();

      this.Api2.sendGet(`/ticket_statuses/${id}`).then(response =>
        deferred.resolve({
          status: response.data.data
        })

      , () => deferred.reject());

      return deferred.promise;
    }

    /**
     * Return tree of statuses (excluding hidden statuses)
     * Tree composed from the current list (this.listModels)
     * this functions doesn't check if list was loaded
     *
     * @param {Array} excludeIds
     * @returns {Array}
     */
    getTree(excludeIds) {
      const tree = [];

      if (typeof excludeIds == 'undefined') {
        excludeIds = [];
      }
      
      [
        'awaiting_agent',
        'awaiting_user',
        'pending',
        'resolved',
        'archived'
      ]
      .filter(topStatus => excludeIds.indexOf(topStatus) === -1)
      .forEach(topStatus => {
        tree.push({
          id: topStatus,
          status_type: topStatus,
          status_code: topStatus,
          title: topStatus,
          children: this.listModels.filter(status => (excludeIds.indexOf(status.id) === -1) && status.status_type == topStatus)
        });

      });

      return tree;
    }
  }
  Admin_TicketStatuses_DataService_TicketStatuses.initClass();
  return Admin_TicketStatuses_DataService_TicketStatuses;
});
