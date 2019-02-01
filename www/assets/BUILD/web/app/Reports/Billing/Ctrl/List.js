define([
  'Reports/Main/Ctrl/Base',
  'DeskPRO/Util/Util'
], function(
  ReportsBaseCtrl,
  Util
) {
  class Reports_Billing_Ctrl_List extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID = 'Reports_Billing_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS    = ['Api', '$timeout'];
    }

    init() {
      return this.reports_list = [
        {id: 'list-charges-date', title: 'List of charges <1:date group, default: today>'},
        {id: 'total-charges-per-day-date', title: 'Total charges per day <1:date group, default: this_month>'},
        {id: 'total-amount-charges-per-day-date', title: 'Total amount charges per day <1:date group, default: this_month>'},
        {id: 'total-time-charges-per-day-date', title: 'Total time charges per day <1:date group, default: this_month>'},
        {id: 'total-charges-person-date', title: 'Total charges per person <1:date group, default: this_month>'},
        {id: 'total-amount-charges-person-date', title: 'Total amount charges per person <1:date group, default: this_month>'},
        {id: 'total-time-charges-person-date', title: 'Total time charges per person <1:date group, default: this_month>'},
        {id: 'list-charges-person-date', title: 'List of charges per person <1:date group, default: this_month>'},
        {id: 'total-charges-organization-date', title: 'Total charges per organization <1:date group, default: this_month>'},
        {id: 'total-amount-charges-organization-date', title: 'Total amount charges per organization <1:date group, default: this_month>'},
        {id: 'total-time-charges-organization-date', title: 'Total time charges per organization <1:date group, default: this_month>'},
        {id: 'list-charges-organization-date', title: 'List of charges per organization <1:date group, default: this_month>'},
        {id: 'total-charges-agent-date', title: 'Total charges per agent <1:date group, default: this_month>'},
        {id: 'total-amount-charges-agent-date', title: 'Total amount charges per agent <1:date group, default: this_month>'},
        {id: 'total-time-charges-agent-date', title: 'Total time charges per agent <1:date group, default: this_month>'},
        {id: 'list-charges-agent-date', title: 'List of charges per agent <1:date group, default: this_month>'}
      ];
    }


    /*
     * Loads 2 lists - first with custom reports, second with built-in reports
     */
    initialLoad() {
      const d = this.$q.defer();

      const group_params_promise = this.Api.sendGet('/reports/builder/group-params').then( data => {
        this.group_params = data.data;
        return this.$timeout(() => {
          return d.resolve();
        }
        , 150);
      });

      return d.promise;
    }
  }
  Reports_Billing_Ctrl_List.initClass();

  return Reports_Billing_Ctrl_List.EXPORT_CTRL();
});
