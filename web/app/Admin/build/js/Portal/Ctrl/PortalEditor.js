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

      Admin_Portal_Ctrl_PortalEditor.prototype.init = function() {
        this.portal_enabled = false;
      };

      Admin_Portal_Ctrl_PortalEditor.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet('/settings/values/user.portal_enabled').then(function(result) {
          if (result.data.value === true || result.data.value === "1") {
            return _this.portal_enabled = true;
          } else {
            return _this.portal_enabled = false;
          }
        });
        return promise;
      };

      Admin_Portal_Ctrl_PortalEditor.prototype.startTogglePortal = function() {
        var message,
          _this = this;
        if (this.portal_enabled) {
          message = "Are are sure you want to disable the portal? The front-end portal website for end-users will be completely disabled. Anyone who knows the URL of the portal will see a blank page.";
        } else {
          message = "Are are sure you want to enable the portal?";
        }
        this.showConfirm(message).result.then(function() {
          return _this.togglePortal();
        });
      };

      Admin_Portal_Ctrl_PortalEditor.prototype.togglePortal = function() {
        var newVal,
          _this = this;
        newVal = this.portal_enabled ? '0' : '1';
        return this.Api.sendPost('/settings/values/user.portal_enabled', {
          value: newVal
        }).then(function() {
          return _this.$state.go('portal.portal_editor_go');
        });
      };

      return Admin_Portal_Ctrl_PortalEditor;

    })(Admin_Ctrl_Base);
    return Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=PortalEditor.js.map
*/