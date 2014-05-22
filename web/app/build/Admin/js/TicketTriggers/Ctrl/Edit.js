(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Arrays', 'Admin/Main/Ctrl/Base', 'Admin/TicketTriggers/TriggerEditFormMapper', 'Admin/TicketTriggers/Ctrl/EditBase'], function(Arrays, Admin_Ctrl_Base, TriggerEditFormMapper, Admin_TicketTriggers_Ctrl_EditBase) {
    var Admin_TicketTriggers_Ctrl_Edit;
    Admin_TicketTriggers_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketTriggers_Ctrl_Edit, _super);

      function Admin_TicketTriggers_Ctrl_Edit() {
        return Admin_TicketTriggers_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketTriggers_Ctrl_Edit.CTRL_ID = 'Admin_TicketTriggers_Ctrl_Edit';

      Admin_TicketTriggers_Ctrl_Edit.CTRL_AS = 'TicketTriggersEdit';

      Admin_TicketTriggers_Ctrl_Edit.DEPS = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];

      return Admin_TicketTriggers_Ctrl_Edit;

    })(Admin_TicketTriggers_Ctrl_EditBase);
    return Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
