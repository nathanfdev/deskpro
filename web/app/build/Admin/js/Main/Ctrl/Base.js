(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Main/Ctrl/Base'], function(DeskPRO_Main_Ctrl_Base) {
    var Admin_Ctrl_Base;
    return Admin_Ctrl_Base = (function(_super) {
      __extends(Admin_Ctrl_Base, _super);

      function Admin_Ctrl_Base() {
        return Admin_Ctrl_Base.__super__.constructor.apply(this, arguments);
      }

      Admin_Ctrl_Base.CTRL_AS = null;

      Admin_Ctrl_Base.CTRL_ID = 'Admin_Main_Ctrl_Base';

      Admin_Ctrl_Base.DEPS = [];


      /**
      		* Get the URL to the template
      		*
      		* @return {String}
       */

      Admin_Ctrl_Base.prototype.getTemplatePath = function(path) {
        return DP_BASE_ADMIN_URL + '/load-view/' + path;
      };

      return Admin_Ctrl_Base;

    })(DeskPRO_Main_Ctrl_Base);
  });

}).call(this);

//# sourceMappingURL=Base.js.map
