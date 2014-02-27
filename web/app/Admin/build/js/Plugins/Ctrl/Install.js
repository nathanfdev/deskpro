(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Plugins_Ctrl_Install;
    Admin_Plugins_Ctrl_Install = (function(_super) {
      __extends(Admin_Plugins_Ctrl_Install, _super);

      function Admin_Plugins_Ctrl_Install() {
        return Admin_Plugins_Ctrl_Install.__super__.constructor.apply(this, arguments);
      }

      Admin_Plugins_Ctrl_Install.CTRL_ID = 'Admin_Plugins_Ctrl_Install';

      Admin_Plugins_Ctrl_Install.CTRL_AS = 'InstallCtrl';

      Admin_Plugins_Ctrl_Install.prototype.init = function() {
        return this["package"] = [];
      };

      Admin_Plugins_Ctrl_Install.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/plugins/package/" + this.$stateParams.name + "/installer").then((function(_this) {
          return function(result) {
            return _this["package"] = result.data.plugin_def;
          };
        })(this));
        return promise;
      };

      return Admin_Plugins_Ctrl_Install;

    })(Admin_Ctrl_Base);
    return Admin_Plugins_Ctrl_Install.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Install.js.map
