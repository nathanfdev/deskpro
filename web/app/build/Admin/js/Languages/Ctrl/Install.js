(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Languages_Ctrl_Install;
    Admin_Languages_Ctrl_Install = (function(_super) {
      __extends(Admin_Languages_Ctrl_Install, _super);

      function Admin_Languages_Ctrl_Install() {
        return Admin_Languages_Ctrl_Install.__super__.constructor.apply(this, arguments);
      }

      Admin_Languages_Ctrl_Install.CTRL_ID = 'Admin_Languages_Ctrl_Install';

      Admin_Languages_Ctrl_Install.CTRL_AS = 'EditCtrl';

      Admin_Languages_Ctrl_Install.prototype.init = function() {
        return this.id = this.$stateParams.id.replace(/^install\-/, '');
      };

      Admin_Languages_Ctrl_Install.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/langs/" + this.id).then((function(_this) {
          return function(result) {
            if (result.data.language) {
              _this.$state.go('setup.languages.edit', {
                id: _this.id
              });
              return;
            }
            _this.pack = result.data.pack;
            return _this.lang = result.data.language;
          };
        })(this));
        return promise;
      };

      Admin_Languages_Ctrl_Install.prototype.doInstall = function() {
        this.startSpinner('saving');
        return this.$scope.$parent.ListCtrl.installLang(this.id).then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true);
            return _this.$state.go('setup.languages.edit', {
              id: _this.id
            });
          };
        })(this));
      };

      return Admin_Languages_Ctrl_Install;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_Install.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Install.js.map
