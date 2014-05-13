(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackSettings_Ctrl_FeedbackSettings;
    Admin_FeedbackSettings_Ctrl_FeedbackSettings = (function(_super) {
      __extends(Admin_FeedbackSettings_Ctrl_FeedbackSettings, _super);

      function Admin_FeedbackSettings_Ctrl_FeedbackSettings() {
        return Admin_FeedbackSettings_Ctrl_FeedbackSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_FeedbackSettings_Ctrl_FeedbackSettings.CTRL_ID = 'Admin_FeedbackSettings_Ctrl_FeedbackSettings';

      Admin_FeedbackSettings_Ctrl_FeedbackSettings.CTRL_AS = 'Ctrl';

      Admin_FeedbackSettings_Ctrl_FeedbackSettings.DEPS = [];

      Admin_FeedbackSettings_Ctrl_FeedbackSettings.prototype.initialLoad = function() {
        return this.Api.sendGet('/settings/portal/feedback').then((function(_this) {
          return function(res) {
            return _this.$scope.settings = res.data.settings;
          };
        })(this));
      };

      Admin_FeedbackSettings_Ctrl_FeedbackSettings.prototype.save = function() {
        var postData;
        postData = {
          settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return this.Api.sendPostJson('/settings/portal/feedback', postData).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving');
          };
        })(this));
      };

      return Admin_FeedbackSettings_Ctrl_FeedbackSettings;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackSettings_Ctrl_FeedbackSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=FeedbackSettings.js.map
