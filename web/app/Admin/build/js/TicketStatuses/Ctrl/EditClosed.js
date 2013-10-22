(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditClosed, _ref;
    Admin_TicketStatuses_Ctrl_EditClosed = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditClosed, _super);

      function Admin_TicketStatuses_Ctrl_EditClosed() {
        _ref = Admin_TicketStatuses_Ctrl_EditClosed.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketStatuses_Ctrl_EditClosed.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditClosed';

      Admin_TicketStatuses_Ctrl_EditClosed.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditClosed.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditClosed.CTRL_TYPE = 'page';

      Admin_TicketStatuses_Ctrl_EditClosed.prototype.init = function() {
        this.auto_purge_time = 604800;
      };

      Admin_TicketStatuses_Ctrl_EditClosed.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet("/ticket_statuses/closed").success(function(data) {
          _this.enabled = data.closed_info.enabled;
          return _this.auto_archive_time = data.closed_info.auto_archive_time;
        });
        return promise;
      };

      return Admin_TicketStatuses_Ctrl_EditClosed;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditClosed.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EditClosed.js.map
*/