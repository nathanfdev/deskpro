(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditHiddenSpam;
    Admin_TicketStatuses_Ctrl_EditHiddenSpam = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditHiddenSpam, _super);

      function Admin_TicketStatuses_Ctrl_EditHiddenSpam() {
        return Admin_TicketStatuses_Ctrl_EditHiddenSpam.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenSpam';

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.prototype.init = function() {
        this.$scope.getCount = (function(_this) {
          return function() {
            var _ref;
            return (_ref = _this.$scope.$parent.TicketStatusesList) != null ? _ref.getStatusCount('hidden_spam') : void 0;
          };
        })(this);
        this.$scope.settings = {
          auto_purge_time: 604800
        };
      };

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/ticket_statuses/spam").success((function(_this) {
          return function(data) {
            return _this.$scope.settings.auto_purge_time = data.spam_info.auto_purge_time;
          };
        })(this));
        return promise;
      };

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.prototype.saveSettings = function() {
        var promise;
        this.startSpinner('saving_settings');
        promise = this.Api.sendPostJson('/ticket_statuses/spam/settings', this.$scope.settings).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving_settings');
          };
        })(this));
        return promise;
      };

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.prototype.startPurge = function() {
        var inst;
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketStatuses/modal-purge-spam.html'),
          controller: [
            '$scope', '$modalInstance', 'Api', (function(_this) {
              return function($scope, $modalInstance, Api) {
                var purgeNow;
                $scope.dismiss = function() {
                  return $modalInstance.dismiss();
                };
                $scope.confirm = function() {
                  return purgeNow();
                };
                return purgeNow = function() {
                  $scope.is_loading = true;
                  return Api.sendDelete('/ticket_statuses/spam/purge').success(function(data) {
                    $scope.is_done = true;
                    return $scope.count = data.count;
                  }).then(function() {
                    return $scope.is_loading = false;
                  });
                };
              };
            })(this)
          ]
        });
      };

      return Admin_TicketStatuses_Ctrl_EditHiddenSpam;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditHiddenSpam.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditHiddenSpam.js.map
