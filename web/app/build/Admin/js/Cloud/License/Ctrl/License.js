(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Cloud_License_Ctrl_License;
    Admin_Cloud_License_Ctrl_License = (function(_super) {
      __extends(Admin_Cloud_License_Ctrl_License, _super);

      function Admin_Cloud_License_Ctrl_License() {
        return Admin_Cloud_License_Ctrl_License.__super__.constructor.apply(this, arguments);
      }

      Admin_Cloud_License_Ctrl_License.CTRL_ID = 'Admin_Cloud_License_Ctrl_License';

      Admin_Cloud_License_Ctrl_License.CTRL_AS = 'Ctrl';

      Admin_Cloud_License_Ctrl_License.DEPS = ['$window'];

      Admin_Cloud_License_Ctrl_License.prototype.init = function() {
        return this.$scope.iframe_loading = true;
      };

      Admin_Cloud_License_Ctrl_License.prototype.initialLoad = function() {
        this.Api.sendGet('/dp_license/cloud/billing-login-token').then((function(_this) {
          return function(result) {
            console.log(result.data);
            _this.$scope.iframe_loading = false;
            return _this.$scope.iframe_code = '<iframe src="' + result.data.ma_url + '" frameborder="0"></iframe>';
          };
        })(this));
        return null;
      };

      return Admin_Cloud_License_Ctrl_License;

    })(Admin_Ctrl_Base);
    return Admin_Cloud_License_Ctrl_License.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=License.js.map
