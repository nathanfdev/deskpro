(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminStart/Ctrl/StartBase'], function(StartBase) {
    var AdminStart_Ctrl_Finish;
    AdminStart_Ctrl_Finish = (function(_super) {
      __extends(AdminStart_Ctrl_Finish, _super);

      function AdminStart_Ctrl_Finish() {
        return AdminStart_Ctrl_Finish.__super__.constructor.apply(this, arguments);
      }

      AdminStart_Ctrl_Finish.CTRL_ID = 'AdminStart_Ctrl_Finish';

      AdminStart_Ctrl_Finish.prototype.init = function() {
        this.done_set = true;
        this.set_prom = this.Api.sendPost('/start-settings/set-initial').success((function(_this) {
          return function() {
            return _this.done_set = true;
          };
        })(this));
      };

      AdminStart_Ctrl_Finish.prototype.goAdmin = function(ev, el) {
        if (!this.done_set) {
          ev.preventDefault();
          return this.set_prom.success((function(_this) {
            return function() {
              return el.click();
            };
          })(this));
        }
      };

      return AdminStart_Ctrl_Finish;

    })(StartBase);
    return AdminStart_Ctrl_Finish.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Finish.js.map
