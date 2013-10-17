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

      Admin_TicketTriggers_Ctrl_List.DEPS = ['$state'];

      Admin_TicketTriggers_Ctrl_List.CTRL_TYPE = 'list';

      Admin_TicketTriggers_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.triggers = null;
        this.triggersCollection = null;
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: function(ev, data) {
            var $list, postData, promise;
            $list = data.item.closest('ul');
            postData = {
              run_orders: []
            };
            $list.find('li').each(function() {
              return postData.run_orders.push($(this).data('id'));
            });
            promise = _this.Api.sendPostJson('/ticket_triggers/run_order', postData);
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
        promise = this.Api.sendGet('/ticket_triggers').success(function(data) {
          _this.triggersCollection = new OrderedDictionary();
          _this.triggersCollection.addArray(data.triggers);
          return _this.triggers = data.triggers;
        });
        return promise;
      };

      /*
      		# Toggle the enabled state of a trigger
      */


      Admin_TicketTriggers_Ctrl_List.prototype.toggleTriggerEnabledState = function(trigger) {
        trigger.is_enabled = !trigger.is_enabled;
        if (trigger.is_enabled) {
          return this.Api.sendPost("/ticket_triggers/" + trigger.id + "/enable");
        } else {
          return this.Api.sendPost("/ticket_triggers/" + trigger.id + "/disable");
        }
      };

      /*
      		# Show the delete dlg
      */


      Admin_TicketTriggers_Ctrl_List.prototype.startTriggerDelete = function(trigger) {
        var inst,
          _this = this;
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
          return _this.deleteTrigger(trigger);
        });
      };

      /*
      		# Actually do the delete
      */


      Admin_TicketTriggers_Ctrl_List.prototype.deleteTrigger = function(trigger) {
        var _this = this;
        this.triggersCollection.remove(trigger.id);
        this.triggers = this.triggersCollection.values();
        return this.Api.sendDelete('/ticket_triggers/' + trigger.id).success(function() {
          if (_this.$state.current.name === 'tickets.ticket_triggers.edit' && parseInt(_this.$state.params.id) === trigger.id) {
            return _this.$state.go('tickets.ticket_triggers');
          }
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