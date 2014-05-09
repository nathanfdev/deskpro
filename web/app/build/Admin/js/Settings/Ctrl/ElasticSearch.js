(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Settings_Ctrl_ElasticSearch;
    Admin_Settings_Ctrl_ElasticSearch = (function(_super) {
      __extends(Admin_Settings_Ctrl_ElasticSearch, _super);

      function Admin_Settings_Ctrl_ElasticSearch() {
        return Admin_Settings_Ctrl_ElasticSearch.__super__.constructor.apply(this, arguments);
      }

      Admin_Settings_Ctrl_ElasticSearch.CTRL_ID = 'Admin_Settings_Ctrl_ElasticSearch';

      Admin_Settings_Ctrl_ElasticSearch.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_ElasticSearch.prototype.init = function() {};

      Admin_Settings_Ctrl_ElasticSearch.prototype.initialLoad = function() {
        return this.Api.sendDataGet({
          'settings': '/elastic-search/settings'
        }).then((function(_this) {
          return function(res) {
            _this.$scope.settings = res.data.settings.elastic_settings;
            return _this.$scope.was_on = _this.$scope.settings.enabled;
          };
        })(this));
      };

      Admin_Settings_Ctrl_ElasticSearch.prototype.saveSettings = function() {
        var postData;
        this.startSpinner('saving');
        postData = {
          elastic_settings: this.$scope.settings
        };
        return this.Api.sendPostJson('/elastic-search/settings', postData).success((function(_this) {
          return function() {
            _this.settings = angular.copy(_this.$scope.settings);
            return _this.stopSpinner('saving').then(function() {
              if (_this.$scope.settings.enabled && !_this.$scope.was_on) {
                _this.$scope.settings.requires_reset = true;
              }
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            return _this.stopSpinner('saving', true);
          };
        })(this));
      };


      /*
        	 * Show the test account modal
       */

      Admin_Settings_Ctrl_ElasticSearch.prototype.testSettingsModal = function() {
        var inst, loadAccountTest;
        loadAccountTest = (function(_this) {
          return function() {
            var postData;
            postData = {
              host: _this.$scope.settings.host,
              port: _this.$scope.settings.port
            };
            return _this.Api.sendPostJson('/elastic-search/settings/test', postData);
          };
        })(this);
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('ElasticSearch/test-settings-modal.html'),
          controller: [
            '$scope', '$modalInstance', (function(_this) {
              return function($scope, $modalInstance) {
                var testNow;
                $scope.dismiss = function() {
                  return $modalInstance.dismiss();
                };
                $scope.showLog = function() {
                  return $scope.showing_log = true;
                };
                testNow = function() {
                  $scope.showing_log = false;
                  $scope.is_testing = true;
                  return loadAccountTest().success(function(result) {
                    $scope.is_testing = false;
                    $scope.is_success = result.is_success;
                    return $scope.log = result.log;
                  }).error(function() {
                    $scope.showing_log = true;
                    $scope.is_testing = false;
                    $scope.is_success = false;
                    return $scope.log = "Server Error";
                  });
                };
                testNow();
                return $scope.testNow = function() {
                  return testNow();
                };
              };
            })(this)
          ]
        });
      };

      return Admin_Settings_Ctrl_ElasticSearch;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_ElasticSearch.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ElasticSearch.js.map
