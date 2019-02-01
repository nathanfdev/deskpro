// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
  'Reports/Builder/ReportEditFormMapper'
], function(
  BaseListEdit,
  ReportEditFormMapper
)  {
  class ReportCustomAbstract extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    getUrlPart() {
      return '';
    }

    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api.sendGet(`/reports/${this.getUrlPart()}/custom`).success( data => {

        const models = data.reports;
        return deferred.resolve(models);
      }
      , (data, status, headers, config) => deferred.reject());

      return deferred.promise;
    }


    /*
     * Remove a model
     *
     * @param {Integer} id
     * @return {promise}
     */
    deleteReportById(id) {
      const promise = this.Api.sendDelete(`/reports/${this.getUrlPart()}/${id}`).success( () => {
        return this.removeListModelById(id);
      });
      return promise;
    }


    /*
     * Get the form mapper
     *
     * @return {ReportEditFormMapper}
     */
    getFormMapper() {

      if (this.formMapper) { return this.formMapper; }
      this.formMapper = new ReportEditFormMapper();
      return this.formMapper;
    }

    /*
     * Get all data needed for the edit page
     *
     * @param {Integer} id
     * @return {promise}
     */
    loadEditReportData(id, params) {

      const deferred = this.$q.defer();
      if (id) {

        this.Api.sendGet(`/reports/${this.getUrlPart()}/${id}`, {params}).then( result => {

          if (result.data.type !== 'custom') { throw new Error('Report you are loading should be custom report'); }

          const data = {};
          data.report = result.data.report;
          data.rendered_result = result.data.rendered_result;
          data.query_parts = result.data.query_parts;
          data.form = this.getFormMapper().getFormFromModel(data);

          return deferred.resolve(data);
        }
        , () => deferred.reject());

      } else {

        const data = {};
        data.report = {
          title: '',
          description: '',
          is_custom: true
        };
        data.rendered_result = '';
        data.query_parts = {};
        data.form = this.getFormMapper().getFormFromModel(data);

        deferred.resolve(data);
      }

      return deferred.promise;
    }


    /*
     * Saves a form model and merges model with list data
     *
     * @param {Object} model report model
     * @param {Object} formModel  The model representing the form
     * @param {Object} queryParts object that is used for storing query builder data
     * @return {promise}
     */
    saveFormModel(model, formModel, queryParts) {

      let promise;
      const mapper = this.getFormMapper();
      const postData = mapper.getPostDataFromForm(formModel);

      if (model.id) {
        promise = this.Api.sendPostJson(`/reports/${this.getUrlPart()}/${model.id}`, {report: postData, parts: queryParts});
      } else {
        promise = this.Api.sendPutJson(`/reports/${this.getUrlPart()}`, {report: postData, parts: queryParts}).success( data => model.id = data.id);
      }

      promise.success( data => {
        if (data.error) {
          return;
        }

        mapper.applyFormToModel(model, formModel);
        return this.mergeDataModel(model);
      });

      return promise;
    }
  }
  ReportCustomAbstract.initClass();
  return ReportCustomAbstract;
});