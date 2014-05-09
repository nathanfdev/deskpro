(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditResolved;
    Admin_TicketStatuses_Ctrl_EditResolved = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditResolved, _super);

      function Admin_TicketStatuses_Ctrl_EditResolved() {
        return Admin_TicketStatuses_Ctrl_EditResolved.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketStatuses_Ctrl_EditResolved.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditResolved';

      Admin_TicketStatuses_Ctrl_EditResolved.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditResolved.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditResolved.prototype.init = function() {};

      return Admin_TicketStatuses_Ctrl_EditResolved;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditResolved.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditResolved.js.map
