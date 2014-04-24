(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_GoToReports;
    Admin_Main_Ctrl_GoToReports = (function(_super) {
      __extends(Admin_Main_Ctrl_GoToReports, _super);

      function Admin_Main_Ctrl_GoToReports() {
        return Admin_Main_Ctrl_GoToReports.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_GoToReports.CTRL_ID = 'Admin_Main_Ctrl_GoToReports';

      Admin_Main_Ctrl_GoToReports.prototype.init = function() {
        if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.reports) {
          return window.location.href = window.DP_BASE_URL + 'reports/';
        } else {
          return window.parent.DP_FRAME_OVERLAYS.reports.open();
        }
      };

      return Admin_Main_Ctrl_GoToReports;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_GoToReports.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=GoToReports.js.map
