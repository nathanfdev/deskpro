(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserFields_Ctrl_List;
    Admin_UserFields_Ctrl_List = (function(_super) {
      __extends(Admin_UserFields_Ctrl_List, _super);

      function Admin_UserFields_Ctrl_List() {
        return Admin_UserFields_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_UserFields_Ctrl_List.CTRL_ID = 'Admin_UserFields_Ctrl_List';

      Admin_UserFields_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_UserFields_Ctrl_List.DEPS = [];


      /*
       	 *
       */

      Admin_UserFields_Ctrl_List.prototype.init = function() {
        this.chat_fields = this.DataService.get('UserFields');
        this.custom_fields = [];
      };


      /*
       	 *
       */

      Admin_UserFields_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.chat_fields.loadList();
        promise.then((function(_this) {
          return function(list) {
            return _this.custom_fields = list;
          };
        })(this));
        return promise;
      };


      /*
       	 *
       */

      Admin_UserFields_Ctrl_List.prototype.updateCustomFieldEnabledState = function(field) {
        var val;
        if (field.is_enabled) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/user_fields/set-enabled/field_' + field.id + '/' + val);
      };

      return Admin_UserFields_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_UserFields_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
