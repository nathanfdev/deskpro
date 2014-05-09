(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Main/Ctrl/Base'], function(DeskPROBaseCtrl) {
    var Reports_Main_Ctrl_Bare;
    Reports_Main_Ctrl_Bare = (function(_super) {
      __extends(Reports_Main_Ctrl_Bare, _super);

      function Reports_Main_Ctrl_Bare() {
        return Reports_Main_Ctrl_Bare.__super__.constructor.apply(this, arguments);
      }

      Reports_Main_Ctrl_Bare.CTRL_ID = 'Reports_Main_Ctrl_Bare';

      return Reports_Main_Ctrl_Bare;

    })(DeskPROBaseCtrl);
    return Reports_Main_Ctrl_Bare.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Bare.js.map
