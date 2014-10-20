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

      Admin_UserFields_Ctrl_List.prototype.init = function() {
        var data;
        this.fieldDataService = this.DataService.get('UserFields');
        this.custom_fields = [];
        this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
              var $list, displayOrders;
              $list = data.item.closest('ul');
              displayOrders = [];
              $list.find('li').each(function() {
                return displayOrders.push(parseInt($(this).data('id')));
              });
              _this.fieldDataService.saveDisplayOrder(displayOrders);
              return _this.pingElement('display_orders');
            };
          })(this)
        };
        this.specific_user_custom_fields = [];
        data = this.$state.current.data;
        this.service = this.DataService.get('CustomFields', data.owner, data.context);
        this.customFieldListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
              var $list, displayOrders;
              $list = data.item.closest('ul');
              displayOrders = [];
              $list.find('li').each(function() {
                return displayOrders.push(parseInt($(this).data('id')));
              });
              _this.service.saveDisplayOrder(displayOrders);
              return _this.pingElement('display_orders');
            };
          })(this)
        };
      };

      Admin_UserFields_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.fieldDataService.loadList();
        promise.then((function(_this) {
          return function(list) {
            return _this.custom_fields = list;
          };
        })(this));
        this.service.all().then((function(_this) {
          return function(list) {
            return _this.specific_user_custom_fields = list;
          };
        })(this));
        return promise;
      };

      return Admin_UserFields_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_UserFields_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
