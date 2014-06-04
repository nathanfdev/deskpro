(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerErrorLogs_Ctrl_ServerErrorLogs;
    Admin_ServerErrorLogs_Ctrl_ServerErrorLogs = (function(_super) {
      __extends(Admin_ServerErrorLogs_Ctrl_ServerErrorLogs, _super);

      function Admin_ServerErrorLogs_Ctrl_ServerErrorLogs() {
        return Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.CTRL_ID = 'Admin_ServerErrorLogs_Ctrl_ServerErrorLogs';

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.CTRL_AS = 'ServerErrorLogs';

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.DEPS = [];

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.prototype.init = function() {
        this.$scope.server_error_logs = null;
        return this.$scope.logs_size = 0;
      };

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_error_logs').then((function(_this) {
          return function(res) {
            var _ref;
            _this.$scope.server_error_logs = res.data.server_error_logs;
            if ((_ref = _this.$scope.server_error_logs) != null ? _ref.logs : void 0) {
              _this.$scope.server_error_logs.logs = _this.$scope.server_error_logs.logs.reverse();
            }
            return _this.$scope.logs_size = _.size(_this.$scope.server_error_logs.logs);
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
      		 * Show the clear dlg
       */

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.prototype.startClearAll = function() {
        var inst;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Server/server-error-logs-delete-modal.html'),
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

      Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.prototype.clearAll = function() {
        return this.Api.sendDelete('/server_error_logs/').success((function(_this) {
          return function() {
            return _this.$scope.server_error_logs.logs = null;
          };
        })(this));
      };

      return Admin_ServerErrorLogs_Ctrl_ServerErrorLogs;

    })(Admin_Ctrl_Base);
    return Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerErrorLogs.js.map
