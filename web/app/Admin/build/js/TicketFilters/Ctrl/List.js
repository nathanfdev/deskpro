(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketFilters_Ctrl_List, _ref;
    Admin_TicketFilters_Ctrl_List = (function(_super) {
      __extends(Admin_TicketFilters_Ctrl_List, _super);

      function Admin_TicketFilters_Ctrl_List() {
        _ref = Admin_TicketFilters_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFilters_Ctrl_List.CTRL_ID = 'Admin_TicketFilters_Ctrl_List';

      Admin_TicketFilters_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_TicketFilters_Ctrl_List.DEPS = ['$state', '$stateParams', 'DataService'];

      Admin_TicketFilters_Ctrl_List.CTRL_TYPE = 'list';

      Admin_TicketFilters_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.list = [];
        this.filterData = this.DataService.get('TicketFilters');
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: function(ev, data) {
            var $list, orders;
            $list = data.item.closest('ul');
            orders = [];
            $list.find('li').each(function() {
              var id;
              id = parseInt($(this).data('id'));
              console.log(id);
              if (id) {
                return orders.push(id);
              }
            });
            return _this.filterData.saveDisplayOrder(orders).then(function() {
              return _this.pingElement('display_orders');
            });
          }
        };
      };

      Admin_TicketFilters_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.filterData.loadList();
        promise.then(function(list) {
          _this.list = list;
          if (_this.$state.current.name === 'tickets.ticket_filters') {
            if (_this.list[0]) {
              return _this.$state.go('tickets.ticket_filters.edit', {
                id: _this.list[0].id
              });
            } else {
              return _this.$state.go('tickets.ticket_filters.create');
            }
          }
        });
        return promise;
      };

      /*
      		# Show the delete dlg
      */


      Admin_TicketFilters_Ctrl_List.prototype.startDelete = function(filter) {
        var inst,
          _this = this;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketFilters/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then(function() {
          return _this.filterData.deleteFilterId(filter.id).then(function() {
            if (_this.$state.current.name === 'tickets.ticket_filters.edit' && parseInt(_this.$state.params.id) === filter.id) {
              return _this.$state.go('tickets.ticket_filters');
            }
          });
        });
      };

      return Admin_TicketFilters_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketFilters_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/