(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Apps_Ctrl_PackageInfo, _ref;
    Admin_Apps_Ctrl_PackageInfo = (function(_super) {
      __extends(Admin_Apps_Ctrl_PackageInfo, _super);

      function Admin_Apps_Ctrl_PackageInfo() {
        _ref = Admin_Apps_Ctrl_PackageInfo.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Apps_Ctrl_PackageInfo.CTRL_ID = 'Admin_Apps_Ctrl_PackageInfo';

      Admin_Apps_Ctrl_PackageInfo.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_PackageInfo.DEPS = [];

      Admin_Apps_Ctrl_PackageInfo.prototype.init = function() {
        this.packageName = this.$stateParams.name;
      };

      Admin_Apps_Ctrl_PackageInfo.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendDataGet({
          pack: '/apps/packages/' + this.packageName
        }).then(function(result) {
          return _this.pack = result.data.pack['package'];
        });
        return promise;
      };

      /*
        	# Shows the install overlay
      */


      Admin_Apps_Ctrl_PackageInfo.prototype.startInstall = function() {
        var doInstall, inst, listCtrl, _ref1,
          _this = this;
        if (((_ref1 = this.$scope.$parent.ListCtrl) != null ? _ref1.addAppInstance : void 0) != null) {
          listCtrl = this.$scope.$parent.ListCtrl;
        }
        doInstall = function(setting_values) {
          return _this.Api.sendPut("/apps/packages/" + _this.packageName, {
            settings: setting_values
          }).success(function(info) {
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
          });
        };
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Apps/package-install-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'pack', function($scope, $modalInstance, pack) {
              var setting, _i, _len, _ref2, _results;
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              $scope.doInstall = function() {
                $scope.is_loading = true;
                return doInstall($scope.setting_values).then(function() {
                  return $modalInstance.dismiss();
                }, function() {
                  return $scope.is_loading = false;
                });
              };
              $scope.pack = pack;
              $scope.setting_values = {
                dp_app: {
                  title: pack.title
                }
              };
              _ref2 = pack.settings_def;
              _results = [];
              for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
                setting = _ref2[_i];
                if (setting.default_value) {
                  _results.push($scope.setting_values[setting.name] = setting.default_value);
                } else {
                  _results.push(void 0);
                }
              }
              return _results;
            }
          ],
          resolve: {
            pack: function() {
              return _this.pack;
            }
          }
        });
      };

      return Admin_Apps_Ctrl_PackageInfo;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_PackageInfo.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=PackageInfo.js.map
*/