(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Apps_Ctrl_EditInstance, _ref;
    Admin_Apps_Ctrl_EditInstance = (function(_super) {
      __extends(Admin_Apps_Ctrl_EditInstance, _super);

      function Admin_Apps_Ctrl_EditInstance() {
        _ref = Admin_Apps_Ctrl_EditInstance.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Apps_Ctrl_EditInstance.CTRL_ID = 'Admin_Apps_Ctrl_EditInstance';

      Admin_Apps_Ctrl_EditInstance.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_EditInstance.DEPS = [];

      Admin_Apps_Ctrl_EditInstance.prototype.init = function() {
        this.instanceId = parseInt(this.$stateParams.id);
      };

      Admin_Apps_Ctrl_EditInstance.prototype.initialLoad = function() {
        var d,
          _this = this;
        d = this.$q.defer();
        this.Api.sendDataGet({
          app: '/apps/instances/' + this.instanceId
        }).then(function(result) {
          _this.app = result.data.app.app;
          return _this.Api.sendDataGet({
            pack: '/apps/packages/' + _this.app.package_name
          }).then(function(result) {
            _this.pack = result.data.pack['package'];
            return d.resolve();
          });
        });
        d.promise.then(function() {
          _this.$scope.pack = _this.pack;
          _this.$scope.setting_values = _this.app.settings;
          return _this.$scope.setting_values.dp_app = {
            title: _this.app.title
          };
        });
        return d.promise;
      };

      Admin_Apps_Ctrl_EditInstance.prototype.saveSettings = function() {
        var postData,
          _this = this;
        postData = {
          settings: this.$scope.setting_values
        };
        this.startSpinner('saving_settings');
        return this.Api.sendPostJson("/apps/instances/" + this.instanceId, postData).then(function() {
          return _this.stopSpinner('saving_settings').then(function() {
            _this.$scope.$parent.ListCtrl.updateAppTitle(_this.instanceId, _this.$scope.setting_values.dp_app.title);
            return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
          });
        }, function() {
          return this.stopSpinner('saving_settings');
        });
      };

      Admin_Apps_Ctrl_EditInstance.prototype.showReadme = function() {
        var _this = this;
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
            pack: function() {
              return _this.pack;
            }
          }
        });
      };

      return Admin_Apps_Ctrl_EditInstance;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_EditInstance.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EditInstance.js.map
*/