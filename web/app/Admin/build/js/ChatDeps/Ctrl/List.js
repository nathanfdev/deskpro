(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ChatDeps_Ctrl_List, _ref;
    Admin_ChatDeps_Ctrl_List = (function(_super) {
      __extends(Admin_ChatDeps_Ctrl_List, _super);

      function Admin_ChatDeps_Ctrl_List() {
        _ref = Admin_ChatDeps_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ChatDeps_Ctrl_List.CTRL_ID = 'Admin_ChatDeps_Ctrl_List';

      Admin_ChatDeps_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_ChatDeps_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.depData = this.DataService.get('ChatDeps');
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: function(ev, data) {
            var $list, order;
            $list = data.item.closest('ul');
            order = [];
            $list.find('li').each(function() {
              return order.push(parseInt($(this).data('id')));
            });
            _this.depData.saveDisplayOrders(order);
            return _this.pingElement('display_orders');
          }
        };
      };

      /*
      		# Loads the dep list
      */


      Admin_ChatDeps_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.depData.loadList().then(function(list) {
          _this.depList = list;
          return _this.deps = _this.depData.listModels;
        });
        return promise;
      };

      return Admin_ChatDeps_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_ChatDeps_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/