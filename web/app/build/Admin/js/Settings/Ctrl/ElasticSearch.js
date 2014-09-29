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

      Admin_Settings_Ctrl_ElasticSearch.DEPS = ['$timeout'];

      Admin_Settings_Ctrl_ElasticSearch.prototype.init = function() {
        this.pollTimer = null;
        this.hasInit = false;
      };

      Admin_Settings_Ctrl_ElasticSearch.prototype.initialLoad = function() {
        return this.updateStatus();
      };

      Admin_Settings_Ctrl_ElasticSearch.prototype.startStatusPoller = function() {
        if (this.pollTimer) {
          this.$timeout.cancel(this.pollTimer);
          this.pollTimer = null;
        }
        return this.pollTimer = this.$timeout((function(_this) {
          return function() {
            return _this.updateStatus();
          };
        })(this), 1500);
      };

      Admin_Settings_Ctrl_ElasticSearch.prototype.updateStatus = function() {
        return this.Api.sendDataGet({
          'settings': '/elastic-search/settings',
          'status': '/elastic-search/index-status'
        }).then((function(_this) {
          return function(res) {
            var _ref, _ref1, _ref2;
            if (!_this.hasInit) {
              _this.$scope.settings = res.data.settings.elastic_settings;
              _this.$scope.was_on = _this.$scope.settings.enabled;
              _this.hasInit = true;
            }
            _this.$scope.status = res.data.status;
            _this.$scope.indexer_status = (_ref = res.data.status) != null ? _ref.indexer_status : void 0;
            _this.$scope.indexer_log = (_ref1 = res.data.status) != null ? _ref1.indexer_log : void 0;
            _this.$scope.info = (_ref2 = res.data.status) != null ? _ref2.info : void 0;
            if (_this.$scope.status.is_indexing) {
              _this.startStatusPoller();
            }
            return null;
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
            if (_this.$scope.settings.enabled && !_this.$scope.was_on) {
              _this.$scope.was_on = true;
              _this.$scope.status = {
                is_indexing: true
              };
              _this.$scope.indexer_status = null;
              _this.$scope.indexer_log = null;
            }
            return _this.updateStatus().then(function() {
              _this.stopSpinner('saving', true);
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            return _this.stopSpinner('saving', true);
          };
        })(this));
      };

      Admin_Settings_Ctrl_ElasticSearch.prototype.startReindex = function() {
        return this.showConfirm("Are you sure you want to reset your search index? This will wipe the index and search results will not work until the re-indexing has complete.").result.then((function(_this) {
          return function() {
            var postData;
            postData = {
              elastic_settings: _this.$scope.settings
            };
            postData.reindex = true;
            return _this.Api.sendPostJson('/elastic-search/settings', postData).success(function() {
              _this.settings = angular.copy(_this.$scope.settings);
              _this.$scope.status = {
                is_indexing: true
              };
              _this.$scope.indexer_status = null;
              _this.$scope.indexer_log = null;
              return _this.updateStatus();
            }).error(function(info, code) {
              return _this.stopSpinner('saving', true);
            });
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
              url: _this.$scope.settings.url
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
