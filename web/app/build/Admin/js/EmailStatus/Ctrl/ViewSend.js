(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'moment'], function(Admin_Ctrl_Base, moment) {
    var Admin_EmailStatus_Ctrl_ViewSend;
    Admin_EmailStatus_Ctrl_ViewSend = (function(_super) {
      __extends(Admin_EmailStatus_Ctrl_ViewSend, _super);

      function Admin_EmailStatus_Ctrl_ViewSend() {
        return Admin_EmailStatus_Ctrl_ViewSend.__super__.constructor.apply(this, arguments);
      }

      Admin_EmailStatus_Ctrl_ViewSend.CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSend';

      Admin_EmailStatus_Ctrl_ViewSend.CTRL_AS = 'ViewSource';

      Admin_EmailStatus_Ctrl_ViewSend.DEPS = ['$state', '$modal'];

      Admin_EmailStatus_Ctrl_ViewSend.prototype.init = function() {
        this.sendmailId = parseInt(this.$stateParams.id);
      };

      Admin_EmailStatus_Ctrl_ViewSend.prototype.initialLoad = function() {
        return this.Api.sendGet("/email_status/sendmail/" + this.sendmailId + "?with_raw=1").then((function(_this) {
          return function(res) {
            _this.sendmail = res.data.sendmail;
            _this.sendmail_raw = res.data.sendmail_raw;
            return _this.log = res.data.sendmail_log;
          };
        })(this));
      };

      Admin_EmailStatus_Ctrl_ViewSend.prototype["delete"] = function() {
        return this.Api.sendDelete("/email_status/sendmail/" + this.sendmailId);
      };

      Admin_EmailStatus_Ctrl_ViewSend.prototype.resend = function() {
        return this.Api.sendPost("/email_status/sendmail/" + this.sendmailId + "/resend");
      };

      Admin_EmailStatus_Ctrl_ViewSend.prototype.startDelete = function() {
        var doDelete;
        doDelete = (function(_this) {
          return function() {
            return _this["delete"]().then(function() {
              return _this.$state.go('tickets.ticket_accounts.sendmailqueue');
            });
          };
        })(this);
        return this.$modal.open({
          templateUrl: this.getTemplatePath('EmailStatus/sendmail-delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.doDelete = function() {
                $scope.is_loading = true;
                return doDelete().then(function() {
                  return $modalInstance.dismiss();
                });
              };
            }
          ]
        });
      };

      Admin_EmailStatus_Ctrl_ViewSend.prototype.startResend = function() {
        var doResend;
        doResend = (function(_this) {
          return function() {
            return _this.resend().then(function() {
              return _this.$state.go('tickets.ticket_accounts.gosendmailview', {
                id: _this.sendmailId
              });
            });
          };
        })(this);
        return this.$modal.open({
          templateUrl: this.getTemplatePath('EmailStatus/sendmail-resend-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.doResend = function() {
                $scope.is_loading = true;
                return doResend().then(function() {
                  return $modalInstance.dismiss();
                });
              };
            }
          ]
        });
      };

      return Admin_EmailStatus_Ctrl_ViewSend;

    })(Admin_Ctrl_Base);
    return Admin_EmailStatus_Ctrl_ViewSend.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ViewSend.js.map
