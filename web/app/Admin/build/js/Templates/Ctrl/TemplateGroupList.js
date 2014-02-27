(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['angular', 'Admin/Main/Ctrl/Base'], function(angular, Admin_Ctrl_Base) {
    var Admin_Templates_Ctrl_TemplateGroupList;
    Admin_Templates_Ctrl_TemplateGroupList = (function(_super) {
      __extends(Admin_Templates_Ctrl_TemplateGroupList, _super);

      function Admin_Templates_Ctrl_TemplateGroupList() {
        return Admin_Templates_Ctrl_TemplateGroupList.__super__.constructor.apply(this, arguments);
      }

      Admin_Templates_Ctrl_TemplateGroupList.CTRL_ID = 'Admin_Templates_Ctrl_TemplateGroupList';

      Admin_Templates_Ctrl_TemplateGroupList.CTRL_AS = 'ListCtrl';

      Admin_Templates_Ctrl_TemplateGroupList.DEPS = [];

      Admin_Templates_Ctrl_TemplateGroupList.prototype.init = function() {};

      Admin_Templates_Ctrl_TemplateGroupList.prototype.initialLoad = function() {
        var promise;
        return promise = this.Api.sendDataGet({
          info: '/templates-info'
        }).then((function(_this) {
          return function(res) {
            var groupName, title, tplList, _ref;
            _this.groups = [];
            _ref = res.data.info.list.UserBundle;
            for (groupName in _ref) {
              if (!__hasProp.call(_ref, groupName)) continue;
              tplList = _ref[groupName];
              title = groupName;
              if (title === 'TOP') {
                title = 'Layout';
              }
              _this.groups.push({
                id: "UserBundle:" + groupName,
                title: title
              });
            }
            return _this.groups.push({
              id: "DeskPRO:custom_fields",
              title: 'CustomFields'
            });
          };
        })(this));
      };

      return Admin_Templates_Ctrl_TemplateGroupList;

    })(Admin_Ctrl_Base);
    return Admin_Templates_Ctrl_TemplateGroupList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=TemplateGroupList.js.map
