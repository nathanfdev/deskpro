define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_Apps_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Apps_Ctrl_List';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];

      this.apps = [];
      this.apps_v2 = [];
      this.apps_v2_packages = [];
    }

    init() {
      this.$scope.hide_installed = true;

      const ctrl = this.$scope.ListCtrl;

      this.$scope.packagesFilter = function (hide_installed) {
        const is_installed = !hide_installed;
        return itm => !itm.is_usersource_app && (!itm.is_installed || (itm.is_installed === is_installed));
      };

      this.$scope.packagesV2Filter = hide_installed =>
        function (pkg) {
          if (!hide_installed) {
            return true;
          }

          for (const instance of Array.from(ctrl.apps_v2)) {
            if (pkg.id === instance.application_id) {
              return false;
            }
          }

          return true;
        }
      ;
    }

    initialLoad() {
      this.apps = [];
      this.apps_v2 = [];
      this.apps_v2_packages = [];

      const appsPromise = this.Api.sendDataGet({ apps: '/apps' });
      appsPromise.then((result) => {
        this.packages = result.data.apps.packages;

        // Dont list custom apps as "packages"
        this.packages = this.packages.filter(x => !x.is_custom);

        result.data.apps.apps.filter(x => !x.package.is_custom).forEach(app => this.apps.push(app));
        return this.custom_apps = result.data.apps.apps.filter(x => x.package.is_custom);
      });

      const apps2Promise = this.Api2.sendGet('/apps?include=app&inline_sideloads=true');
      apps2Promise.then(result => result.data.data.forEach((instance) => {
        instance.app.icon_32 = `${instance.app.icon_url}?s=32`;
        return this.apps_v2.push(instance);
      }));

      const apps2PackagesPromise = this.Api2.sendGet('/apps/packages');
      apps2PackagesPromise.then(result => result.data.data.forEach((app) => {
        app.icon_48 = `${app.icon_url}?s=48`;
        return this.apps_v2_packages.push(app);
      }));

      return this.$q.all([appsPromise, apps2Promise, apps2PackagesPromise]).then((data) => {
        if (this.$stateParams.instanceId) {
          this.$state.go('apps.apps.edit-v2', { instanceId: this.$stateParams.instanceId, configuration: '' });
        }

        return data;
      });
    }

    listInstalledAppsV2() {
      if (this.apps_v2 instanceof Array) {
        return this.apps_v2;
      }
      return [];
    }

    addAppInstance(instanceInfo, isNew) {
      if (isNew == null) { isNew = false; }
      if (isNew) {
        instanceInfo.app.icon_32 = `${instanceInfo.app.icon_url}?s=32`;
        return this.apps_v2.push(instanceInfo);
      }
      this.apps.push(instanceInfo);

      return (() => {
        const result = [];
        for (const p of Array.from(this.packages)) {
          if (p.name === instanceInfo.package.name) {
            if (!p.apps) { p.apps = []; }
            p.apps.push(instanceInfo);
            p.is_installed = true;
            break;
          } else {
            result.push(undefined);
          }
        }
        return result;
      })();
    }

    removeAppInstance(instanceId, isNew) {
      if (isNew == null) { isNew = false; }
      if (isNew) {
        return this.apps_v2 = this.apps_v2.filter(x => x.id !== instanceId);
      }
      const app = this.apps.find(x => x.id === instanceId);
      this.apps = this.apps.filter(x => x.id !== instanceId);
      this.custom_apps = this.custom_apps.filter(x => x.id !== instanceId);

        // we just removed an app so we might need to switch the
        // is_installed flag on the package so it appears back in the list
      if (app) {
        let hasOtherApp = false;
        this.apps.map((x) => { if (x.package.name === app.package.name) { return hasOtherApp = true; } });
        if (!hasOtherApp) {
          const p = this.packages.find(x => x.name === app.package.name);
          if (p) {
            return p.is_installed = false;
          }
        }
      }
    }


    updateAppTitle(id, title) {
      this.apps.filter(x => x.id === id).map(x => x.title = title);
      return this.custom_apps.filter(x => x.id === id).map(x => x.title = title);
    }

    ensureCustomAppInList(customApp) {
      if (!this.custom_apps) { return; }
      const exist = this.custom_apps.filter(x => x.id === customApp.id);
      if (!exist.length) {
        return this.custom_apps.push(customApp);
      }
    }

    getInstallerRouteParams(pkg) {
      return { appName: encodeURIComponent(pkg.name) };
    }


    showNewApp() {
      const saveNewApp = (options) => {
        const postData = {
          options
        };
        return this.Api.sendPutJson('/apps/custom', postData).success(info => this.$state.go('apps.apps.custom_instance', { custom_id: `custom_${info.id}` }));
      };

      return this.$modal.open({
        templateUrl: this.getTemplatePath('Apps/new-app-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.doCreate = function () {
            $scope.is_loading = true;
            return saveNewApp($scope.opt).then(() => {
              $modalInstance.dismiss();
              return $scope.is_loading = false;
            }
            , () => $scope.is_loading = false);
          };

          return $scope.opt = {
            ticket: {}
          };
        }
        ]
      });
    }

    showUploadApp() {
      const me = this;
      return this.$modal.open({
        templateUrl: this.getTemplatePath('Apps/upload-package-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          const uploadDone = data =>
            me.initialLoad().then(() => {
              $modalInstance.dismiss();

              return me.$timeout(() => {
                if (data.version === 2) {
                  const instance = data.data.manifest.isSingle ? me.apps_v2.filter(x => x.app.name === data.data.manifest.name).pop() : null;

                  if ((data.install_type === 'upgrade') && instance) {
                    return me.$state.go('apps.apps.edit-v2', { instanceId: instance.id, configuration: `?forceConfiguration=${data.force_configuration ? 'yes' : 'no'}` }, { reload: true });
                  }
                  return me.$state.go('apps.apps.install-v2-reload', { appName: encodeURIComponent(data.package_name) });
                }
                return me.$state.go('apps.go_apps_install', { name: `go-apps-${data.package_name}` });
              }

              , 250);
            })
          ;

          const uploadError = data => $scope.form.error = (data != null ? data.error_code : undefined) || 'general';

          $scope.form = {};
          $scope.form.upload_type = 'upload';
          $scope.form.is_active = false;

          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.fileUploadOptions = {
            singleFileUploads:     true,
            limitMultiFileUploads: 1,
            formData:              {
              'API-TOKEN':     window.DP_API_TOKEN,
              'REQUEST-TOKEN': window.DP_REQUEST_TOKEN,
              'SESSION-ID':    window.DP_SESSION_ID
            }
          };

          $scope.$on('fileuploaddone', (e, data) => {
            $scope.form.is_active = false;
            return uploadDone(data.result);
          });
          $scope.$on('fileuploadfail', (e, data) => {
            $scope.form.is_active = false;
            return uploadError(data.result || {});
          });

          return $scope.startUpload = function () {
            $scope.form.error = null;
            if ($scope.form.upload_type === 'upload') {
              $scope.form.is_active = true;
              return $scope.form.uploadScope.submit();
            }
            $scope.form.is_active = true;
            return me.Api.sendPost('/apps/upload-package', { file_url: $scope.form.upload_url }).then((result) => {
              $scope.form.is_active = false;
              return uploadDone(result.data);
            }
              , (result) => {
              $scope.form.is_active = false;
              return uploadError(result.data || {});
            });
          };
        }
        ]
      });
    }
  }
  Admin_Apps_Ctrl_List.initClass();

  return Admin_Apps_Ctrl_List.EXPORT_CTRL();
});
