(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Cloud_Settings_Ctrl_CustomDomain;
    Admin_Cloud_Settings_Ctrl_CustomDomain = (function(_super) {
      __extends(Admin_Cloud_Settings_Ctrl_CustomDomain, _super);

      function Admin_Cloud_Settings_Ctrl_CustomDomain() {
        return Admin_Cloud_Settings_Ctrl_CustomDomain.__super__.constructor.apply(this, arguments);
      }

      Admin_Cloud_Settings_Ctrl_CustomDomain.CTRL_ID = 'Admin_Cloud_Settings_Ctrl_CustomDomain';

      Admin_Cloud_Settings_Ctrl_CustomDomain.CTRL_AS = 'Ctrl';

      Admin_Cloud_Settings_Ctrl_CustomDomain.prototype.initialLoad = function() {
        return this.Api.sendGet('/settings/cloud/url-settings').then((function(_this) {
          return function(res) {
            return _this.$scope.form = res.data.settings;
          };
        })(this));
      };

      Admin_Cloud_Settings_Ctrl_CustomDomain.prototype.save = function() {
        var postData;
        this.$scope.form_error = null;
        this.startSpinner('saving');
        postData = {
          settings: this.$scope.form
        };
        return this.Api.sendPostJson('/settings/cloud/url-settings', postData).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this), (function(_this) {
          return function(res) {
            var _ref;
            _this.stopSpinner('saving', true);
            if (((_ref = res.data) != null ? _ref.error_code : void 0) != null) {
              return _this.$scope.form_error = res.data.error_code;
            } else {
              return _this.$scope.form_error = 'server_error';
            }
          };
        })(this));
      };

      return Admin_Cloud_Settings_Ctrl_CustomDomain;

    })(Admin_Ctrl_Base);
    return Admin_Cloud_Settings_Ctrl_CustomDomain.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=CustomDomain.js.map
