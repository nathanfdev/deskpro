(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ChatFields_Ctrl_List, _ref;
    Admin_ChatFields_Ctrl_List = (function(_super) {
      __extends(Admin_ChatFields_Ctrl_List, _super);

      function Admin_ChatFields_Ctrl_List() {
        _ref = Admin_ChatFields_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ChatFields_Ctrl_List.CTRL_ID = 'Admin_ChatFields_Ctrl_List';

      Admin_ChatFields_Ctrl_List.CTRL_AS = 'ChatFieldsList';

      Admin_ChatFields_Ctrl_List.DEPS = [];

      Admin_ChatFields_Ctrl_List.prototype.init = function() {
        this.chat_fields = this.DataService.get('ChatFields');
        this.custom_fields = [];
      };

      Admin_ChatFields_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.chat_fields.loadList();
        promise.then(function(list) {
          return _this.custom_fields = list;
        });
        return promise;
      };

      return Admin_ChatFields_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_ChatFields_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/