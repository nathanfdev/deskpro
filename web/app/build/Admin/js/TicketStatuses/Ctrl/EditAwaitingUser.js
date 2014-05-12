(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditAwaitingUser;
    Admin_TicketStatuses_Ctrl_EditAwaitingUser = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditAwaitingUser, _super);

      function Admin_TicketStatuses_Ctrl_EditAwaitingUser() {
        return Admin_TicketStatuses_Ctrl_EditAwaitingUser.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketStatuses_Ctrl_EditAwaitingUser.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditAwaitingUser';

      Admin_TicketStatuses_Ctrl_EditAwaitingUser.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditAwaitingUser.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditAwaitingUser.prototype.init = function() {
        this.$scope.getCount = (function(_this) {
          return function() {
            var _ref;
            return (_ref = _this.$scope.$parent.TicketStatusesList) != null ? _ref.getStatusCount('awaiting_user') : void 0;
          };
        })(this);
      };

      return Admin_TicketStatuses_Ctrl_EditAwaitingUser;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditAwaitingUser.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditAwaitingUser.js.map
