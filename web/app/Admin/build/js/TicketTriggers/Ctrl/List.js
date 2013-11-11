(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Ctrl_Base, OrderedDictionary) {
    var Admin_TicketTriggers_Ctrl_List, _ref;
    Admin_TicketTriggers_Ctrl_List = (function(_super) {
      __extends(Admin_TicketTriggers_Ctrl_List, _super);

      function Admin_TicketTriggers_Ctrl_List() {
        _ref = Admin_TicketTriggers_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketTriggers_Ctrl_List.CTRL_ID = 'Admin_TicketTriggers_Ctrl_List';

      Admin_TicketTriggers_Ctrl_List.CTRL_AS = 'TicketTriggersList';

      Admin_TicketTriggers_Ctrl_List.DEPS = ['$state', '$stateParams'];

      Admin_TicketTriggers_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.triggers = null;
        this.eventType = this.$stateParams.type;
        if (this.$stateParams.type === 'newticket') {
          this.dpTriggers = this.DataService.get('TriggersNew');
        } else if (this.$stateParams.type === 'newreply') {
          this.dpTriggers = this.DataService.get('TriggersReply');
        } else {
          this.dpTriggers = this.DataService.get('TriggersUpdate');
        }
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: function(ev, data) {
            var $list, runOrders;
            $list = data.item.closest('ul');
            runOrders = [];
            $list.find('li').each(function() {
              return runOrders.push(parseInt($(this).data('id')));
            });
            _this.dpTriggers.saveRunOrder(runOrders);
            return _this.pingElement('run_orders');
          }
        };
      };

      /*
      		# Loads the triggers list
      */


      Admin_TicketTriggers_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.dpTriggers.loadList().then(function(list) {
          return _this.triggers = list;
        });
        return promise;
      };

      /*
      		# Update the enabled state of a trigger
      */


      Admin_TicketTriggers_Ctrl_List.prototype.updateTriggerEnabledState = function(trigger) {
        return this.dpTriggers.saveEnabledState(trigger);
      };

      /*
      		# Show the delete dlg
      */


      Admin_TicketTriggers_Ctrl_List.prototype.startTriggerDelete = function(trigger_id) {
        var inst, trigger, v, _i, _len, _ref1,
          _this = this;
        trigger = null;
        _ref1 = this.triggers;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          v = _ref1[_i];
          if (v.id === trigger_id) {
            trigger = v;
            break;
          }
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketTriggers/delete-modal.html'),
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
          return _this.dpTriggers.deleteTriggerById(trigger.id).then(function() {
            if (_this.$state.current.name === 'tickets.ticket_triggers.edit' && parseInt(_this.$state.params.id) === trigger.id) {
              return _this.$state.go('tickets.ticket_triggers');
            }
          });
        });
      };

      return Admin_TicketTriggers_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketTriggers_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/