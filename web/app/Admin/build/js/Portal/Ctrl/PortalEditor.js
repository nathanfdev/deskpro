(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Portal_Ctrl_PortalEditor, _ref;
    Admin_Portal_Ctrl_PortalEditor = (function(_super) {
      __extends(Admin_Portal_Ctrl_PortalEditor, _super);

      function Admin_Portal_Ctrl_PortalEditor() {
        _ref = Admin_Portal_Ctrl_PortalEditor.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Portal_Ctrl_PortalEditor.CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor';

      Admin_Portal_Ctrl_PortalEditor.CTRL_AS = 'Portal';

      Admin_Portal_Ctrl_PortalEditor.prototype.init = function() {};

      Admin_Portal_Ctrl_PortalEditor.prototype.initialLoad = function() {};

      return Admin_Portal_Ctrl_PortalEditor;

    })(Admin_Ctrl_Base);
    return Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=PortalEditor.js.map
*/