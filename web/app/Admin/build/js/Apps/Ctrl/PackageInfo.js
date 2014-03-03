(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Apps_Ctrl_PackageInfo;
    Admin_Apps_Ctrl_PackageInfo = (function(_super) {
      __extends(Admin_Apps_Ctrl_PackageInfo, _super);

      function Admin_Apps_Ctrl_PackageInfo() {
        return Admin_Apps_Ctrl_PackageInfo.__super__.constructor.apply(this, arguments);
      }

      Admin_Apps_Ctrl_PackageInfo.CTRL_ID = 'Admin_Apps_Ctrl_PackageInfo';

      Admin_Apps_Ctrl_PackageInfo.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_PackageInfo.DEPS = ['$http'];

      Admin_Apps_Ctrl_PackageInfo.prototype.init = function() {
        this.packageName = this.$stateParams.name;
      };

      Admin_Apps_Ctrl_PackageInfo.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          pack: '/apps/packages/' + this.packageName
        }).then((function(_this) {
          return function(result) {
            return _this.pack = result.data.pack['package'];
          };
        })(this));
        return promise;
      };

      return Admin_Apps_Ctrl_PackageInfo;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_PackageInfo.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=PackageInfo.js.map
