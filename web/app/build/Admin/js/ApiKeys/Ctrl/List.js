(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ApiKeys_Ctrl_List;
    Admin_ApiKeys_Ctrl_List = (function(_super) {
      __extends(Admin_ApiKeys_Ctrl_List, _super);

      function Admin_ApiKeys_Ctrl_List() {
        return Admin_ApiKeys_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_ApiKeys_Ctrl_List.CTRL_ID = 'Admin_ApiKeys_Ctrl_List';

      Admin_ApiKeys_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_ApiKeys_Ctrl_List.prototype.init = function() {
        return this.service = this.DataService.get('ApiKeys');
      };


      /*
      		 * Loads the list
       */

      Admin_ApiKeys_Ctrl_List.prototype.initialLoad = function() {
        return this.service.all().then((function(_this) {
          return function(list) {
            return _this.list = list;
          };
        })(this));
      };

      return Admin_ApiKeys_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_ApiKeys_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
