(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ImportCsv_Ctrl_ImportCsv, _ref;
    Admin_ImportCsv_Ctrl_ImportCsv = (function(_super) {
      __extends(Admin_ImportCsv_Ctrl_ImportCsv, _super);

      function Admin_ImportCsv_Ctrl_ImportCsv() {
        _ref = Admin_ImportCsv_Ctrl_ImportCsv.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ImportCsv_Ctrl_ImportCsv.CTRL_ID = 'Admin_ImportCsv_Ctrl_ImportCsv';

      Admin_ImportCsv_Ctrl_ImportCsv.CTRL_AS = 'Ctrl';

      Admin_ImportCsv_Ctrl_ImportCsv.DEPS = [];

      Admin_ImportCsv_Ctrl_ImportCsv.prototype.init = function() {};

      /*
       	#
      */


      Admin_ImportCsv_Ctrl_ImportCsv.prototype.initialLoad = function() {};

      return Admin_ImportCsv_Ctrl_ImportCsv;

    })(Admin_Ctrl_Base);
    return Admin_ImportCsv_Ctrl_ImportCsv.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ImportCsv.js.map
*/