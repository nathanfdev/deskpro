(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_KbSettings_Ctrl_KbSettings;
    Admin_KbSettings_Ctrl_KbSettings = (function(_super) {
      __extends(Admin_KbSettings_Ctrl_KbSettings, _super);

      function Admin_KbSettings_Ctrl_KbSettings() {
        return Admin_KbSettings_Ctrl_KbSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_KbSettings_Ctrl_KbSettings.CTRL_ID = 'Admin_KbSettings_Ctrl_KbSettings';

      Admin_KbSettings_Ctrl_KbSettings.CTRL_AS = 'Ctrl';

      Admin_KbSettings_Ctrl_KbSettings.DEPS = [];


      /*
       	 *
       */

      Admin_KbSettings_Ctrl_KbSettings.prototype.init = function() {};


      /*
       	 *
       */

      Admin_KbSettings_Ctrl_KbSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/enable_settings/app_kb').then((function(_this) {
          return function(res) {
            return _this.$scope.status = res.data.status;
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
      		 *
       */

      Admin_KbSettings_Ctrl_KbSettings.prototype.toggle = function() {
        var val;
        if (this.$scope.status) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/enable_settings/app_kb/toggle/' + val);
      };

      return Admin_KbSettings_Ctrl_KbSettings;

    })(Admin_Ctrl_Base);
    return Admin_KbSettings_Ctrl_KbSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=KbSettings.js.map
