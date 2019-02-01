define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
  class Admin_Apps_Ctrl_EditInstance extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Apps_Ctrl_EditInstance';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$http', 'dpTemplateManager', '$q'];
    }

    init() {
      this.instanceId = parseInt(this.$stateParams.id);
      this.$scope.getController = () => { return this; };
      this.$scope.setPresaveCallback = callback => { return this.presaveCallback = callback; };
      this.$scope.enableCustomFooter = () => { return this.$scope.has_own_footer = true; };
      this.presaveCallback = null;
    }

    initialLoad() {
      const d = this.$q.defer();
      const d2 = this.$q.defer();

      const service = {
        groups: this.DataService.get('AgentGroups'),
        agents: this.DataService.get('Agents')
      };

      this.Api.sendDataGet({
        app: `/apps/instances/${this.instanceId}`
      }).then( result => {
        this.app = result.data.app.app;
        this.$scope.appId = this.app.id;

        const getData = {
          pack: `/apps/packages/${this.app.package_name}`
        };

        if (this.app.with_permissions) {
          service.agents.all();
          service.groups.all();
        }

        return this.Api.sendDataGet(getData).then( result => {
          this.pack = result.data.pack['package'];
          this.packageName = this.pack.name;

          if (!this.app.with_permissions) {
            return d.resolve();
          } else {
            return this.$q.all([service.agents.all(), service.groups.all()]).then( res => {
              this.$scope.perms = {};
              this.$scope.perms.type = this.app.perm_type;
              this.$scope.perms.usergroups = res[1].map(x => {
                return {
                  id: x.id,
                  name: x.title,
                  checked: this.app.permissions.usergroup_ids.indexOf(x.id) !== -1
                };
              });
              this.$scope.perms.agents = res[0].map(x => {
                return {
                  id: x.id,
                  name: x.display_name,
                  checked: this.app.permissions.person_ids.indexOf(x.id) !== -1
                };
              });

              this.$scope.selected_agents_count = () => this.$scope.perms.agents.filter(x => x.checked).length;
              this.$scope.selected_groups_count = () => this.$scope.perms.usergroups.filter(x => x.checked).length;

              return d.resolve();
            });
          }
        });
      });

      d.promise.then( () => {
        let path;
        this.$scope.pack = this.pack;
        this.$scope.setting_values = this.app.settings;
        if (!this.$scope.setting_values || Util.isArray(this.$scope.setting_values)) {
          this.$scope.setting_values = {};
        }
        this.$scope.setting_values.dp_app = {title: this.app.title};

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
          require([path], function(c) {
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
              this.$scope.install_ctrl = [function() {
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
      });

      return d2.promise;
    }

    saveSettings() {
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

      let perms;
      if (this.app.with_permissions && (this.$scope.perms.type != null) && (this.$scope.perms.type === 'set')) {
        perms = {
          type: 'set',
          usergroup_ids: this.$scope.perms.usergroups.filter(x => x.checked).map(x => x.id),
          person_ids: this.$scope.perms.agents.filter(x => x.checked).map(x => x.id)
        };
      } else {
        perms = { type: 'global' };
      }

      const postData = {
        settings: this.$scope.setting_values,
        permissions: perms
      };

      return this.Api.sendPostJson(`/apps/instances/${this.instanceId}`, postData).then(() => {
        return this.stopSpinner('saving_settings').then(() => {
          this.$scope.$parent.ListCtrl.updateAppTitle(this.instanceId, this.$scope.setting_values.dp_app.title);
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        });
      });
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
    startDelete() {
      const doDelete = () => {
        return this.Api.sendDelete(`/apps/instances/${this.app.id}`).success( () => {

          // If we are viewing with the parent list, we need to remove this
          // app from the list
          if ((this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined) != null) {
            if (this.$scope.$parent != null) {
              this.$scope.$parent.ListCtrl.removeAppInstance(this.app.id);
            }
          }

          // close this view
          return this.$state.go('apps.apps');
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
  }
  Admin_Apps_Ctrl_EditInstance.initClass();

  return Admin_Apps_Ctrl_EditInstance.EXPORT_CTRL();
});
