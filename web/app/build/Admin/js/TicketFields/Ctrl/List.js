(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketFields_Ctrl_List;
    Admin_TicketFields_Ctrl_List = (function(_super) {
      __extends(Admin_TicketFields_Ctrl_List, _super);

      function Admin_TicketFields_Ctrl_List() {
        return Admin_TicketFields_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketFields_Ctrl_List.CTRL_ID = 'Admin_TicketFields_Ctrl_List';

      Admin_TicketFields_Ctrl_List.CTRL_AS = 'TicketFieldsList';

      Admin_TicketFields_Ctrl_List.DEPS = [];

      Admin_TicketFields_Ctrl_List.prototype.init = function() {
        this.ticket_fields = this.DataService.get('TicketFields');
        this.custom_fields = [];
        this.field_enabled = {};
      };

      Admin_TicketFields_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.ticket_fields.loadList();
        promise.then((function(_this) {
          return function(list) {
            _this.custom_fields = list;
            return _this.field_enabled = _this.ticket_fields.field_enabled;
          };
        })(this));
        return promise;
      };

      return Admin_TicketFields_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketFields_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
