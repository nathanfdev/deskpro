(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base'], function(Reports_Main_Ctrl_Bare) {
    var Reports_Main_Ctrl_BackToAgent;
    Reports_Main_Ctrl_BackToAgent = (function(_super) {
      __extends(Reports_Main_Ctrl_BackToAgent, _super);

      function Reports_Main_Ctrl_BackToAgent() {
        return Reports_Main_Ctrl_BackToAgent.__super__.constructor.apply(this, arguments);
      }

      Reports_Main_Ctrl_BackToAgent.CTRL_ID = 'Reports_Main_Ctrl_BackToAgent';

      Reports_Main_Ctrl_BackToAgent.DEPS = ['$location'];

      Reports_Main_Ctrl_BackToAgent.prototype.init = function() {
        if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.reports) {
          return window.location.href = window.DP_BASE_URL + 'agent/';
        } else {
          return window.parent.DP_FRAME_OVERLAYS.reports.close();
        }
      };

      return Reports_Main_Ctrl_BackToAgent;

    })(Reports_Main_Ctrl_Bare);
    return Reports_Main_Ctrl_BackToAgent.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=BackToAgent.js.map
