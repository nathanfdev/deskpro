(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Apps_Ctrl_List, _ref;
    Admin_Apps_Ctrl_List = (function(_super) {
      __extends(Admin_Apps_Ctrl_List, _super);

      function Admin_Apps_Ctrl_List() {
        _ref = Admin_Apps_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Apps_Ctrl_List.CTRL_ID = 'Admin_Apps_Ctrl_List';

      Admin_Apps_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_Apps_Ctrl_List.DEPS = [];

      Admin_Apps_Ctrl_List.prototype.init = function() {
        this.$scope.hide_installed = true;
        this.$scope.packagesFilter = function(hide_installed) {
          var is_installed;
          is_installed = !hide_installed;
          return function(itm) {
            return !itm.is_installed || itm.is_installed === is_installed;
          };
        };
      };

      Admin_Apps_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendDataGet({
          apps: '/apps'
        }).then(function(result) {
          _this.packages = result.data.apps.packages;
          _this.packages = _this.packages.filter(function(x) {
            return !x.is_custom;
          });
          _this.apps = result.data.apps.apps.filter(function(x) {
            return !x["package"].is_custom;
          });
          return _this.custom_apps = result.data.apps.apps.filter(function(x) {
            return x["package"].is_custom;
          });
        });
        return promise;
      };

      Admin_Apps_Ctrl_List.prototype.addAppInstance = function(instanceInfo) {
        var p, _i, _len, _ref1, _results;
        if (!this.apps) {
          this.apps = [];
        }
        this.apps.push(instanceInfo);
        _ref1 = this.packages;
        _results = [];
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          p = _ref1[_i];
          if (p.name === instanceInfo["package"].name) {
            if (!p.apps) {
              p.apps = [];
            }
            p.apps.push(instanceInfo);
            p.is_installed = true;
            break;
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };

      Admin_Apps_Ctrl_List.prototype.updateAppTitle = function(id, title) {
        this.apps.filter(function(x) {
          return x.id === id;
        }).map(function(x) {
          return x.title = title;
        });
        return this.custom_apps.filter(function(x) {
          return x.id === id;
        }).map(function(x) {
          return x.title = title;
        });
      };

      Admin_Apps_Ctrl_List.prototype.ensureCustomAppInList = function(customApp) {
        var exist;
        if (!this.custom_apps) {
          return;
        }
        exist = this.custom_apps.filter(function(x) {
          return x.id === customApp.id;
        });
        if (!exist.length) {
          return this.custom_apps.push(customApp);
        }
      };

      Admin_Apps_Ctrl_List.prototype.showNewApp = function() {
        var saveNewApp,
          _this = this;
        saveNewApp = function(options) {
          var postData;
          postData = {
            options: options
          };
          return _this.Api.sendPutJson('/apps/custom', postData).success(function(info) {
            return _this.$state.go('apps.apps.custom_instance', {
              custom_id: "custom_" + info.id
            });
          });
        };
        return this.$modal.open({
          templateUrl: this.getTemplatePath('Apps/new-app-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              $scope.doCreate = function() {
                $scope.is_loading = true;
                return saveNewApp($scope.opt).then(function() {
                  $modalInstance.dismiss();
                  return $scope.is_loading = false;
                }, function() {
                  return $scope.is_loading = false;
                });
              };
              return $scope.opt = {
                ticket: {}
              };
            }
          ]
        });
      };

      return Admin_Apps_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/