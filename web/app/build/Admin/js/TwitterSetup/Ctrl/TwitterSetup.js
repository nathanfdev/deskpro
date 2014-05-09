(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_TwitterSetup_Ctrl_TwitterSetup;
    Admin_TwitterSetup_Ctrl_TwitterSetup = (function(_super) {
      __extends(Admin_TwitterSetup_Ctrl_TwitterSetup, _super);

      function Admin_TwitterSetup_Ctrl_TwitterSetup() {
        return Admin_TwitterSetup_Ctrl_TwitterSetup.__super__.constructor.apply(this, arguments);
      }

      Admin_TwitterSetup_Ctrl_TwitterSetup.CTRL_ID = 'Admin_TwitterSetup_Ctrl_TwitterSetup';

      Admin_TwitterSetup_Ctrl_TwitterSetup.CTRL_AS = 'TwitterSetup';

      Admin_TwitterSetup_Ctrl_TwitterSetup.DEPS = [];

      Admin_TwitterSetup_Ctrl_TwitterSetup.prototype.init = function() {
        return this.setup = null;
      };

      Admin_TwitterSetup_Ctrl_TwitterSetup.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'twitter_setup': '/twitter_setup'
        }).then((function(_this) {
          return function(res) {
            _this.$scope.setup = res.data.twitter_setup.twitter_setup;
            return _this.setup = angular.copy(_this.$scope.setup);
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      Admin_TwitterSetup_Ctrl_TwitterSetup.prototype.isDirtyState = function() {
        if (!this.setup) {
          return false;
        }
        if (!angular.equals(this.setup, this.$scope.setup)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_TwitterSetup_Ctrl_TwitterSetup.prototype.save = function() {
        var postData, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        postData = {
          twitter_setup: this.$scope.setup
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/twitter_setup', postData).success((function(_this) {
          return function() {
            _this.setup = angular.copy(_this.$scope.setup);
            return _this.stopSpinner('saving').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_setup'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_TwitterSetup_Ctrl_TwitterSetup;

    })(Admin_Ctrl_Base);
    return Admin_TwitterSetup_Ctrl_TwitterSetup.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=TwitterSetup.js.map
