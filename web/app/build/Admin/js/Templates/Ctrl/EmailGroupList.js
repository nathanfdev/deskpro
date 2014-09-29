(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Templates_Ctrl_EmailGroupList;
    Admin_Templates_Ctrl_EmailGroupList = (function(_super) {
      __extends(Admin_Templates_Ctrl_EmailGroupList, _super);

      function Admin_Templates_Ctrl_EmailGroupList() {
        return Admin_Templates_Ctrl_EmailGroupList.__super__.constructor.apply(this, arguments);
      }

      Admin_Templates_Ctrl_EmailGroupList.CTRL_ID = 'Admin_Templates_Ctrl_EmailGroupList';

      Admin_Templates_Ctrl_EmailGroupList.CTRL_AS = 'ListCtrl';

      Admin_Templates_Ctrl_EmailGroupList.DEPS = [];

      Admin_Templates_Ctrl_EmailGroupList.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          info: '/email-templates-info'
        }).then((function(_this) {
          return function(res) {
            return _this.templateInfo = res.data.info.list;
          };
        })(this));
        return promise;
      };

      return Admin_Templates_Ctrl_EmailGroupList;

    })(Admin_Ctrl_Base);
    return Admin_Templates_Ctrl_EmailGroupList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EmailGroupList.js.map
