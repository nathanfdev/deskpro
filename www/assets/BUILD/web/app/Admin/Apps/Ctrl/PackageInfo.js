define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_Apps_Ctrl_PackageInfo extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Apps_Ctrl_PackageInfo';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$http'];
    }

    init() {
      this.packageName = this.$stateParams.name;
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        pack: `/apps/packages/${this.packageName}`,
      }).then((result) => {
        this.pack = result.data.pack.package;
        if (this.pack.is_usersource_app) {
          return (() => {
            const result1 = [];
            for (const the_app of Array.from(this.pack.apps)) {
              if (the_app.user_usersource) {
                this.pack.user_usersource_app = the_app;
              }
              if (the_app.agent_usersource) {
                result1.push(this.pack.agent_usersource_app = the_app);
              } else {
                result1.push(undefined);
              }
            }
            return result1;
          })();
        }
      });

      return promise;
    }

    startDelete() {
      const doDelete = () => this.Api.sendDelete(`/apps/packages/${this.pack.name}`).success(() => this.$state.go('apps.go_apps'));

      return this.$modal.open({
        templateUrl: this.getTemplatePath('Apps/package-delete-modal.html'),
        controller:  ['pack', '$scope', '$modalInstance', function (pack, $scope, $modalInstance) {
          $scope.pack = pack;
          $scope.dismiss = () => $modalInstance.close();

          return $scope.confirm = function () {
            $scope.is_loading = true;
            return doDelete().then(() => $modalInstance.close());
          };
        }
        ],
        resolve: {
          pack: () => this.pack
        }
      });
    }
  }
  Admin_Apps_Ctrl_PackageInfo.initClass();

  return Admin_Apps_Ctrl_PackageInfo.EXPORT_CTRL();
});
