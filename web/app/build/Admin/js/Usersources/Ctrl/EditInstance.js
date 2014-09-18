(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'Admin/Usersources/Helper/UsersourceTypeDecider'], function(Admin_Ctrl_Base, Util, Admin_Usersources_Helper_UsersourceTypeDecider) {
    var Admin_Usersources_Ctrl_EditInstance;
    Admin_Usersources_Ctrl_EditInstance = (function(_super) {
      __extends(Admin_Usersources_Ctrl_EditInstance, _super);

      function Admin_Usersources_Ctrl_EditInstance() {
        return Admin_Usersources_Ctrl_EditInstance.__super__.constructor.apply(this, arguments);
      }

      Admin_Usersources_Ctrl_EditInstance.CTRL_ID = 'Admin_Usersources_Ctrl_EditInstance';

      Admin_Usersources_Ctrl_EditInstance.CTRL_AS = 'Ctrl';

      Admin_Usersources_Ctrl_EditInstance.DEPS = ['$http', 'dpTemplateManager'];

      Admin_Usersources_Ctrl_EditInstance.prototype.init = function() {
        this.instanceId = this.$stateParams.id;
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
        this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
        this.presaveCallback = null;
        this.app = null;
      };

      Admin_Usersources_Ctrl_EditInstance.prototype.initialLoad = function() {
        var d, d2;
        d = this.$q.defer();
        d2 = this.$q.defer();
        this.Api.sendDataGet({
          app: '/apps/instances/' + this.instanceId
        }).then((function(_this) {
          return function(result) {
            var _ref;
            _this.app = (_ref = result.data.app) != null ? _ref.app : void 0;
            if (_this.app) {
              return _this.Api.sendDataGet({
                pack: '/apps/packages/' + _this.app.package_name
              }).then(function(result) {
                _this.pack = result.data.pack['package'];
                _this.packageName = _this.pack.name;
                return d.resolve();
              });
            } else {
              _this.usersourceId = _this.instanceId;
              return _this.Api.sendGet('/usersources/' + _this.usersourceType + '/' + _this.usersourceId).then(function(result) {
                _this.usersource = result.data.usersource;
                return d.resolve();
              });
            }
          };
        })(this));
        d.promise.then((function(_this) {
          return function() {
            var form_template, getResourcePath, installCtrl, jsDeferred, loadingAssets, path;
            if (!_this.app) {
              return d2.resolve();
            } else {
              _this.$scope.pack = _this.pack;
              _this.$scope.setting_values = _this.app.settings;
              if (!_this.$scope.setting_values || Util.isArray(_this.$scope.setting_values)) {
                _this.$scope.setting_values = {};
              }
              _this.$scope.setting_values.dp_app = {
                title: _this.app.title
              };
              _this.$scope.has_display_settings = _this.pack.settings_def.filter(function(x) {
                return x.type !== 'hidden';
              }).length > 0;
              form_template = _this.packageName + '/AdminInterface/Install/settings.html';
              installCtrl = null;
              loadingAssets = [];
              getResourcePath = function(tag, name) {
                var asset, cachebust;
                asset = _this.pack.assets.filter(function(x) {
                  return x.tag === tag && x.name === name;
                })[0];
                if (asset) {
                  cachebust = window.DP_BUILD_TIME;
                  return asset.blob.relative_url + '?' + cachebust;
                } else {
                  return null;
                }
              };
              if (path = getResourcePath('html', 'AdminInterface/Install/settings.html')) {
                loadingAssets.push(_this.$http.get(path, {
                  responseType: "text"
                }).success(function(data) {
                  return _this.dpTemplateManager.setTemplate(form_template, data);
                }));
              }
              if (path = getResourcePath('js', 'AdminInterface/Install/settings.js')) {
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
                  return d2.resolve();
                });
              } else {
                _this.$scope.default_form = true;
                return d2.resolve();
              }
            }
          };
        })(this));
        return d2.promise;
      };

      Admin_Usersources_Ctrl_EditInstance.prototype.saveSettings = function() {
        this.startSpinner('saving_settings');
        if (this.presaveCallback) {
          return this.presaveCallback().then((function(_this) {
            return function() {
              return _this.doSaveSettings()["catch"](function() {
                return _this.stopSpinner('saving_settings', true);
              });
            };
          })(this), (function(_this) {
            return function() {
              return _this.stopSpinner('saving_settings', true);
            };
          })(this));
        } else {
          return this.doSaveSettings()["finally"]((function(_this) {
            return function() {
              return _this.stopSpinner('saving_settings', true);
            };
          })(this));
        }
      };

      Admin_Usersources_Ctrl_EditInstance.prototype.doSaveSettings = function() {
        var postData;
        postData = {
          settings: this.$scope.setting_values
        };
        return this.Api.sendPostJson("/apps/instances/" + this.instanceId, postData).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving_settings').then(function() {
              var _ref, _ref1;
              if ((_ref = _this.$scope.$parent) != null) {
                if ((_ref1 = _ref.ListCtrl) != null) {
                  _ref1.refresh();
                }
              }
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this));
      };

      Admin_Usersources_Ctrl_EditInstance.prototype.saveUsersource = function() {
        var postData;
        this.startSpinner('saving_settings');
        postData = {
          title: this.usersource.title,
          is_enabled: this.usersource.is_enabled
        };
        return this.Api.sendPostJson('/usersources/' + this.usersourceType + '/' + this.usersourceId, postData).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving_settings').then(function() {
              var _ref, _ref1;
              if ((_ref = _this.$scope.$parent) != null) {
                if ((_ref1 = _ref.ListCtrl) != null) {
                  _ref1.refresh();
                }
              }
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this));
      };

      Admin_Usersources_Ctrl_EditInstance.prototype.cannotDeleteUsersource = function() {
        return alert("The DeskPRO usersource cannot be uninstalled. However, you can disable it by unchecking the box on the form and saving.");
      };


      /*
        	 * Shows readme modal window
       */

      Admin_Usersources_Ctrl_EditInstance.prototype.showReadme = function() {
        return this.$modal.open({
          templateUrl: this.getTemplatePath('Apps/readme-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'pack', function($scope, $modalInstance, pack) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.pack = pack;
            }
          ],
          resolve: {
            pack: (function(_this) {
              return function() {
                return _this.pack;
              };
            })(this)
          }
        });
      };


      /*
      		 * SHow delete modal
       */

      Admin_Usersources_Ctrl_EditInstance.prototype.startDelete = function() {
        var doDelete;
        doDelete = (function(_this) {
          return function() {
            return _this.Api.sendDelete('/apps/instances/' + _this.app.id).success(function() {
              var _ref, _ref1, _ref2;
              if (((_ref = _this.$scope.$parent) != null ? _ref.ListCtrl : void 0) != null) {
                if ((_ref1 = _this.$scope.$parent) != null) {
                  if ((_ref2 = _ref1.ListCtrl) != null) {
                    _ref2.refresh();
                  }
                }
              }
              return _this.$state.go('^');
            });
          };
        })(this);
        return this.$modal.open({
          templateUrl: this.getTemplatePath('Apps/instance-delete-modal.html'),
          controller: [
            'app', '$scope', '$modalInstance', function(app, $scope, $modalInstance) {
              $scope.app = app;
              $scope.dismiss = function() {
                return $modalInstance.close();
              };
              return $scope.confirm = function() {
                $scope.is_loading = true;
                return doDelete().then(function() {
                  return $modalInstance.close();
                });
              };
            }
          ],
          resolve: {
            app: (function(_this) {
              return function() {
                return _this.app;
              };
            })(this)
          }
        });
      };

      return Admin_Usersources_Ctrl_EditInstance;

    })(Admin_Ctrl_Base);
    return Admin_Usersources_Ctrl_EditInstance.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditInstance.js.map
