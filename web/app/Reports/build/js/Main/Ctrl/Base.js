(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Main/Ctrl/Base'], function(DeskPROBaseCtrl) {
    var Reports_Ctrl_Base;
    return Reports_Ctrl_Base = (function(_super) {
      __extends(Reports_Ctrl_Base, _super);

      function Reports_Ctrl_Base() {
        return Reports_Ctrl_Base.__super__.constructor.apply(this, arguments);
      }

      Reports_Ctrl_Base.CTRL_AS = null;

      Reports_Ctrl_Base.CTRL_ID = 'Reports_Main_Ctrl_Base';

      Reports_Ctrl_Base.DEPS = [];


      /**
      		* Get the URL to the template
      		*
      		* @return {String}
       */

      Reports_Ctrl_Base.prototype.getTemplatePath = function(path) {
        return DP_BASE_REPORTS_URL + '/load-view/' + path;
      };

      return Reports_Ctrl_Base;

    })(DeskPROBaseCtrl);
  });

}).call(this);

//# sourceMappingURL=Base.js.map
