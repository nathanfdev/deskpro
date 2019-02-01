define([
  'Admin/Main/DataService/BaseListEdit',
  'Reports/Builder/ReportEditFormMapper'
], function(
  BaseListEdit,
  ReportEditFormMapper
)  {
  class ReportBuiltInAbstract extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    /*
     *
     */
    init() {}

    getUrlPart() {
      return '';
    }

    /*
     *
     */
    _doLoadList() {
      const deferred = this.$q.defer();
      this.Api.sendGet(`/reports/${this.getUrlPart()}/builtIn`).success( data => {

        const models = data.reports;
        return deferred.resolve(models);
      }
      , (data, status, headers, config) => deferred.reject());

      return deferred.promise;
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

          if (result.data.type !== 'builtIn') { throw new Error('Report you are loading should be built-in report'); }

          const data = {};
          data.report = result.data.report;
          data.rendered_result = result.data.rendered_result;
          data.query_parts = result.data.query_parts;
          data.form = this.getFormMapper().getFormFromModel(data);

          return deferred.resolve(data);
        }
        , () => deferred.reject());

      } else {

        this.Api.sendGet(`/reports/${this.getUrlPart()}`).then( result => {

          const data = {};
          data.report = {user: {}};
          data.form = this.getFormMapper().getFormFromModel(data);

          return deferred.resolve(data);
        }
        , () => deferred.reject());
      }

      return deferred.promise;
    }
  }
  ReportBuiltInAbstract.initClass();
  return ReportBuiltInAbstract;
});