define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_CustomFields_Base_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomFields_Base_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = [];
    }

    init() {
      this.field_id = parseInt(this.$stateParams.id || 0);
      this.field_type = '0';
      this.field_type_chooser = 'text';
      this.currencies = [];

      this.showFieldType = true;
      this.showEnabled = true;
      this.showAgentOnly = true;

      this.fieldDataService = this.getDataService();
    }

    postLoad() {
    }

    initialLoadExtra() {
    }

    initialLoad() {
      const promises = [];
      promises.push(this.Api2.sendGet('/currencies').then(response => { return this.currencies = response.data.data; }));
      promises.push(this.fieldDataService.loadEditFieldData(this.$stateParams.id || null).then( data => {
        this.field      = data.field;
        this.field_type = data.field_type;
        this.form       = data.form;
        return this.postLoad(data);
      })
      );

      const p = this.initialLoadExtra();
      if (p) {
        promises.push(p);
      }

      return this.$q.all(promises);
    }

    getDataService() {
      throw new Error("Not implemented");
    }

    getBaseRouteName() {
      throw new Error("Not implemented");
    }

    postSave() {
    }

    saveForm() {
      const is_new = !this.field.id;

      this.field.type_name = this.field_type;
      const promise = this.fieldDataService.saveFormModel(this.field, this.form);

      this.startSpinner('saving');

      const successFn = () => {
        this.stopSpinner('saving', true).then(() => {
          return this.Growl.success('Saved');
        });

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go(this.getBaseRouteName() + ".gocreate");
        }
      };

      promise.success( data => {

        if (!this.field_id) {
          this.field.id = data.field_id;
          this.field_id = data.field_id;
        }

        const v = this.postSave();
        if (v && v.then) {
          return v.then(() => successFn());
        } else {
          return successFn();
        }
      });

      return promise.error((info, code) => {
        this.stopSpinner('saving', true);
        this.applyErrorResponseToView(info);
        if (info.error_message && info.error_message) {
          return this.Growl.error(info.error_message);
        }
      });
    }

    startDelete() {
      if (this.field.choices != null ? this.field.choices.length : undefined) {
        const message = this.getRegisteredMessage('remove_choices');
        return this.$modal.open({
          templateUrl: this.getTemplatePath('Index/modal-alert.html'),
          controller:  ['$scope', '$modalInstance', '$state', function($scope, $modalInstance, $state) {
            $scope.message = message;
            return $scope.dismiss = () => $modalInstance.dismiss();
          }
          ]
        });
      }

      const doDelete = () => {
        return this.fieldDataService.deleteFieldById(this.field_id);
      };

      const baseRouteName = this.getBaseRouteName();
      return this.$modal.open({
        templateUrl: this.getTemplatePath('CustomField/delete-modal.html'),
        controller: ['$scope', '$modalInstance', '$state', function($scope, $modalInstance, $state) {
          $scope.confirm = () =>
            $scope.is_loading =
            doDelete().then(function() {
              const baseParts = baseRouteName.split('.');
              $state.go(baseParts[0] + '.' + baseParts[1]);
              return $modalInstance.dismiss();
            })
          ;

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });
    }

    type() {}
  }
  Admin_CustomFields_Base_Ctrl_Edit.initClass();
  return Admin_CustomFields_Base_Ctrl_Edit;
});

