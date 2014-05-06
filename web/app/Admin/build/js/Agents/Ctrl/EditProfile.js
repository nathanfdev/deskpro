(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], function(Strings, Admin_Ctrl_Base) {
    var Admin_Agents_Ctrl_EditProfile;
    Admin_Agents_Ctrl_EditProfile = (function(_super) {
      __extends(Admin_Agents_Ctrl_EditProfile, _super);

      function Admin_Agents_Ctrl_EditProfile() {
        return Admin_Agents_Ctrl_EditProfile.__super__.constructor.apply(this, arguments);
      }

      Admin_Agents_Ctrl_EditProfile.CTRL_ID = 'Admin_Agents_Ctrl_EditProfile';

      Admin_Agents_Ctrl_EditProfile.CTRL_AS = 'Edit';

      Admin_Agents_Ctrl_EditProfile.DEPS = ['form', 'agent', '$modalInstance'];

      Admin_Agents_Ctrl_EditProfile.prototype.init = function() {
        this.$scope.dismiss = (function(_this) {
          return function() {
            return _this.$modalInstance.dismiss();
          };
        })(this);
        if (this.agent.picture_blob) {
          return this.form.picture_set = 'current';
        } else {
          return this.form.picture_set = 'default';
        }
      };

      return Admin_Agents_Ctrl_EditProfile;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_EditProfile.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditProfile.js.map
