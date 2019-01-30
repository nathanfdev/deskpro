/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
  'Admin/TicketEscalations/EscalationEditFormMapper'
], function(
  BaseListEdit,
  EscalationEditFormMapper
)  {
  let Admin_TicketFilters_DataService_TicketEscalations;
  return Admin_TicketFilters_DataService_TicketEscalations = (function() {
    Admin_TicketFilters_DataService_TicketEscalations = class Admin_TicketFilters_DataService_TicketEscalations extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      _doLoadList() {
        const deferred = this.$q.defer();

        this.Api.sendGet('/ticket_escalations').success( data => {
          const models = data.escalations;
          return deferred.resolve(models);
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }


      /*
        * Save the enabled state of a esc
        *
        * @param {Integer} escId
        * @param {bool} isEnabled
        * @return {promise}
      */
      saveEnabledStateById(escId, isEnabled) {
        if (isEnabled) {
          return this.Api.sendPost(`/ticket_escalations/${escId}/enable`);
        } else {
          return this.Api.sendPost(`/ticket_escalations/${escId}/disable`);
        }
      }

      saveEnabledState(esc) {
        return this.saveEnabledStateById(esc.id, esc.is_enabled);
      }

      /*
        * Save order of escalations
        *
        * @param {Array} orders Array of IDs, in order
        * @return {promise}
      */
      saveRunOrder(orders) {
        for (let idx = 0; idx < orders.length; idx++) {
          const id = orders[idx];
          const model = this.findListModelById(id);
          if (model) {
            model.display_order = idx;
          }
        }

        const promise = this.Api.sendPostJson('/ticket_escalations/run_order', { display_order: orders });
        return promise;
      }


      /*
        * Remove a filter
        *
        * @param {Integer} id Filter id
        * @return {promise}
      */
      deleteEscalationById(id) {
        const promise = this.Api.sendDelete(`/ticket_escalations/${id}`).then(() => {
          return this.removeListModelById(id);
        });
        return promise;
      }


      /*
        * Get the form mapper
        *
        * @return {EscalationEditFormMapper}
      */
      getFormMapper() {
        if (this.formMapper) { return this.formMapper; }
        this.formMapper = new EscalationEditFormMapper();
        return this.formMapper;
      }

      /*
        * Get all data needed for the edit filter page
        *
        * @param {Integer} id Filter id
        * @return {promise}
      */
      loadEditEscalationData(id) {

        const deferred = this.$q.defer();

        if (id) {
          this.Api.sendGet(`/ticket_escalations/${id}`).then( result => {
            const data = {};
            data.escalation = result.data.escalation;
            data.form = this.getFormMapper().getFormFromModel(data.escalation);
            return deferred.resolve(data);
          }
          , () => deferred.reject());
        } else {
          const data = {};
          data.escalation = {
            id: null,
            title: ''
          };
          data.form = this.getFormMapper().getFormFromModel(data.escalation);
          deferred.resolve(data);
        }

        return deferred.promise;
      }

      loadEditSpecialEscalation(type, id) {
        const deferred = this.$q.defer();

        this.Api.sendGet(`/ticket_escalations/${type}/${id}`).then(
          result => {
            const data = {
              escalation: result.data.escalation,
              form: this.getFormMapper().getFormFromModel(result.data.escalation)
            };
            return deferred.resolve(data);
          }
        , () => deferred.reject());

        return deferred.promise;
      }

      /*
        * Saves a form model and applies the form model to the macro model
        * once finished.
        *
        * @param {Object} escModel The esc model
        * @param {Object} formModel  The model representing the form
        * @return {promise}
      */
      saveFormModel(escModel, formModel) {
        let promise;
        const mapper = this.getFormMapper();

        const postData = mapper.getPostDataFromForm(formModel);

        if (escModel.id) {
          promise = this.Api.sendPostJson(`/ticket_escalations/${escModel.id}`, postData);
        } else {
          promise = this.Api.sendPutJson('/ticket_escalations', postData).success( data => escModel.id = data.escalation_id);
        }

        promise.success(() => {
          mapper.applyFormToModel(escModel, formModel);
          return this.mergeDataModel(escModel);
        });

        return promise;
      }
    };
    Admin_TicketFilters_DataService_TicketEscalations.initClass();
    return Admin_TicketFilters_DataService_TicketEscalations;
  })();
});