define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_Settings_Ctrl_ElasticSearch extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Settings_Ctrl_ElasticSearch';
      this.CTRL_AS   = 'Settings';
      this.DEPS      = ['$timeout'];
    }

    init() {
      this.pollTimer = null;
      this.hasInit = false;
    }

    initialLoad() {
      return this.updateStatus();
    }

    startStatusPoller() {
      if (this.pollTimer) {
        this.$timeout.cancel(this.pollTimer);
        this.pollTimer = null;
      }

      return this.pollTimer = this.$timeout(() => this.updateStatus()
      , 1500);
    }

    updateStatus() {
      this.Api.sendGet('/elastic-search/index-status').then((res) => {
        this.$scope.status         = res.data;
        this.$scope.indexer_status = res.data != null ? res.data.indexer_status : undefined;
        this.$scope.indexer_log    = res.data != null ? res.data.indexer_log : undefined;
        this.$scope.info           = res.data != null ? res.data.info : undefined;
        if (this.$scope.status.is_indexing) { return this.startStatusPoller(); }
      });

      return this.Api.sendGet('/elastic-search/settings').then((res) => {
        if (this.hasInit) { return; }
        this.$scope.settings       = res.data.elastic_settings;
        this.$scope.was_on         = this.$scope.settings.enabled;
        return this.hasInit = true;
      });
    }

    saveSettings() {
      this.startSpinner('saving');
      const postData = { elastic_settings: this.$scope.settings };
      return this.Api.sendPostJson('/elastic-search/settings', postData).success(() => {
        this.settings = angular.copy(this.$scope.settings);

        if (this.$scope.settings.enabled && !this.$scope.was_on) {
          this.$scope.was_on = true;
          this.$scope.status         = { is_indexing: true };
          this.$scope.indexer_status = null;
          this.$scope.indexer_log    = null;
        }

        return this.updateStatus().then(() => {
          this.stopSpinner('saving', true);
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        });
      }).error((info, code) => {
        this.Growl.error(info.error_message);
        this.stopSpinner('saving', true);
        return this.$scope.settings.enabled = false;
      });
    }

    startReindex() {
      return this.showConfirm('Are you sure you want to reset your search index? This will wipe the index and search results will not work until the re-indexing has complete.').result.then(() => {
        const postData = { elastic_settings: this.$scope.settings };
        postData.reindex = true;

        return this.Api.sendPostJson('/elastic-search/settings', postData).success(() => {
          this.settings = angular.copy(this.$scope.settings);

          this.$scope.status         = { is_indexing: true };
          this.$scope.indexer_status = null;
          this.$scope.indexer_log    = null;

          return this.updateStatus();
        }).error((info, code) => this.stopSpinner('saving', true));
      });
    }

    /*
      * Show the test account modal
    */
    testSettingsModal() {
      let inst;
      const loadAccountTest = () => {
        const postData = {
          url:          this.$scope.settings.url,
          tika_enabled: this.$scope.settings.tika_enabled,
          tika_ip:      this.$scope.settings.tika_ip,
          tika_port:    this.$scope.settings.tika_port
        };
        return this.Api.sendPostJson('/elastic-search/settings/test', postData);
      };

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('ElasticSearch/test-settings-modal.html'),
        controller:  ['$scope', '$modalInstance', ($scope, $modalInstance) => {
          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.showLog = () => $scope.showing_log = true;

          const testNow = () => {
            $scope.showing_log = false;
            $scope.is_testing = true;

            return loadAccountTest().success((result) => {
              $scope.is_testing    = false;
              $scope.is_success    = result.is_success;
              return $scope.log           = result.log;
            }).error(() => {
              $scope.showing_log   = true;
              $scope.is_testing    = false;
              $scope.is_success    = false;
              return $scope.log           = 'Server Error';
            });
          };

          testNow();

          return $scope.testNow = () => testNow();
        }
        ]
      });
    }
  }
  Admin_Settings_Ctrl_ElasticSearch.initClass();

  return Admin_Settings_Ctrl_ElasticSearch.EXPORT_CTRL();
});
