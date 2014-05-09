(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_NewsSettings_Ctrl_NewsSettings;
    Admin_NewsSettings_Ctrl_NewsSettings = (function(_super) {
      __extends(Admin_NewsSettings_Ctrl_NewsSettings, _super);

      function Admin_NewsSettings_Ctrl_NewsSettings() {
        return Admin_NewsSettings_Ctrl_NewsSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_NewsSettings_Ctrl_NewsSettings.CTRL_ID = 'Admin_NewsSettings_Ctrl_NewsSettings';

      Admin_NewsSettings_Ctrl_NewsSettings.CTRL_AS = 'Ctrl';

      Admin_NewsSettings_Ctrl_NewsSettings.DEPS = [];


      /*
       	 *
       */

      Admin_NewsSettings_Ctrl_NewsSettings.prototype.init = function() {};


      /*
       	 *
       */

      Admin_NewsSettings_Ctrl_NewsSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/enable_settings/app_news').then((function(_this) {
          return function(res) {
            return _this.$scope.status = res.data.status;
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
      		 *
       */

      Admin_NewsSettings_Ctrl_NewsSettings.prototype.toggle = function() {
        var val;
        if (this.$scope.status) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/enable_settings/app_news/toggle/' + val);
      };

      return Admin_NewsSettings_Ctrl_NewsSettings;

    })(Admin_Ctrl_Base);
    return Admin_NewsSettings_Ctrl_NewsSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=NewsSettings.js.map
