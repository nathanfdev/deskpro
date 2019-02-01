define([
  'Admin/Main/DataService/BaseListEdit'
], function(
  BaseListEdit
)  {
  class Admin_TicketFilters_DataService_TicketSlas extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api.sendGet('/ticket_slas').success( data => {
        const models = data.slas;
        return deferred.resolve(models);
      }
      , (data, status, headers, config) => deferred.reject());

      return deferred.promise;
    }


    /*
      * Remove an slas
      *
      * @param {Integer} id SLA id
      * @return {promise}
    */
    deleteSlaById(id) {
      const promise = this.Api.sendDelete(`/ticket_slas/${id}`).then(() => {
        return this.removeListModelById(id);
      });
      return promise;
    }


    /*
      * Get all data needed for the edit filter page
      *
      * @param {Integer} id Filter id
      * @return {promise}
    */
    loadEditSlaData(id) {

      const deferred = this.$q.defer();

      this.Api.sendGet(`/ticket_slas/${id}`).then( result =>
        deferred.resolve({
          sla: result.data.sla
        })

      , () => deferred.reject());

      return deferred.promise;
    }
  }
  Admin_TicketFilters_DataService_TicketSlas.initClass();
  return Admin_TicketFilters_DataService_TicketSlas;
});
