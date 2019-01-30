// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Reports/App/Service/BaseService',
  'moment',
], function(
  BaseService,
  moment,
) {
  let AgentHours;
  return (AgentHours = class AgentHours extends BaseService {

    constructor(Api, $sce, $q) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.Api = Api;
      this.$sce = $sce;
      this.$q = $q;
      this.html = '';
      this.date1 = new Date();
      this.date2 = new Date();
      this.filter = {};
      this.filter.date1 = moment(this.date).format("YYYY-MM-DD");
      this.filter.date2 = moment(this.date).format("YYYY-MM-DD");
    }

    /*
     * This method updates current parameters that are used for sending request to API
     */
    updateFilter() {
      this.filter.date1 = moment(this.date1).format("YYYY-MM-DD");
      this.filter.date2 = moment(this.date2).format("YYYY-MM-DD");
      return this.loadResults();
    }

    /*
     * Loading the results of sending request to API
     */
    loadResults() {
//      @startSpinner('loading_results')

      const promise = this.Api.sendGet(`/reports/agent-hours/${this.filter.date1}/${this.filter.date2}`).then(res => {
        return this.html = this.$sce.trustAsHtml(res.data.html);

//        @stopSpinner('loading_results', true)
      });

      return promise;
    }
  });
});