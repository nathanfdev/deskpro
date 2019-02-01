define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Languages_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Languages_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
    }

    init() {
      this.id = this.$stateParams.id;

      const format = function(flag) {
        if (!flag || !flag.text) { return ''; }
        return `<img src='${DP_ASSET_URL}/images/flags/${flag.id.toLowerCase()}' style='margin-right: 2px;' />${flag.text}`;
      };

      this.$scope.select2Flag = {
        formatResult: format,
        formatSelection: format,
        escapeMarkup(m) { return m; }
      };

      return this.$scope.isDefaultLang = () => {
        return this.lang && this.lang.id && (this.lang.id === parseInt(this.$scope.$parent.ListCtrl.default_lang_id));
      };
    }

    initialLoad() {
      const promise = this.Api.sendGet(`/langs/${this.id}`).then( result => {
        if (!result.data.language) {
          this.$state.go('setup.languages.install', {id: `install-${this.id}`});
          return;
        }

        this.pack = result.data.pack;
        this.lang = result.data.language;
        return this.form = {
          title: this.lang.title,
          flag_image: this.lang.flag_image,
          locale: this.lang.locale
        };
      });
      return promise;
    }

    startUninstall() {
      if (this.lang.id === parseInt(this.$scope.$parent.ListCtrl.default_lang_id)) {
        this.showAlert('@no_delete_default');
        return;
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Languages/uninstall-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => {
        this.startSpinner('saving');
        return this.$scope.$parent.ListCtrl.uninstallLang(this.id).then(() => {
          return this.$state.go('setup.languages');
        });
      });
    }

    doSave() {
      if (this.$scope.form_props.$invalid) { return; }

      this.form.locale = this.form.locale.replace(/-/, '_');

      this.startSpinner('saving');
      return this.$scope.$parent.ListCtrl.saveLanguage(this.id, {
        title: this.form.title,
        flag_image: this.form.flag_image,
        locale: this.form.locale
      }).then( () => {
        return this.stopSpinner('saving', true);
      });
    }
  }
  Admin_Languages_Ctrl_Edit.initClass();

  return Admin_Languages_Ctrl_Edit.EXPORT_CTRL();
});