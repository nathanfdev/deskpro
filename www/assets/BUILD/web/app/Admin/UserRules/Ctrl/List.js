define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_UserRules_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_UserRules_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      return this.userRulesData = this.DataService.get('UserRules');
    }

    /*
     * Loads the list
     */
    initialLoad() {
      return this.userRulesData.loadList().then(list => this.list = list);
    }

    /*
     * Show the delete dlg
     */
    startDelete(for_rule_id) {
      const rule = this.userRulesData.findListModelById(for_rule_id);
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('UserRules/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => this.deleteUserRule(rule));
    }

    /*
     * Actually do the delete
     */
    deleteUserRule(for_rule) {
      return this.userRulesData.deleteUserRuleById(for_rule.id).success(() => {
        if ((this.$state.current.name === 'crm.rules.edit') && (parseInt(this.$state.params.id) === for_rule.id)) {
          return this.$state.go('crm.rules');
        }
      }).error((info, code) => this.applyErrorResponseToView(info));
    }
  }
  Admin_UserRules_Ctrl_List.initClass();

  return Admin_UserRules_Ctrl_List.EXPORT_CTRL();
});
