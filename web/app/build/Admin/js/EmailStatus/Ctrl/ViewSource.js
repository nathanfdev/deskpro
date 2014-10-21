(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'moment'], function(Admin_Ctrl_Base, moment) {
    var Admin_EmailStatus_Ctrl_ViewSource;
    Admin_EmailStatus_Ctrl_ViewSource = (function(_super) {
      __extends(Admin_EmailStatus_Ctrl_ViewSource, _super);

      function Admin_EmailStatus_Ctrl_ViewSource() {
        return Admin_EmailStatus_Ctrl_ViewSource.__super__.constructor.apply(this, arguments);
      }

      Admin_EmailStatus_Ctrl_ViewSource.CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSource';

      Admin_EmailStatus_Ctrl_ViewSource.CTRL_AS = 'ViewSource';

      Admin_EmailStatus_Ctrl_ViewSource.DEPS = ['$state', '$modal', 'DpDateService'];

      Admin_EmailStatus_Ctrl_ViewSource.prototype.init = function() {
        this.sourceId = parseInt(this.$stateParams.id);
        this.$scope.ds = this.DpDateService;
      };

      Admin_EmailStatus_Ctrl_ViewSource.prototype.initialLoad = function() {
        return this.Api.sendGet("/email_status/sources/" + this.sourceId + "?with_raw=1").then((function(_this) {
          return function(res) {
            var k, v, _ref, _results;
            _ref = res.data;
            _results = [];
            for (k in _ref) {
              if (!__hasProp.call(_ref, k)) continue;
              v = _ref[k];
              if ('date_status' === k || 'date_created' === k) {
                v = _this.DpDateService.local(v);
                _results.push(_this[k] = v);
              } else {
                _results.push(void 0);
              }
            }
            return _results;
          };
        })(this));
      };

      Admin_EmailStatus_Ctrl_ViewSource.prototype["delete"] = function() {
        return this.Api.sendDelete("/email_status/sources/" + this.sourceId);
      };

      Admin_EmailStatus_Ctrl_ViewSource.prototype.reprocess = function() {
        return this.Api.sendPost("/email_status/sources/" + this.sourceId + "/reprocess");
      };

      Admin_EmailStatus_Ctrl_ViewSource.prototype.startDelete = function() {
        var doDelete;
        doDelete = (function(_this) {
          return function() {
            return _this["delete"]().then(function() {
              return _this.$state.go('tickets.ticket_accounts.emailsources');
            });
          };
        })(this);
        return this.$modal.open({
          templateUrl: this.getTemplatePath('EmailStatus/emailsource-delete-modal.html'),
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

      Admin_EmailStatus_Ctrl_ViewSource.prototype.startReprocess = function() {
        var doReprocess;
        doReprocess = (function(_this) {
          return function() {
            return _this.reprocess().then(function() {
              return _this.$state.go('tickets.ticket_accounts.goemailsourcesview', {
                id: _this.sourceId
              });
            });
          };
        })(this);
        return this.$modal.open({
          templateUrl: this.getTemplatePath('EmailStatus/emailsource-reprocess-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.doReprocess = function() {
                $scope.is_loading = true;
                return doReprocess().then(function() {
                  return $modalInstance.dismiss();
                });
              };
            }
          ]
        });
      };

      return Admin_EmailStatus_Ctrl_ViewSource;

    })(Admin_Ctrl_Base);
    return Admin_EmailStatus_Ctrl_ViewSource.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ViewSource.js.map
