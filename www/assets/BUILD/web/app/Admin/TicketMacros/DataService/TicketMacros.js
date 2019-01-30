// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
  'Admin/TicketMacros/MacroEditFormMapper',
], function(
  BaseListEdit,
  MacroEditFormMapper
)  {
  let Admin_TicketFilters_DataService_TicketMacros;
  return Admin_TicketFilters_DataService_TicketMacros = (function() {
    Admin_TicketFilters_DataService_TicketMacros = class Admin_TicketFilters_DataService_TicketMacros extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      _doLoadList() {
        const deferred = this.$q.defer();

        this.Api.sendGet('/ticket_macros').success( data => {
          const models = data.macros;
          return deferred.resolve(models);
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }


      /*
        * Remove a filter
        *
        * @param {Integer} id Filter id
        * @return {promise}
      */
      deleteMacroById(id) {
        const promise = this.Api.sendDelete(`/ticket_macros/${id}`).then(() => {
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
      loadEditMacroData(id) {

        const deferred = this.$q.defer();
        this.Api.sendDataGet({
          'macro': (id ? `/ticket_macros/${id}` : null),
          'agents': '/agents'
        }).then( result => {
          const data = {};

          if ((result.data.macro != null ? result.data.macro.macro : undefined) != null) {
            data.macro = result.data.macro.macro;
            data.macro.person_id = data.macro.person ? data.macro.person.id + "" : result.data.agents.agents[0].id + "";
          } else {
            data.macro = {
              id: null,
              title: '',
              is_global: true,
              person_id: result.data.agents.agents[0].id + ""
            };
          }

          data.agents = result.data.agents.agents;
          data.form = this.getFormMapper().getFormFromModel(data.macro);
          return deferred.resolve(data);
        }
        , () => deferred.reject());

        return deferred.promise;
      }


      /*
        * Get the form mapper
        *
        * @return {MacroEditFormMapper}
      */
      getFormMapper() {
        if (this.formMapper) { return this.formMapper; }
        this.formMapper = new MacroEditFormMapper();
        return this.formMapper;
      }


      /*
        * Saves a form model and applies the form model to the macro model
        * once finished.
        *
        * @param {Object} macroModel The macro model
        * @param {Object} formModel  The model representing the form
        * @return {promise}
      */
      saveFormModel(macroModel, formModel) {
        let promise;
        const mapper = this.getFormMapper();

        const postData = mapper.getPostDataFromForm(formModel);

        if (macroModel.id) {
          promise = this.Api.sendPostJson(`/ticket_macros/${macroModel.id}`, postData);
        } else {
          promise = this.Api.sendPutJson('/ticket_macros', postData).success( data => macroModel.id = data.macro_id);
        }

        promise.success(() => {

          macroModel.title = formModel.title;
          macroModel.is_global = formModel.is_global;
          macroModel.person = parseInt(formModel.person_id) ? formModel.agents.filter( x => x.id === parseInt(formModel.person_id))[0] : null;
          macroModel.department = parseInt(formModel.department_id) ? formModel.departments.filter( x => x.id === parseInt(formModel.department_id))[0] : null;

          mapper.applyFormToModel(macroModel, formModel);
          return this.mergeDataModel(macroModel);
        });

        return promise;
      }
    };
    Admin_TicketFilters_DataService_TicketMacros.initClass();
    return Admin_TicketFilters_DataService_TicketMacros;
  })();
});