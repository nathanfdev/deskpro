(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_Nav;
    Admin_Main_Ctrl_Nav = (function(_super) {
      __extends(Admin_Main_Ctrl_Nav, _super);

      function Admin_Main_Ctrl_Nav() {
        return Admin_Main_Ctrl_Nav.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_Nav.CTRL_ID = 'Admin_Main_Ctrl_Nav';

      Admin_Main_Ctrl_Nav.DEPS = ['$timeout'];

      Admin_Main_Ctrl_Nav.prototype.init = function() {
        var depth;
        depth = this.$state.current.name.split('.').length;
        if (depth === 1) {
          this.$timeout(function() {
            return $('.dp-layout-appnav').find('li').first().find('a').click();
          }, 10);
        }
      };

      return Admin_Main_Ctrl_Nav;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_Nav.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Nav.js.map
