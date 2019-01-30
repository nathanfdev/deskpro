/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['require', 'Admin/Main/Ctrl/Base', 'Admin/Usersources/Helper/UsersourceTypeDecider'
], function(require, Admin_Ctrl_Base, Admin_Usersources_Helper_UsersourceTypeDecider) {
  class Admin_Apps_Ctrl_PackageInstall extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Apps_Ctrl_PackageInstall';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = ['$state', '$http', 'dpTemplateManager'];
    }

    init() {
      this.packageName = this.$stateParams.name.replace(/\.install$/, '');
      // TODO: this is a hack to allow installation of packages which might be scoped to an organization, for instance
      // @deskproapps/app-mailchimp
      this.packageName = this.packageName.replace(/\//, '-').replace('@', '');
      this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
      this.$scope.getController      = () => { return this; };
      this.$scope.setPresaveCallback = callback => { return this.presaveCallback = callback; };
      this.$scope.enableCustomFooter                                  = () => { return this.$scope.has_own_footer = true; };
      this.presaveCallback                                                                        = null;
      this.permission_groups                                                                      = [];
      this.permission_groups_user                                                                 = [];
    }

    initialLoad() {
      const deferred = this.$q.defer();

      this.Api.sendDataGet({
        pack: `/apps/packages/${this.packageName}`,
        agent_groups: '/agent_groups',
        user_groups: '/user_groups'
      }).then(result => {
        let path;
        this.pack        = result.data.pack['package'];
        this.$scope.pack = this.pack;

        for (var val of Array.from(result.data.agent_groups.groups)) {
          this.permission_groups.push({ "value": val.id.toString(), "label": val.title });
        }

        this.permission_groups_user = [{ "value": 0, "label": "" }];
        for (val of Array.from(result.data.user_groups.groups)) {
          if (val.sys_name === 'everyone') { continue; }
          this.permission_groups_user.push({ "value": val.id.toString(), "label": val.title });
        }

        const form_template = this.packageName + '/Install/install.html';
        let installCtrl = null;
        const loadingAssets = [];
        this.$scope.has_display_settings = this.pack.settings_def.filter(x => x.type !== 'hidden').length > 0;

        this.$scope.setting_values = { dp_app: { title: this.pack.title } };

        for (let setting of Array.from(this.pack.settings_def)) {
          if (setting.default_value) {
            this.$scope.setting_values[setting.name] = setting.default_value;
          }
        }

        const getResourcePath = (tag, name) => {
          const asset = this.pack.assets.filter(x => (x.tag === tag) && (x.name === name))[0];
          if (asset) { return asset.blob.relative_url; } else { return null; }
        };

        if (path = getResourcePath('html', 'AdminInterface/Install/install.html')) {
          loadingAssets.push(this.$http.get(path, { responseType: "text" }).success(data => {
            return this.dpTemplateManager.setTemplate(form_template, data);
          }));
        }
        if (path = getResourcePath('js', 'AdminInterface/Install/install.js')) {
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
              this.$scope.default_form  = false;
            } else {
              this.$scope.default_form = true;
            }

            return deferred.resolve();
          });
        } else {
          this.$scope.default_form = true;
          return deferred.resolve();
        }
      });

      return deferred.promise;
    }

    installApp() {
      this.startSpinner('saving_settings');
      if (this.presaveCallback) {
        return this.presaveCallback(this.$scope.setting_values).then(() => {
          return this.doInstall().catch(() => {
            return this.stopSpinner('saving_settings', true);
          });
        }
        , () => {
          return this.stopSpinner('saving_settings', true);
        });
      } else {
        return this.doInstall().catch(() => {
          return this.stopSpinner('saving_settings', true);
        });
      }
    }

    cancelInstall() {
      if (this.usersourceType === 'user') {
        return this.$state.go('crm.usersources');
      } else if (this.usersourceType === 'agent') {
        return this.$state.go('agents.usersources');
      } else {
        return this.$state.go('apps.apps.package', { name: this.pack.name });
      }
    }

    doInstall() {
      let listCtrl = null;
      if ((this.$scope.$parent.ListCtrl != null ? this.$scope.$parent.ListCtrl.addAppInstance : undefined) != null) {
        listCtrl = this.$scope.$parent.ListCtrl;
      }

      const { setting_values } = this.$scope;
      const { pack } = this;
      const { usersourceType } = this;

      const defer = this.$q.defer();
      const modalInstance = this.$modal.open({
        templateUrl: this.getTemplatePath('Apps/install-progress-modal.html'),
        controller: 'Admin_Apps_Ctrl_InstallProgress',
        resolve: {
          pack() { return pack; },
          setting_values() { return setting_values; },
          usersourceType() { return usersourceType; }
        }
      }).result.then(info => defer.resolve(info)
      , info => defer.reject(info));

      defer.promise.then(info => {
        if (listCtrl) {
          if (info.version === 2) {
            if (!info.updated) {
              listCtrl.addAppInstance(info.data, true);
            }
          } else {
            listCtrl.addAppInstance({
              id: info.id,
              title: setting_values.dp_app.title,
              package_name: this.pack.name,
              package: this.pack
            });
          }
        }

        if (this.usersourceType === 'user') {
          __guard__(this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined, x => x.refresh());
          return this.$state.go('crm.usersources.id', { id: info.id });
        } else if (this.usersourceType === 'agent') {
          __guard__(this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined, x1 => x1.refresh());
          return this.$state.go('agents.usersources.id', { id: info.id });
        } else if (info.version === 2) {
          return this.$state.go('apps.apps.instance_v2', { id: `v2_${info.data.id}` });
        } else {
          return this.$state.go('apps.apps.instance', { id: info.id });
        }
      });

      return defer.promise;
    }
  }
  Admin_Apps_Ctrl_PackageInstall.initClass();

  return Admin_Apps_Ctrl_PackageInstall.EXPORT_CTRL();
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}