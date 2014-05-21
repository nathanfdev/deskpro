(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketEscalations_Ctrl_List;
    Admin_TicketEscalations_Ctrl_List = (function(_super) {
      __extends(Admin_TicketEscalations_Ctrl_List, _super);

      function Admin_TicketEscalations_Ctrl_List() {
        return Admin_TicketEscalations_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketEscalations_Ctrl_List.CTRL_ID = 'Admin_TicketEscalations_Ctrl_List';

      Admin_TicketEscalations_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_TicketEscalations_Ctrl_List.DEPS = ['$state', '$stateParams', 'DataService'];

      Admin_TicketEscalations_Ctrl_List.prototype.init = function() {
        this.list = [];
        this.escData = this.DataService.get('TicketEscalations');
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
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
              return _this.escData.saveRunOrder(orders).then(function() {
                return _this.pingElement('run_orders');
              });
            };
          })(this)
        };
      };

      Admin_TicketEscalations_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.escData.loadList();
        promise.then((function(_this) {
          return function(list) {
            _this.list = list;
            if (_this.$state.current.name === 'tickets.ticket_escalations') {
              if (_this.list[0]) {
                return _this.$state.go('tickets.ticket_escalations.edit', {
                  id: _this.list[0].id
                });
              } else {
                return _this.$state.go('tickets.ticket_escalations.create');
              }
            }
          };
        })(this));
        return promise;
      };


      /*
      		 * Show the delete dlg
       */

      Admin_TicketEscalations_Ctrl_List.prototype.startDelete = function(esc_id) {
        var esc, inst, v, _i, _len, _ref;
        esc = null;
        _ref = this.list;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          v = _ref[_i];
          if (v.id === esc) {
            esc = v;
            break;
          }
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketEscalations/delete-modal.html'),
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
        return inst.result.then((function(_this) {
          return function() {
            return _this.escData.deleteEscalationById(esc_id).then(function() {
              if (_this.$state.current.name === 'tickets.ticket_escalations.edit' && parseInt(_this.$state.params.id) === esc_id) {
                return _this.$state.go('tickets.ticket_escalations');
              }
            });
          };
        })(this));
      };

      return Admin_TicketEscalations_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketEscalations_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
