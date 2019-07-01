define(function() {
  class FieldOptionRemoveHandler {
    constructor(Api, $modal, fieldType) {
      this.Api = Api;
      this.$modal = $modal;
      this.fieldType = fieldType;
    }

    /**
     *
     * @param object ctrl - CategoryBuilder controller
     * @param object row - Selected row
     * @param array removeIds
     * @param function doRemoveCallback - callback from CategoryBuilder that really remove elements from UI
     * @returns {unresolved}
     */
    removeCallback(builderCtrl, ev, removeIds, doRemoveCallback) {
      const self = this;
      const row = $(ev.target).closest('li');
      return self.Api.sendDelete('/ticket_fields/option', { step: 1, type: self.fieldType, ids: removeIds }).then(
        (res) => {
          // if nothing to do, just delete
          if (!res.data.success || (res.data.options == null)) { return doRemoveCallback(); }

          return self.$modal.open({
            templateUrl: `${DP_BASE_ADMIN_URL}/load-view/` + 'CustomFields/Common/delete-option-modal.html',
            controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
              for (const k in res.data.options) {
                const v = res.data.options[k];
                if (!builderCtrl.cat_rows[k]) { delete res.data.options[k]; }
              }

              $scope.dismiss = () => $modalInstance.dismiss();
              $scope.mode = 0;
              $scope.busy = false;
              $scope.options = res.data.options;
              $scope.update_to = res.data.default;
              $scope.type = self.fieldType;
              $scope.name = row.children('div').children('input').val();

              return $scope.confirm = function () {
                $scope.busy = true;
                let data = {};
                if ($scope.mode) {
                  data = {
                    step:      2,
                    type:      self.fieldType,
                    ids:       removeIds,
                    update_to: $scope.update_to
                  };
                } else {
                  data = {
                    step:      3,
                    type:      self.fieldType,
                    ids:       removeIds
                  };
                }
                self.Api.sendDelete('/ticket_fields/option', data).then(
                  (res) => {
                    if (res.data.success) {
                      doRemoveCallback();
                      return $scope.dismiss();
                    }
                  },
                  () => {
                    return $scope.dismiss();
                  }
                );
              };
            }
            ] });
        },
        () => {}
      );
    }
  }

  return FieldOptionRemoveHandler;
});
