(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Apps_Ctrl_EditInstance, _ref;
    Admin_Apps_Ctrl_EditInstance = (function(_super) {
      __extends(Admin_Apps_Ctrl_EditInstance, _super);

      function Admin_Apps_Ctrl_EditInstance() {
        _ref = Admin_Apps_Ctrl_EditInstance.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Apps_Ctrl_EditInstance.CTRL_ID = 'Admin_Apps_Ctrl_EditInstance';

      Admin_Apps_Ctrl_EditInstance.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_EditInstance.DEPS = [];

      Admin_Apps_Ctrl_EditInstance.prototype.init = function() {};

      Admin_Apps_Ctrl_EditInstance.prototype.initialLoad = function() {
        return null;
      };

      return Admin_Apps_Ctrl_EditInstance;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_EditInstance.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EditInstance.js.map
*/