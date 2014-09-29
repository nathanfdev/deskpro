(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Apps_Ctrl_Resync;
    Admin_Apps_Ctrl_Resync = (function(_super) {
      __extends(Admin_Apps_Ctrl_Resync, _super);

      function Admin_Apps_Ctrl_Resync() {
        return Admin_Apps_Ctrl_Resync.__super__.constructor.apply(this, arguments);
      }

      Admin_Apps_Ctrl_Resync.CTRL_ID = 'Admin_Apps_Ctrl_Resync';

      Admin_Apps_Ctrl_Resync.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_Resync.DEPS = [];

      Admin_Apps_Ctrl_Resync.prototype.init = function() {
        this.$scope.state = 'default';
      };

      Admin_Apps_Ctrl_Resync.prototype.initialLoad = function() {};

      Admin_Apps_Ctrl_Resync.prototype.beginResync = function() {
        this.$scope.state = 'running';
        return this.Api.sendPost("/apps/resync-packages").then((function(_this) {
          return function(result) {
            _this.$scope.state = 'done';
            return _this.$scope.log = result.data.log;
          };
        })(this), (function(_this) {
          return function(result) {
            _this.$scope.state = 'done';
            return _this.$scope.log = 'There was an error. Please try again.';
          };
        })(this));
      };

      return Admin_Apps_Ctrl_Resync;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_Resync.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Resync.js.map
