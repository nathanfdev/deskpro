(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['require', 'Admin/Main/Ctrl/Base'], function(require, Admin_Ctrl_Base) {
    var Admin_Apps_Ctrl_PackageInstall;
    Admin_Apps_Ctrl_PackageInstall = (function(_super) {
      __extends(Admin_Apps_Ctrl_PackageInstall, _super);

      function Admin_Apps_Ctrl_PackageInstall() {
        return Admin_Apps_Ctrl_PackageInstall.__super__.constructor.apply(this, arguments);
      }

      Admin_Apps_Ctrl_PackageInstall.CTRL_ID = 'Admin_Apps_Ctrl_PackageInstall';

      Admin_Apps_Ctrl_PackageInstall.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_PackageInstall.DEPS = ['$http', 'dpTemplateManager'];

      Admin_Apps_Ctrl_PackageInstall.prototype.init = function() {
        this.packageName = this.$stateParams.name.replace(/\.install$/, '');
        this.$scope.getController = (function(_this) {
          return function() {
            return _this;
          };
        })(this);
        this.$scope.setPresaveCallback = (function(_this) {
          return function(callback) {
            return _this.presaveCallback = callback;
          };
        })(this);
        this.$scope.enableCustomFooter = (function(_this) {
          return function() {
            return _this.$scope.has_own_footer = true;
          };
        })(this);
        this.presaveCallback = null;
      };

      Admin_Apps_Ctrl_PackageInstall.prototype.initialLoad = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendDataGet({
          pack: '/apps/packages/' + this.packageName
        }).then((function(_this) {
          return function(result) {
            var form_template, getResourcePath, installCtrl, jsDeferred, loadingAssets, path, setting, _i, _len, _ref;
            _this.pack = result.data.pack['package'];
            _this.$scope.pack = _this.pack;
            form_template = _this.packageName + '/Install/install.html';
            installCtrl = null;
            loadingAssets = [];
            _this.$scope.has_display_settings = _this.pack.settings_def.filter(function(x) {
              return x.type !== 'hidden';
            }).length > 0;
            _this.$scope.setting_values = {
              dp_app: {
                title: _this.pack.title
              }
            };
            _ref = _this.pack.settings_def;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              setting = _ref[_i];
              if (setting.default_value) {
                _this.$scope.setting_values[setting.name] = setting.default_value;
              }
            }
            getResourcePath = function(tag, name) {
              var asset;
              asset = _this.pack.assets.filter(function(x) {
                return x.tag === tag && x.name === name;
              })[0];
              if (asset) {
                return asset.blob.relative_url;
              } else {
                return null;
              }
            };
            if (path = getResourcePath('html', 'AdminInterface/Install/install.html')) {
              loadingAssets.push(_this.$http.get(path, {
                responseType: "text"
              }).success(function(data) {
                return _this.dpTemplateManager.setTemplate(form_template, data);
              }));
            }
            if (path = getResourcePath('js', 'AdminInterface/Install/install.js')) {
              jsDeferred = _this.$q.defer();
              require([path], function(c) {
                installCtrl = c;
                return jsDeferred.resolve();
              });
              loadingAssets.push(jsDeferred.promise);
            }
            if (loadingAssets.length) {
              return _this.$q.all(loadingAssets).then(function() {
                if (installCtrl) {
                  _this.$scope.install_ctrl = installCtrl;
                } else {
                  _this.$scope.install_ctrl = [function() {}];
                }
                if (form_template) {
                  _this.$scope.form_template = form_template;
                  _this.$scope.default_form = false;
                } else {
                  _this.$scope.default_form = true;
                }
                return deferred.resolve();
              });
            } else {
              _this.$scope.default_form = true;
              return deferred.resolve();
            }
          };
        })(this));
        return deferred.promise;
      };

      Admin_Apps_Ctrl_PackageInstall.prototype.installApp = function() {
        this.startSpinner('saving_settings');
        if (this.presaveCallback) {
          return this.presaveCallback(this.$scope.setting_values).then((function(_this) {
            return function() {
              return _this.doInstall()["catch"](function() {
                return _this.stopSpinner('saving_settings', true);
              });
            };
          })(this), (function(_this) {
            return function() {
              return _this.stopSpinner('saving_settings', true);
            };
          })(this));
        } else {
          return this.doInstall()["catch"]((function(_this) {
            return function() {
              return _this.stopSpinner('saving_settings', true);
            };
          })(this));
        }
      };

      Admin_Apps_Ctrl_PackageInstall.prototype.cancelInstall = function() {
        return this.$state.go('apps.apps.package', {
          name: this.pack.name
        });
      };

      Admin_Apps_Ctrl_PackageInstall.prototype.doInstall = function() {
        var defer, listCtrl, modalInstance, pack, setting_values, _ref;
        listCtrl = null;
        if (((_ref = this.$scope.$parent.ListCtrl) != null ? _ref.addAppInstance : void 0) != null) {
          listCtrl = this.$scope.$parent.ListCtrl;
        }
        setting_values = this.$scope.setting_values;
        pack = this.pack;
        defer = this.$q.defer();
        modalInstance = this.$modal.open({
          templateUrl: this.getTemplatePath('Apps/install-progress-modal.html'),
          controller: 'Admin_Apps_Ctrl_InstallProgress',
          resolve: {
            pack: function() {
              return pack;
            },
            setting_values: function() {
              return setting_values;
            }
          }
        }).result.then((function(_this) {
          return function(info) {
            return defer.resolve(info);
          };
        })(this), (function(_this) {
          return function(info) {
            return defer.reject(info);
          };
        })(this));
        defer.promise.then((function(_this) {
          return function(info) {
            var instanceInfo;
            if (listCtrl) {
              instanceInfo = {
                id: info.id,
                title: setting_values.dp_app.title,
                package_name: _this.pack.name,
                "package": _this.pack
              };
              listCtrl.addAppInstance(instanceInfo);
            }
            return _this.$state.go('apps.apps.instance', {
              id: info.id
            });
          };
        })(this));
        return defer.promise;
      };

      return Admin_Apps_Ctrl_PackageInstall;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_PackageInstall.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=PackageInstall.js.map
