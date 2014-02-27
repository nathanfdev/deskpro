(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
    var Admin_Apps_Ctrl_EditInstance;
    Admin_Apps_Ctrl_EditInstance = (function(_super) {
      __extends(Admin_Apps_Ctrl_EditInstance, _super);

      function Admin_Apps_Ctrl_EditInstance() {
        return Admin_Apps_Ctrl_EditInstance.__super__.constructor.apply(this, arguments);
      }

      Admin_Apps_Ctrl_EditInstance.CTRL_ID = 'Admin_Apps_Ctrl_EditInstance';

      Admin_Apps_Ctrl_EditInstance.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_EditInstance.DEPS = [];

      Admin_Apps_Ctrl_EditInstance.prototype.init = function() {
        this.instanceId = parseInt(this.$stateParams.id);
      };

      Admin_Apps_Ctrl_EditInstance.prototype.initialLoad = function() {
        var d;
        d = this.$q.defer();
        this.Api.sendDataGet({
          app: '/apps/instances/' + this.instanceId
        }).then((function(_this) {
          return function(result) {
            _this.app = result.data.app.app;
            return _this.Api.sendDataGet({
              pack: '/apps/packages/' + _this.app.package_name
            }).then(function(result) {
              _this.pack = result.data.pack['package'];
              return d.resolve();
            });
          };
        })(this));
        d.promise.then((function(_this) {
          return function() {
            _this.$scope.pack = _this.pack;
            _this.$scope.setting_values = _this.app.settings;
            if (!_this.$scope.setting_values || Util.isArray(_this.$scope.setting_values)) {
              _this.$scope.setting_values = {};
            }
            return _this.$scope.setting_values.dp_app = {
              title: _this.app.title
            };
          };
        })(this));
        return d.promise;
      };


      /*
        	 * Saves settings
       */

      Admin_Apps_Ctrl_EditInstance.prototype.saveSettings = function() {
        var postData;
        postData = {
          settings: this.$scope.setting_values
        };
        this.startSpinner('saving_settings');
        return this.Api.sendPostJson("/apps/instances/" + this.instanceId, postData).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving_settings').then(function() {
              _this.$scope.$parent.ListCtrl.updateAppTitle(_this.instanceId, _this.$scope.setting_values.dp_app.title);
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this), function() {
          return this.stopSpinner('saving_settings');
        });
      };


      /*
        	 * Shows readme modal window
       */

      Admin_Apps_Ctrl_EditInstance.prototype.showReadme = function() {
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

      Admin_Apps_Ctrl_EditInstance.prototype.startDelete = function() {
        var doDelete;
        doDelete = (function(_this) {
          return function() {
            return _this.Api.sendDelete('/apps/instances/' + _this.app.id).success(function() {
              var _ref, _ref1;
              if (((_ref = _this.$scope.$parent) != null ? _ref.ListCtrl : void 0) != null) {
                if ((_ref1 = _this.$scope.$parent) != null) {
                  _ref1.ListCtrl.removeAppInstance(_this.app.id);
                }
              }
              return _this.$state.go('apps.apps');
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

      return Admin_Apps_Ctrl_EditInstance;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_EditInstance.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditInstance.js.map
