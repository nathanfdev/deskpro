define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'Admin/Usersources/Helper/UsersourceTypeDecider']
, function(Admin_Ctrl_Base, Util, Admin_Usersources_Helper_UsersourceTypeDecider) {
  class Admin_Usersources_Ctrl_EditInstance extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Usersources_Ctrl_EditInstance';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$http', 'dpTemplateManager'];
    }

    init() {
      this.instanceId = this.getInstanceId();
      this.$scope.getController = () => { return this; };
      this.$scope.setPresaveCallback = callback => { return this.presaveCallback = callback; };
      this.$scope.enableCustomFooter = () => { return this.$scope.has_own_footer = true; };
      this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
      this.presaveCallback = null;
      this.app = null;
      return this.$scope.usersource_detailsv2 = {
        brands: [],
        is_all_brands: false

      };
    }

    getInstanceId() { return this.$stateParams.id; }
    getApp2Id() { return `app-${this.instanceId}`; }

    initialLoad() {
      const d = this.$q.defer();
      const d2 = this.$q.defer();
      const d3 = this.$q.defer();

      const brands_promise = this.Api2.sendGet('brands').then( res => {
        return this.brands = res.data.data;
      });

      if (this.instanceId) {
        this.Api2.sendGet(`user_sources/${this.usersourceType}/${this.getApp2Id()}`).then( res => {
          this.$scope.usersource_detailsv2 = res.data.data;
          return d3.resolve();
        });
      } else {
        d3.resolve();
      }

      this.listCtrl().refresh().then(() => {
        let enabled = 0;
        this.listCtrl().usersources.map(source => {
          const s = source.usersource;
          if (this.usersourceType !== s.type) { return; }
          if ('Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO' === s.source_type) { return; }
          if (s.is_enabled) { return enabled++; }
        });
        return this.$scope.can_disable_deskpro = (this.usersourceType === 'user') || (enabled > 0);
      });

      if (this.instanceId) {
        if (this.is_local) {
          this.usersourceId = this.instanceId;
          this.Api.sendGet(`/usersources/${this.usersourceType}/${this.usersourceId}`).then(result => {
            this.usersource = result.data.usersource;
            return d.resolve();

          });
        } else {
          this.Api.sendDataGet({
            app: `/apps/instances/${this.instanceId}`
          }).then( result => {
            this.app = result.data.app != null ? result.data.app.app : undefined;

            this.$scope.app = this.app;
            this.$scope.appId = this.app != null ? this.app.id : undefined;

            if (this.app) {
              return this.Api.sendDataGet({
                extra_info: `/usersources/${this.usersourceType}/app-${this.instanceId}/extra-details`,
                pack: `/apps/packages/${this.app.package_name}`
              }).then( result => {
                this.pack = result.data.pack['package'];
                this.$scope.usersource_details = result.data.extra_info != null ? result.data.extra_info.usersource_details : undefined;
                this.packageName = this.pack.name;
                return d.resolve();
              });
            } else {
              this.usersourceId = this.instanceId;
              return this.Api.sendGet(`/usersources/${this.usersourceType}/${this.usersourceId}`).then(result => {
                this.usersource = result.data.usersource;
                return d.resolve();

              });
            }
          });
        }
      }

      d.promise.then( () => {
        // we do nothing here if its a direct usersource, but if its an app we have some work t do
        if (!this.app) {
          return d2.resolve();
        } else {
          // this is an app instance

          let path;
          this.$scope.pack = this.pack;
          this.$scope.setting_values = this.app.settings;
          if (!this.$scope.setting_values || Util.isArray(this.$scope.setting_values)) {
            this.$scope.setting_values = {};
          }
          this.$scope.setting_values.dp_app = { title: this.app.title };

          this.$scope.has_display_settings = this.pack.settings_def.filter( x => x.type !== 'hidden').length > 0;
          const form_template = this.packageName + '/AdminInterface/Install/settings.html';
          let installCtrl = null;
          const loadingAssets = [];

          const getResourcePath = (tag, name) => {
            const asset = this.pack.assets.filter(x => (x.tag === tag) && (x.name === name))[0];
            if (asset) {
              const cachebust = window.DP_BUILD_TIME;
              return asset.blob.relative_url + '?' + cachebust;
            } else {
              return null;
            }
          };

          if (path = getResourcePath('html', 'AdminInterface/Install/settings.html')) {
            loadingAssets.push(this.$http.get(path, { responseType: "text"}).success(data => {
              return this.dpTemplateManager.setTemplate(form_template, data);
            }));
          }
          if (path = getResourcePath('js', 'AdminInterface/Install/settings.js')) {
            const jsDeferred = this.$q.defer();
            require([path], c => {
              installCtrl = c;
              return jsDeferred.resolve();
            });
            loadingAssets.push(jsDeferred.promise);
          }

          if (loadingAssets.length) {
            return this.$q.all(loadingAssets).then(() => {
              if (installCtrl) {
                this.$scope.install_ctrl = installCtrl;
              } else {
                this.$scope.install_ctrl = [() => {
                }
                ];
              }

              if (form_template) {
                this.$scope.form_template = form_template;
                this.$scope.default_form = false;
              } else {
                this.$scope.default_form = true;
              }

              return d2.resolve();
            });
          } else {
            this.$scope.default_form = true;
            return d2.resolve();
          }
        }
      });

      return this.$q.all([d2.promise, d3.promise, brands_promise]);
    }



    saveSettings() {
      if ((this.brands.length > 1) && !this.$scope.usersource_detailsv2.is_all_brands && !this.$scope.usersource_detailsv2.brands.length) {
        window.alert("Usersource needs to be linked to at least one Brand");
        return false;
      }

      this.startSpinner('saving_settings');
      if (this.presaveCallback) {
        return this.presaveCallback().then( () => {
          return this.doSaveSettings().catch(() => {
            return this.stopSpinner('saving_settings', true);
          });
        }
        , () => {
          return this.stopSpinner('saving_settings', true);
        });
      } else {
        return this.doSaveSettings().finally(() => {
          return this.stopSpinner('saving_settings', true);
        });
      }
    }



    doSaveSettings() {
      let postData = {
        settings: this.$scope.setting_values
      };

      this.Api.sendPostJson(`/apps/instances/${this.instanceId}`, postData).then(() => {
        this.Api.sendGet(`/usersources/${this.usersourceType}/app-${this.instanceId}/extra-details`).then(result => {
          return this.$scope.usersource_details = result.data.usersource_details;
        });
        return this.stopSpinner('saving_settings').then(() => {
          this.listCtrl().refresh();
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        });
      });

      if (this.usersourceType === 'user') {
        postData = {
          brands: this.$scope.usersource_detailsv2.brands,
          is_all_brands: this.$scope.usersource_detailsv2.is_all_brands
        };

        return this.Api2.sendPutJson(`/user_sources/${this.usersourceType}/${this.getApp2Id()}`, postData);
      }
    }



    doSaveUsersource() {
      let postData = {
        title: this.usersource.title,
        is_enabled: this.usersource.is_enabled,
        options: this.usersource.options
      };

      this.Api.sendPostJson(`/usersources/${this.usersourceType}/${this.usersourceId}`, postData).then(
        () => {
          this.listCtrl().refresh();
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        },
        res => {
          const msg = this.getRegisteredMessage(res.data.error_code) || res.data.error_message || '';
          return this.Growl.error(msg);
      });

      if (this.usersourceType === 'user') {
        postData = {
          brands: this.$scope.usersource_detailsv2.brands,
          is_all_brands: this.$scope.usersource_detailsv2.is_all_brands
        };

        return this.Api2.sendPutJson(`/user_sources/${this.usersourceType}/${this.getApp2Id()}`, postData);
      }
    }

    saveUsersource() {
      if ((this.brands.length > 1) && !this.$scope.usersource_detailsv2.is_all_brands && !this.$scope.usersource_detailsv2.brands.length) {
        window.alert("Usersource needs to be linked to at least one Brand");
        return false;
      }

      this.startSpinner('saving_settings');
      return this.doSaveUsersource().finally(() => this.stopSpinner('saving_settings'));
    }


    cannotDeleteUsersource() {
      return alert("The DeskPRO usersource cannot be uninstalled. However, you can disable it by unchecking the box on the form and saving.");
    }



    /*
      * Shows readme modal window
      */
    showReadme() {
      return this.$modal.open({
        templateUrl: this.getTemplatePath('Apps/readme-modal.html'),
        controller: ['$scope', '$modalInstance', 'pack', function($scope, $modalInstance, pack) {
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.pack = pack;
        }
        ],
        resolve: {
          pack: () => {
            return this.pack;
          }
        }
      });
    }



    /*
     * SHow delete modal
     */
    startDelete($event) {
      if ($event) {
        $event.preventDefault();
      }

      const doDelete = () => {
        return this.Api.sendDelete(`/apps/instances/${this.app.id}`).success( () => {

          // If we are viewing with the parent list, we need to remove this
          // app from the list
          this.listCtrl().refresh();

          // close this view
          return this.$state.go('^');
        });
      };

      return this.$modal.open({
        templateUrl: this.getTemplatePath('Apps/instance-delete-modal.html'),
        controller: ['app', '$scope', '$modalInstance', function(app, $scope, $modalInstance) {
          $scope.app = app;
          $scope.dismiss = () => $modalInstance.close();

          return $scope.confirm = function() {
            $scope.is_loading = true;
            return doDelete().then(() => $modalInstance.close());
          };
        }
        ],
        resolve: {
          app: () => {
            return this.app;
          }
        }
      });
    }



    listCtrl() {
      return (this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined) || {refresh: () => {}};
    }

    handleBrand(brandId, e) {
      const index = this.$scope.usersource_detailsv2.brands.indexOf(brandId);
      if (index === -1) {
        return this.$scope.usersource_detailsv2.brands.unshift(brandId);
      } else {
        if (this.$scope.usersource_detailsv2.brands.length > 1) {
          return this.$scope.usersource_detailsv2.brands.splice(index, 1);
        } else {
          alert("Usersource needs to be linked to at least one Brand");
          $(e.target).prop("checked", true);
          return true;
        }
      }
    }
  }
  Admin_Usersources_Ctrl_EditInstance.initClass();



  return Admin_Usersources_Ctrl_EditInstance.EXPORT_CTRL();
});
