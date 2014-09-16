(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Usersources_Ctrl_UsersourcesList;
    Admin_Usersources_Ctrl_UsersourcesList = (function(_super) {
      __extends(Admin_Usersources_Ctrl_UsersourcesList, _super);

      function Admin_Usersources_Ctrl_UsersourcesList() {
        return Admin_Usersources_Ctrl_UsersourcesList.__super__.constructor.apply(this, arguments);
      }

      Admin_Usersources_Ctrl_UsersourcesList.CTRL_ID = 'Admin_Usersources_Ctrl_UsersourcesList';

      Admin_Usersources_Ctrl_UsersourcesList.CTRL_AS = 'ListCtrl';

      Admin_Usersources_Ctrl_UsersourcesList.DEPS = [];

      Admin_Usersources_Ctrl_UsersourcesList.prototype.init = function() {
        this.usersourcesDataService = this.DataService.get('Usersources');
        return this.sortedListOptions = {
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
              _this.usersourcesDataService.saveDisplayOrder(displayOrders);
              return _this.pingElement('display_orders');
            };
          })(this)
        };
      };

      Admin_Usersources_Ctrl_UsersourcesList.prototype.initialLoad = function() {
        var promise;
        promise = this.refresh();
        return promise;
      };

      Admin_Usersources_Ctrl_UsersourcesList.prototype.updateAppTitle = function(id, title) {
        this.usersources.filter(function(x) {
          var _ref;
          return ((_ref = x.app) != null ? _ref.id : void 0) === id;
        }).map(function(x) {
          return x.usersource.title = title;
        });
        return this.refresh();
      };

      Admin_Usersources_Ctrl_UsersourcesList.prototype.refresh = function() {
        return this.Api.sendGet('/usersources/user').then((function(_this) {
          return function(result) {
            return _this.usersources = result.data.usersources;
          };
        })(this));
      };

      return Admin_Usersources_Ctrl_UsersourcesList;

    })(Admin_Ctrl_Base);
    return Admin_Usersources_Ctrl_UsersourcesList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=UsersourcesList.js.map
