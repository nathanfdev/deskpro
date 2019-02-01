define([
  'Reports/Main/Ctrl/Base',
  'DeskPRO/Util/Util'
], (
  ReportsBaseCtrl,
  Util
) => {
  class Reports_Builder_Ctrl_List extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID = 'Reports_Builder_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS    = ['Api', '$timeout'];
    }

    init() {
      this.customData = this.DataService.get('ReportBuilderCustom');
      return this.builtInData = this.DataService.get('ReportBuilderBuiltIn');
    }


    /*
     * Loads 2 lists - first with custom reports, second with built-in reports
     */
    initialLoad() {
      const d = this.$q.defer();

      const custom_promise = this.customData.loadList().then(list => this.custom_data_list = list);
      const built_in_promise = this.builtInData.loadList().then(list => this.built_in_data_list = list);
      const group_params_promise = this.Api.sendGet('/reports/builder/group-params').then(data => this.group_params = data.data);

      this.$q.all([custom_promise, built_in_promise, group_params_promise]).then(() =>
        // small delay gives chance for select2 boxes to set up, reduces visual jitter
         this.$timeout(() => d.resolve()
        , 350));

      return d.promise;
    }


    /*
     * Show the delete dlg
     */
    startDelete(for_report_id) {
      const report = this.customData.findListModelById(for_report_id);

      if (!report.is_custom) { throw new Error('Report you are going to delete should be custom report'); }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Builder/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => this.deleteReport(report));
    }


    /*
     * Actually do the delete
     */
    deleteReport(for_report) {
      return this.customData.deleteReportById(for_report.id).success(() => {
        if ((this.$state.current.name === 'builder.edit') && (parseInt(this.$state.params.id) === for_report.id)) {
          return this.$state.go('builder');
        }
      }).error((info, code) => this.applyErrorResponseToView(info));
    }
  }
  Reports_Builder_Ctrl_List.initClass();

  return Reports_Builder_Ctrl_List.EXPORT_CTRL();
});
