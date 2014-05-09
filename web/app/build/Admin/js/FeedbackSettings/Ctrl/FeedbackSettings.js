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


      /*
       	 *
       */

      Admin_FeedbackSettings_Ctrl_FeedbackSettings.prototype.init = function() {};


      /*
       	 *
       */

      Admin_FeedbackSettings_Ctrl_FeedbackSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/enable_settings/app_feedback').then((function(_this) {
          return function(res) {
            return _this.$scope.status = res.data.status;
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
      		 *
       */

      Admin_FeedbackSettings_Ctrl_FeedbackSettings.prototype.toggle = function() {
        var val;
        if (this.$scope.status) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/enable_settings/app_feedback/toggle/' + val);
      };

      return Admin_FeedbackSettings_Ctrl_FeedbackSettings;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackSettings_Ctrl_FeedbackSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=FeedbackSettings.js.map
