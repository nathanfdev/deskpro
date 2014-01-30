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

      AdminUpgrade_Main_Ctrl_UpgradeHome.DEPS = ['$sce', '$location'];

      AdminUpgrade_Main_Ctrl_UpgradeHome.prototype.init = function() {
        var _this = this;
        this.$scope.opt = {
          db_backup: true,
          file_backup: false,
          time_type: 'now',
          delay: 15,
          user_message: ''
        };
        this.$scope.card_loaded = false;
        this.Api.sendDataGet({
          versionInfo: '/dp_license/version-info',
          latestVersion: '/dp_license/latest-version-info',
          updateStatus: '/server/updates/auto'
        }).then(function(result) {
          var _ref1;
          if (result.data.updateStatus.is_scheduled) {
            _this.$location.path('/progress');
            return;
          }
          _this.$scope.card_loaded = true;
          _this.$scope.version_info = result.data.versionInfo;
          _this.$scope.release_notes_url = _this.$sce.trustAsResourceUrl("https://www.deskpro.com/members/versions/changelog/" + _this.$scope.version_info.build_num_base);
          if (((_ref1 = result.data.latestVersion) != null ? _ref1.version_info : void 0) == null) {
            return _this.$scope.latest_version = null;
          } else {
            return _this.$scope.latest_version = result.data.latestVersion.version_info;
          }
        });
      };

      AdminUpgrade_Main_Ctrl_UpgradeHome.prototype.startUpgrade = function() {
        var formData, opt,
          _this = this;
        this.$scope.is_loading = true;
        opt = this.$scope.opt;
        formData = {
          backup_db: opt.db_backup ? 1 : 0,
          backup_files: opt.file_backup ? 1 : 0,
          minutes: opt.time_type === 'now' ? 0 : parseInt(opt.delay) || 0,
          user_message: opt.user_message
        };
        return this.Api.sendPutJson('/server/updates/auto', formData).success(function() {
          return _this.$location.path('/progress');
        });
      };

      return AdminUpgrade_Main_Ctrl_UpgradeHome;

    })(UpgradeBase);
    return AdminUpgrade_Main_Ctrl_UpgradeHome.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=UpgradeHome.js.map
*/