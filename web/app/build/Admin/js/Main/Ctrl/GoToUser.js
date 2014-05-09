(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_GoToUser;
    Admin_Main_Ctrl_GoToUser = (function(_super) {
      __extends(Admin_Main_Ctrl_GoToUser, _super);

      function Admin_Main_Ctrl_GoToUser() {
        return Admin_Main_Ctrl_GoToUser.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_GoToUser.CTRL_ID = 'Admin_Main_Ctrl_GoToUser';

      Admin_Main_Ctrl_GoToUser.prototype.init = function() {
        if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.reports) {
          return window.location.href = window.DP_BASE_URL;
        } else {
          return window.parent.DP_FRAME_OVERLAYS.user.open();
        }
      };

      return Admin_Main_Ctrl_GoToUser;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_GoToUser.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=GoToUser.js.map
