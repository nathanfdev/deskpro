(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_List;
    Admin_TicketStatuses_Ctrl_List = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_List, _super);

      function Admin_TicketStatuses_Ctrl_List() {
        return Admin_TicketStatuses_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketStatuses_Ctrl_List.CTRL_ID = 'Admin_TicketStatuses_Ctrl_List';

      Admin_TicketStatuses_Ctrl_List.CTRL_AS = 'TicketStatusesList';

      Admin_TicketStatuses_Ctrl_List.DEPS = [];

      Admin_TicketStatuses_Ctrl_List.prototype.init = function() {
        this.stats = {};
      };

      Admin_TicketStatuses_Ctrl_List.prototype.initialLoad = function() {
        return this.Api.sendGet('/ticket_statuses/stats').then((function(_this) {
          return function(res) {
            console.log(res);
            return _this.stats = res.data.status_stats;
          };
        })(this));
      };

      Admin_TicketStatuses_Ctrl_List.prototype.getStatusCount = function(status) {
        return this.stats[status] || 0;
      };

      return Admin_TicketStatuses_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
