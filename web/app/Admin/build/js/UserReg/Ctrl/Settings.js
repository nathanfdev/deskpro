(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_UserReg_Ctrl_Settings, _ref;
    Admin_UserReg_Ctrl_Settings = (function(_super) {
      __extends(Admin_UserReg_Ctrl_Settings, _super);

      function Admin_UserReg_Ctrl_Settings() {
        _ref = Admin_UserReg_Ctrl_Settings.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_UserReg_Ctrl_Settings.CTRL_ID = 'Admin_UserReg_Ctrl_Settings';

      Admin_UserReg_Ctrl_Settings.CTRL_AS = 'PageCtrl';

      Admin_UserReg_Ctrl_Settings.DEPS = [];

      Admin_UserReg_Ctrl_Settings.prototype.init = function() {
        return this.settings = null;
      };

      Admin_UserReg_Ctrl_Settings.prototype.initialLoad = function() {};

      Admin_UserReg_Ctrl_Settings.prototype.isDirtyState = function() {
        if (!this.settings) {
          return false;
        }
        if (!angular.equals(this.settings, this.$scope.settings)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_UserReg_Ctrl_Settings.prototype.save = function() {
        var postData, promise,
          _this = this;
        postData = {};
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/registration_settings', postData).success(function() {
          _this.settings = angular.copy(_this.$scope.settings);
          return _this.stopSpinner('saving').then(function() {
            return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
          });
        }).error(function(info, code) {
          _this.stopSpinner('saving', true);
          return _this.applyErrorResponseToView(info);
        });
      };

      return Admin_UserReg_Ctrl_Settings;

    })(Admin_Ctrl_Base);
    return Admin_UserReg_Ctrl_Settings.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Settings.js.map
*/