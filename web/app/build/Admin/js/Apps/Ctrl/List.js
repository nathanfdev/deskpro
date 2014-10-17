(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Apps_Ctrl_List;
    Admin_Apps_Ctrl_List = (function(_super) {
      __extends(Admin_Apps_Ctrl_List, _super);

      function Admin_Apps_Ctrl_List() {
        return Admin_Apps_Ctrl_List.__super__.constructor.apply(this, arguments);
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
        var promise;
        promise = this.Api.sendDataGet({
          apps: '/apps'
        }).then((function(_this) {
          return function(result) {
            _this.packages = result.data.apps.packages;
            _this.packages = _this.packages.filter(function(x) {
              return !x.is_custom;
            });
            _this.general_packages = _this.packages.filter(function(x) {
              return !(x.tags.indexOf('usersources') !== -1 && x.tags.length === 1);
            });
            _this.auth_packages = _this.packages.filter(function(x) {
              return x.tags.indexOf('usersources') !== -1;
            });
            _this.apps = result.data.apps.apps.filter(function(x) {
              return !x["package"].is_custom;
            });
            return _this.custom_apps = result.data.apps.apps.filter(function(x) {
              return x["package"].is_custom;
            });
          };
        })(this));
        return promise;
      };

      Admin_Apps_Ctrl_List.prototype.addAppInstance = function(instanceInfo) {
        var p, _i, _len, _ref, _results;
        if (!this.apps) {
          this.apps = [];
        }
        this.apps.push(instanceInfo);
        _ref = this.packages;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          p = _ref[_i];
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

      Admin_Apps_Ctrl_List.prototype.removeAppInstance = function(instanceId) {
        var app, hasOtherApp, p;
        app = this.apps.find(function(x) {
          return x.id === instanceId;
        });
        this.apps = this.apps.filter(function(x) {
          return x.id !== instanceId;
        });
        this.custom_apps = this.custom_apps.filter(function(x) {
          return x.id !== instanceId;
        });
        if (app) {
          hasOtherApp = false;
          this.apps.map(function(x) {
            if (x["package"].name === app["package"].name) {
              return hasOtherApp = true;
            }
          });
          if (!hasOtherApp) {
            p = this.packages.find(function(x) {
              return x.name === app["package"].name;
            });
            if (p) {
              return p.is_installed = false;
            }
          }
        }
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
        var saveNewApp;
        saveNewApp = (function(_this) {
          return function(options) {
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
        })(this);
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

      Admin_Apps_Ctrl_List.prototype.showUploadApp = function() {
        var me;
        me = this;
        return this.$modal.open({
          templateUrl: this.getTemplatePath('Apps/upload-package-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              var uploadDone, uploadError;
              uploadDone = function(data) {
                return me.initialLoad().then(function() {
                  $modalInstance.dismiss();
                  return me.$timeout(function() {
                    console.log(data);
                    return me.$state.go('apps.go_apps_install', {
                      name: 'go-apps-' + data.package_name
                    });
                  }, 250);
                });
              };
              uploadError = function(data) {
                return $scope.form.error = (data != null ? data.error_code : void 0) || 'general';
              };
              $scope.form = {};
              $scope.form.upload_type = 'upload';
              $scope.form.is_active = false;
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              $scope.fileUploadOptions = {
                singleFileUploads: true,
                limitMultiFileUploads: 1,
                formData: {
                  "API-TOKEN": window.DP_API_TOKEN,
                  "REQUEST-TOKEN": window.DP_REQUEST_TOKEN,
                  "SESSION-ID": window.DP_SESSION_ID
                }
              };
              $scope.$on('fileuploaddone', function(e, data) {
                $scope.form.is_active = false;
                return uploadDone(data.result);
              });
              $scope.$on('fileuploadfail', function(e, data) {
                $scope.form.is_active = false;
                return uploadError(data.result || {});
              });
              return $scope.startUpload = function() {
                $scope.form.error = null;
                if ($scope.form.upload_type === 'upload') {
                  $scope.form.is_active = true;
                  return $scope.form.uploadScope.submit();
                } else {
                  $scope.form.is_active = true;
                  return me.Api.sendPost('/apps/upload-package', {
                    file_url: $scope.form.upload_url
                  }).then(function(result) {
                    $scope.form.is_active = false;
                    return uploadDone(result.data);
                  }, function(result) {
                    $scope.form.is_active = false;
                    return uploadError(result.data || {});
                  });
                }
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

//# sourceMappingURL=List.js.map
