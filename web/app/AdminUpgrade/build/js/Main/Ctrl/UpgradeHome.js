(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminUpgrade/Main/Ctrl/UpgradeBase'], function(UpgradeBase) {
    var AdminUpgrade_Main_Ctrl_UpgradeHome, _ref;
    AdminUpgrade_Main_Ctrl_UpgradeHome = (function(_super) {
      __extends(AdminUpgrade_Main_Ctrl_UpgradeHome, _super);

      function AdminUpgrade_Main_Ctrl_UpgradeHome() {
        _ref = AdminUpgrade_Main_Ctrl_UpgradeHome.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      AdminUpgrade_Main_Ctrl_UpgradeHome.CTRL_ID = 'AdminUpgrade_Main_Ctrl_UpgradeHome';

      AdminUpgrade_Main_Ctrl_UpgradeHome.CTRL_AS = 'Home';

      AdminUpgrade_Main_Ctrl_UpgradeHome.DEPS = [];

      AdminUpgrade_Main_Ctrl_UpgradeHome.prototype.init = function() {
        var _this = this;
        this.$scope.card_loaded = false;
        this.Api.sendDataGet({
          versionInfo: '/dp_license/version-info',
          latestVersion: '/dp_license/latest-version-info',
          updateStatus: '/server/updates/auto'
        }).then(function(result) {
          var _ref1;
          _this.$scope.card_loaded = true;
          _this.$scope.version_info = result.data.versionInfo;
          if (((_ref1 = result.data.latestVersion) != null ? _ref1.version_info : void 0) == null) {
            return _this.$scope.latest_version = null;
          } else {
            return _this.$scope.latest_version = result.data.latestVersion.version_info;
          }
        });
      };

      AdminUpgrade_Main_Ctrl_UpgradeHome.prototype.startUpgrade = function() {
        return this.$scope.is_loading = true;
      };

      return AdminUpgrade_Main_Ctrl_UpgradeHome;

    })(UpgradeBase);
    return AdminUpgrade_Main_Ctrl_UpgradeHome.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=UpgradeHome.js.map
*/