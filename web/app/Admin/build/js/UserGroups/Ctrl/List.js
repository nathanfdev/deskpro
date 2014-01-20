(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserGroups_Ctrl_List, _ref;
    Admin_UserGroups_Ctrl_List = (function(_super) {
      __extends(Admin_UserGroups_Ctrl_List, _super);

      function Admin_UserGroups_Ctrl_List() {
        _ref = Admin_UserGroups_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_UserGroups_Ctrl_List.CTRL_ID = 'Admin_UserGroups_Ctrl_List';

      Admin_UserGroups_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_UserGroups_Ctrl_List.DEPS = [];

      Admin_UserGroups_Ctrl_List.prototype.init = function() {
        return this.ugData = this.DataService.get('UserGroups');
      };

      Admin_UserGroups_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.ugData.loadList().then(function(list) {
          return _this.list = list;
        });
        return promise;
      };

      Admin_UserGroups_Ctrl_List.prototype.toggleUserGroup = function(user_group) {
        var val;
        if (user_group.is_enabled) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/user_groups/set-enabled/' + user_group.id + '/' + val);
      };

      return Admin_UserGroups_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_UserGroups_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/