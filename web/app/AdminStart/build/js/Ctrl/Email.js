(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminStart/Ctrl/StartBase'], function(StartBase) {
    var AdminStart_Ctrl_Email;
    AdminStart_Ctrl_Email = (function(_super) {
      __extends(AdminStart_Ctrl_Email, _super);

      function AdminStart_Ctrl_Email() {
        return AdminStart_Ctrl_Email.__super__.constructor.apply(this, arguments);
      }

      AdminStart_Ctrl_Email.CTRL_ID = 'AdminStart_Ctrl_Email';

      AdminStart_Ctrl_Email.prototype.init = function() {};

      return AdminStart_Ctrl_Email;

    })(StartBase);
    return AdminStart_Ctrl_Email.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Email.js.map
