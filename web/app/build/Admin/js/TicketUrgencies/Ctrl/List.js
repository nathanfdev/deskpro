(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketUrgencies_Ctrl_List;
    Admin_TicketUrgencies_Ctrl_List = (function(_super) {
      __extends(Admin_TicketUrgencies_Ctrl_List, _super);

      function Admin_TicketUrgencies_Ctrl_List() {
        return Admin_TicketUrgencies_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketUrgencies_Ctrl_List.CTRL_ID = 'Admin_TicketUrgencies_Ctrl_List';

      Admin_TicketUrgencies_Ctrl_List.CTRL_AS = 'TicketUrgenciesList';

      Admin_TicketUrgencies_Ctrl_List.DEPS = [];

      Admin_TicketUrgencies_Ctrl_List.prototype.init = function() {
        this.urgency_counts = {};
        this.urgencies = [];
      };

      Admin_TicketUrgencies_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/ticket_urgencies").success((function(_this) {
          return function(data) {
            var num, _i, _results;
            _this.urgency_counts = data.urgency_counts;
            _results = [];
            for (num = _i = 1; _i <= 10; num = ++_i) {
              _results.push(_this.urgencies.push({
                num: num,
                ticket_count: _this.urgency_counts[num] ? _this.urgency_counts[num] : 0
              }));
            }
            return _results;
          };
        })(this));
        return promise;
      };

      return Admin_TicketUrgencies_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketUrgencies_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
