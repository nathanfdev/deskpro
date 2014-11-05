(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditArchived;
    Admin_TicketStatuses_Ctrl_EditArchived = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditArchived, _super);

      function Admin_TicketStatuses_Ctrl_EditArchived() {
        return Admin_TicketStatuses_Ctrl_EditArchived.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketStatuses_Ctrl_EditArchived.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditArchived';

      Admin_TicketStatuses_Ctrl_EditArchived.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditArchived.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditArchived.prototype.init = function() {
        this.$scope.getCount = (function(_this) {
          return function() {
            var _ref;
            return (_ref = _this.$scope.$parent.TicketStatusesList) != null ? _ref.getStatusCount('archived') : void 0;
          };
        })(this);
        this.$scope.settings = {
          enabled: false,
          auto_archive_time: 2419200
        };
      };

      Admin_TicketStatuses_Ctrl_EditArchived.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/ticket_statuses/archived").success((function(_this) {
          return function(data) {
            _this.$scope.settings.enabled = data.archived_info.enabled;
            return _this.$scope.settings.auto_archive_time = parseInt(data.archived_info.auto_archive_time);
          };
        })(this));
        return promise;
      };

      Admin_TicketStatuses_Ctrl_EditArchived.prototype.saveSettings = function() {
        var promise;
        this.startSpinner('saving_settings');
        promise = this.Api.sendPostJson('/ticket_statuses/archived/settings', this.$scope.settings).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving_settings');
          };
        })(this));
        return promise;
      };

      Admin_TicketStatuses_Ctrl_EditArchived.prototype.resetSearchTables = function() {
        this.startSpinner('is_resetting');
        return this.Api.sendPost('/ticket_statuses/archived/reset-search-tables').then((function(_this) {
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

      return Admin_TicketStatuses_Ctrl_EditArchived;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditArchived.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditArchived.js.map
