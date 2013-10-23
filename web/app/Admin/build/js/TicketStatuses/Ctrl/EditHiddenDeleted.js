(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditHiddenDeleted, _ref;
    Admin_TicketStatuses_Ctrl_EditHiddenDeleted = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditHiddenDeleted, _super);

      function Admin_TicketStatuses_Ctrl_EditHiddenDeleted() {
        _ref = Admin_TicketStatuses_Ctrl_EditHiddenDeleted.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketStatuses_Ctrl_EditHiddenDeleted.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenDeleted';

      Admin_TicketStatuses_Ctrl_EditHiddenDeleted.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditHiddenDeleted.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditHiddenDeleted.CTRL_TYPE = 'page';

      Admin_TicketStatuses_Ctrl_EditHiddenDeleted.prototype.init = function() {
        this.$scope.settings = {
          auto_purge_time: 604800
        };
      };

      Admin_TicketStatuses_Ctrl_EditHiddenDeleted.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet("/ticket_statuses/deleted").success(function(data) {
          return _this.$scope.settings.auto_purge_time = data.deleted_info.auto_purge_time;
        });
        return promise;
      };

      Admin_TicketStatuses_Ctrl_EditHiddenDeleted.prototype.saveSettings = function() {
        var promise,
          _this = this;
        this.startSpinner('saving_settings');
        promise = this.Api.sendPostJson('/ticket_statuses/deleted/settings', this.$scope.settings).then(function() {
          return _this.stopSpinner('saving_settings');
        });
        return promise;
      };

      return Admin_TicketStatuses_Ctrl_EditHiddenDeleted;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditHiddenDeleted.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EditHiddenDeleted.js.map
*/