define([
  'Reports/Main/Ctrl/Base'
], function(
  ReportsBaseCtrl
) {
  class Reports_Billing_Ctrl_View extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID   = 'Reports_Billing_Ctrl_View';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$stateParams', '$sce', 'Api'];
    }

    init() {

      return this.rendered_result = null;
    }

    /*
  *
  */
    initialLoad() {

      const promise = this.Api.sendGet(`/reports/billing/${this.$stateParams.id}`, {params: this.$stateParams.params}).then( res => {
        const { data } = res;
        return this.rendered_result = this.$sce.trustAsHtml(data.rendered_result);
      });

      return promise;
    }
  }
  Reports_Billing_Ctrl_View.initClass();

  return Reports_Billing_Ctrl_View.EXPORT_CTRL();
});
