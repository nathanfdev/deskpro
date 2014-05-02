(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminStart/Ctrl/StartBase'], function(StartBase) {
    var AdminStart_Ctrl_License;
    AdminStart_Ctrl_License = (function(_super) {
      __extends(AdminStart_Ctrl_License, _super);

      function AdminStart_Ctrl_License() {
        return AdminStart_Ctrl_License.__super__.constructor.apply(this, arguments);
      }

      AdminStart_Ctrl_License.CTRL_ID = 'AdminStart_Ctrl_License';

      AdminStart_Ctrl_License.prototype.init = function() {};

      return AdminStart_Ctrl_License;

    })(StartBase);
    return AdminStart_Ctrl_License.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=License.js.map
