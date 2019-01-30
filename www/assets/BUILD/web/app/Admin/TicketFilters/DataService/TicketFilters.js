/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit'
], function(
  BaseListEdit,
)  {
  let Admin_TicketFilters_DataService_TicketFilters;
  return Admin_TicketFilters_DataService_TicketFilters = (function() {
    Admin_TicketFilters_DataService_TicketFilters = class Admin_TicketFilters_DataService_TicketFilters extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      _doLoadList() {
        const deferred = this.$q.defer();

        this.Api.sendGet('/ticket_filters').success( data => {
          const models = data.filters;
          return deferred.resolve(models);
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }


      /*
        * Save order of filters
        *
        * @param {Array} orders Array of IDs, in order
        * @return {promise}
      */
      saveDisplayOrder(orders) {
        for (let idx = 0; idx < orders.length; idx++) {
          const id = orders[idx];
          const model = this.findListModelById(id);
          if (model) {
            model.display_order = idx;
          }
        }

        const promise = this.Api.sendPostJson('/ticket_filters/display_order', { display_order: orders }).then(() => this.loadList(true));
        return promise;
      }


      /*
        * Remove a filter
        *
        * @param {Integer} id Filter id
        * @return {promise}
      */
      deleteFilterId(id) {
        const promise = this.Api.sendDelete(`/ticket_filters/${id}`).then(() => {
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
      loadEditFilterData(id) {

        const deferred = this.$q.defer();

        const types = {};
        if (id) {
          types.filter = `/ticket_filters/${id}`;
        }

        types.agents = '/agents';
        types.teams = '/agent_teams';

        this.Api.sendDataGet(types).then( function(res) {
          const data = {};
          if (res.data.filter) {
            data.filter = res.data.filter.filter;
          }

          data.agents = res.data.agents.agents;
          data.teams  = res.data.teams.agent_teams;
          return deferred.resolve(data);
        }
        , () => deferred.reject());

        return deferred.promise;
      }
    };
    Admin_TicketFilters_DataService_TicketFilters.initClass();
    return Admin_TicketFilters_DataService_TicketFilters;
  })();
});
