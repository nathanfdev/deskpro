(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerReportFile_Ctrl_ServerReportFile, _ref;
    Admin_ServerReportFile_Ctrl_ServerReportFile = (function(_super) {
      __extends(Admin_ServerReportFile_Ctrl_ServerReportFile, _super);

      function Admin_ServerReportFile_Ctrl_ServerReportFile() {
        _ref = Admin_ServerReportFile_Ctrl_ServerReportFile.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ServerReportFile_Ctrl_ServerReportFile.CTRL_ID = 'Admin_ServerReportFile_Ctrl_ServerReportFile';

      Admin_ServerReportFile_Ctrl_ServerReportFile.CTRL_AS = 'Ctrl';

      Admin_ServerReportFile_Ctrl_ServerReportFile.DEPS = [];

      Admin_ServerReportFile_Ctrl_ServerReportFile.prototype.init = function() {
        return this.report_link = window.DP_BASE_API_URL + '/server_report_file?API-TOKEN=' + window.DP_API_TOKEN;
      };

      return Admin_ServerReportFile_Ctrl_ServerReportFile;

    })(Admin_Ctrl_Base);
    return Admin_ServerReportFile_Ctrl_ServerReportFile.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ServerReportFile.js.map
*/