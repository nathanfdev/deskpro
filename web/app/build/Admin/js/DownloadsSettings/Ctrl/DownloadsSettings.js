(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_DownloadsSettings_Ctrl_DownloadsSettings;
    Admin_DownloadsSettings_Ctrl_DownloadsSettings = (function(_super) {
      __extends(Admin_DownloadsSettings_Ctrl_DownloadsSettings, _super);

      function Admin_DownloadsSettings_Ctrl_DownloadsSettings() {
        return Admin_DownloadsSettings_Ctrl_DownloadsSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_DownloadsSettings_Ctrl_DownloadsSettings.CTRL_ID = 'Admin_DownloadsSettings_Ctrl_DownloadsSettings';

      Admin_DownloadsSettings_Ctrl_DownloadsSettings.CTRL_AS = 'Ctrl';

      Admin_DownloadsSettings_Ctrl_DownloadsSettings.DEPS = [];

      Admin_DownloadsSettings_Ctrl_DownloadsSettings.prototype.initialLoad = function() {
        return this.Api.sendGet('/settings/portal/downloads').then((function(_this) {
          return function(res) {
            return _this.$scope.settings = res.data.settings;
          };
        })(this));
      };

      Admin_DownloadsSettings_Ctrl_DownloadsSettings.prototype.save = function() {
        var postData;
        postData = {
          settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return this.Api.sendPostJson('/settings/portal/downloads', postData).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving');
          };
        })(this));
      };

      return Admin_DownloadsSettings_Ctrl_DownloadsSettings;

    })(Admin_Ctrl_Base);
    return Admin_DownloadsSettings_Ctrl_DownloadsSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=DownloadsSettings.js.map
