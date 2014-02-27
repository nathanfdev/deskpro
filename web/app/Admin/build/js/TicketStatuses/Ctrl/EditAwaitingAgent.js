(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditAwaitingAgent;
    Admin_TicketStatuses_Ctrl_EditAwaitingAgent = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditAwaitingAgent, _super);

      function Admin_TicketStatuses_Ctrl_EditAwaitingAgent() {
        return Admin_TicketStatuses_Ctrl_EditAwaitingAgent.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketStatuses_Ctrl_EditAwaitingAgent.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditAwaitingAgent';

      Admin_TicketStatuses_Ctrl_EditAwaitingAgent.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditAwaitingAgent.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditAwaitingAgent.prototype.init = function() {};

      return Admin_TicketStatuses_Ctrl_EditAwaitingAgent;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditAwaitingAgent.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditAwaitingAgent.js.map
