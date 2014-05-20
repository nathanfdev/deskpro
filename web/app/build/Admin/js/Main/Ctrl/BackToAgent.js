(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_BackToAgent;
    Admin_Main_Ctrl_BackToAgent = (function(_super) {
      __extends(Admin_Main_Ctrl_BackToAgent, _super);

      function Admin_Main_Ctrl_BackToAgent() {
        return Admin_Main_Ctrl_BackToAgent.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_BackToAgent.CTRL_ID = 'Admin_Main_Ctrl_BackToAgent';

      Admin_Main_Ctrl_BackToAgent.DEPS = ['$location'];

      Admin_Main_Ctrl_BackToAgent.prototype.init = function() {
        console.log("here");
        if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.admin) {
          return window.location.href = window.DP_BASE_URL + 'agent/';
        } else {
          return window.parent.DP_FRAME_OVERLAYS.admin.close();
        }
      };

      return Admin_Main_Ctrl_BackToAgent;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_BackToAgent.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=BackToAgent.js.map
