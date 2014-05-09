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


      /*
       	 *
       */

      Admin_DownloadsSettings_Ctrl_DownloadsSettings.prototype.init = function() {};


      /*
       	 *
       */

      Admin_DownloadsSettings_Ctrl_DownloadsSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/enable_settings/app_downloads').then((function(_this) {
          return function(res) {
            return _this.$scope.status = res.data.status;
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
      		 *
       */

      Admin_DownloadsSettings_Ctrl_DownloadsSettings.prototype.toggle = function() {
        var val;
        if (this.$scope.status) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/enable_settings/app_downloads/toggle/' + val);
      };

      return Admin_DownloadsSettings_Ctrl_DownloadsSettings;

    })(Admin_Ctrl_Base);
    return Admin_DownloadsSettings_Ctrl_DownloadsSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=DownloadsSettings.js.map
