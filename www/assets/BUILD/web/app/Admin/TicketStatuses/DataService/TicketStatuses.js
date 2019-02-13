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
    deleteStatusById(id) {
      const promise = this.Api2.sendDelete(`/ticket_statuses/${id}`).then(() => this.removeListModelById(id));
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
  }
  Admin_TicketStatuses_DataService_TicketStatuses.initClass();
  return Admin_TicketStatuses_DataService_TicketStatuses;
});
