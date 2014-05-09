(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerCron_Ctrl_Logs;
    Admin_ServerCron_Ctrl_Logs = (function(_super) {
      __extends(Admin_ServerCron_Ctrl_Logs, _super);

      function Admin_ServerCron_Ctrl_Logs() {
        return Admin_ServerCron_Ctrl_Logs.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerCron_Ctrl_Logs.CTRL_ID = 'Admin_ServerCron_Ctrl_Logs';

      Admin_ServerCron_Ctrl_Logs.CTRL_AS = 'LogsCtrl';

      Admin_ServerCron_Ctrl_Logs.DEPS = [];

      Admin_ServerCron_Ctrl_Logs.prototype.init = function() {
        this.server_cron_logs = null;
        this.page = 1;
        this.num_pages = 0;
        this.page_nums = [1];
        this.filter = {
          job_id: '',
          priority: '',
          page: 1
        };
        return this.initializeScopeWatching();
      };

      Admin_ServerCron_Ctrl_Logs.prototype.initialLoad = function() {
        return this.loadResults();
      };


      /*
       	 *
       */

      Admin_ServerCron_Ctrl_Logs.prototype.loadResults = function() {
        var data_promise;
        this.startSpinner('paginating_server_cron_logs');
        data_promise = this.Api.sendGet('/server_cron/logs', {
          job_id: this.filter.job_id,
          priority: this.filter.priority,
          page: this.filter.page
        }).then((function(_this) {
          return function(res) {
            var i, _i, _ref;
            _this.server_cron_logs = res.data.server_cron_logs;
            _this.filter.page = res.data.server_cron_logs.page;
            _this.page = res.data.server_cron_logs.page;
            _this.num_pages = res.data.server_cron_logs.num_pages;
            _this.page_nums = [];
            for (i = _i = 0, _ref = _this.num_pages; 0 <= _ref ? _i < _ref : _i > _ref; i = 0 <= _ref ? ++_i : --_i) {
              _this.page_nums.push(i + 1);
            }
            return _this.stopSpinner('paginating_server_cron_logs', true);
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      Admin_ServerCron_Ctrl_Logs.prototype.updateFilter = function() {
        return this.loadResults();
      };


      /*
      		 * Show the clear dlg
       */

      Admin_ServerCron_Ctrl_Logs.prototype.startClearAll = function() {
        var inst;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Server/server-cron-delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then((function(_this) {
          return function() {
            return _this.clearAll();
          };
        })(this));
      };


      /*
      		 * Actually do the clear
       */

      Admin_ServerCron_Ctrl_Logs.prototype.clearAll = function() {
        return this.Api.sendDelete('/server_cron/logs').success((function(_this) {
          return function() {
            return _this.server_cron_logs = null;
          };
        })(this));
      };


      /*
      		 *	Here we watching scope 'page' variable in order to load new page of results
       */

      Admin_ServerCron_Ctrl_Logs.prototype.initializeScopeWatching = function() {
        return this.$scope.$watch('LogsCtrl.page', (function(_this) {
          return function(newVal, oldVal) {
            if (parseInt(newVal) === parseInt(oldVal)) {
              return void 0;
            }
            if (isNaN(parseInt(newVal))) {
              return void 0;
            }
            return _this.changePageCallback();
          };
        })(this));
      };


      /*
      		 * This is executed after we chnaged the current page
       */

      Admin_ServerCron_Ctrl_Logs.prototype.changePageCallback = function() {
        this.filter.page = this.page;
        return this.loadResults();
      };


      /*
       	 *
       */

      Admin_ServerCron_Ctrl_Logs.prototype.goPrevPage = function() {
        return this.page--;
      };


      /*
      		 *
       */

      Admin_ServerCron_Ctrl_Logs.prototype.goNextPage = function() {
        return this.page++;
      };

      return Admin_ServerCron_Ctrl_Logs;

    })(Admin_Ctrl_Base);
    return Admin_ServerCron_Ctrl_Logs.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerCronLogs.js.map
