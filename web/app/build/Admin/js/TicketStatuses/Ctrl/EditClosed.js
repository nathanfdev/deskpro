(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditClosed;
    Admin_TicketStatuses_Ctrl_EditClosed = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditClosed, _super);

      function Admin_TicketStatuses_Ctrl_EditClosed() {
        return Admin_TicketStatuses_Ctrl_EditClosed.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketStatuses_Ctrl_EditClosed.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditClosed';

      Admin_TicketStatuses_Ctrl_EditClosed.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditClosed.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditClosed.prototype.init = function() {
        this.$scope.getCount = (function(_this) {
          return function() {
            var _ref;
            return (_ref = _this.$scope.$parent.TicketStatusesList) != null ? _ref.getStatusCount('closed') : void 0;
          };
        })(this);
        this.$scope.settings = {
          enabled: false,
          auto_archive_time: 2419200
        };
      };

      Admin_TicketStatuses_Ctrl_EditClosed.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/ticket_statuses/closed").success((function(_this) {
          return function(data) {
            _this.$scope.settings.enabled = data.closed_info.enabled;
            return _this.$scope.settings.auto_archive_time = parseInt(data.closed_info.auto_archive_time);
          };
        })(this));
        return promise;
      };

      Admin_TicketStatuses_Ctrl_EditClosed.prototype.saveSettings = function() {
        var promise;
        this.startSpinner('saving_settings');
        promise = this.Api.sendPostJson('/ticket_statuses/closed/settings', this.$scope.settings).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving_settings');
          };
        })(this));
        return promise;
      };

      Admin_TicketStatuses_Ctrl_EditClosed.prototype.resetSearchTables = function() {
        this.startSpinner('is_resetting');
        return this.Api.sendPost('/ticket_statuses/closed/reset-search-tables').then((function(_this) {
          return function() {
            _this.$scope.reset_done = true;
            return _this.stopSpinner('is_resetting');
          };
        })(this), (function(_this) {
          return function() {
            return _this.stopSpinner('is_resetting');
          };
        })(this));
      };

      return Admin_TicketStatuses_Ctrl_EditClosed;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditClosed.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditClosed.js.map
