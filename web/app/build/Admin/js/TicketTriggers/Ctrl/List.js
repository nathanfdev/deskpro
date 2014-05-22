(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Ctrl_Base, OrderedDictionary) {
    var Admin_TicketTriggers_Ctrl_List;
    Admin_TicketTriggers_Ctrl_List = (function(_super) {
      __extends(Admin_TicketTriggers_Ctrl_List, _super);

      function Admin_TicketTriggers_Ctrl_List() {
        return Admin_TicketTriggers_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketTriggers_Ctrl_List.CTRL_ID = 'Admin_TicketTriggers_Ctrl_List';

      Admin_TicketTriggers_Ctrl_List.CTRL_AS = 'List';

      Admin_TicketTriggers_Ctrl_List.DEPS = ['$state', '$stateParams', '$q', 'TicketAccountsData'];

      Admin_TicketTriggers_Ctrl_List.prototype.init = function() {
        this.dep_triggers = [];
        this.email_triggers = [];
        this.all_triggers = [];
        this.triggers = [];
        this.depTriggersEnabled = true;
        this.emailTriggersEnabled = true;
        this.eventType = this.$stateParams.type;
        if (this.$stateParams.type === 'newticket') {
          this.dpTriggers = this.DataService.get('TriggersNew');
        } else if (this.$stateParams.type === 'newreply') {
          this.dpTriggers = this.DataService.get('TriggersReply');
        } else {
          this.dpTriggers = this.DataService.get('TriggersUpdate');
        }
        this.depData = this.DataService.get('TicketDeps');
        this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
              var $list, runOrders;
              $list = data.item.closest('ul');
              runOrders = [];
              $list.find('li').each(function() {
                return runOrders.push(parseInt($(this).data('id')));
              });
              _this.dpTriggers.saveRunOrder(runOrders);
              return _this.pingElement('run_orders');
            };
          })(this)
        };
        return this.$scope.$watch('TicketTriggersList.all_triggers', (function(_this) {
          return function() {
            return _this.sortTriggers();
          };
        })(this), true);
      };


      /*
      		 * Loads the triggers list
       */

      Admin_TicketTriggers_Ctrl_List.prototype.initialLoad = function() {
        var promises;
        promises = [];
        promises.push(this.dpTriggers.loadList().then((function(_this) {
          return function(list) {
            window.all_triggers = list;
            _this.all_triggers = list;
            return _this.sortTriggers();
          };
        })(this)));
        promises.push(this.depData.loadList().then((function(_this) {
          return function(list) {
            return _this.depList = list;
          };
        })(this)));
        promises.push(this.TicketAccountsData.loadList().then((function(_this) {
          return function(recs) {
            return _this.accounts = recs.values();
          };
        })(this)));
        return this.$q.all(promises);
      };


      /*
        	 * Sorts triggers into display groups
       */

      Admin_TicketTriggers_Ctrl_List.prototype.sortTriggers = function() {
        var tr, _i, _len, _ref, _results;
        this.dep_triggers = [];
        this.email_triggers = [];
        this.triggers = [];
        _ref = this.all_triggers;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          tr = _ref[_i];
          if (tr.department) {
            _results.push(this.dep_triggers.push(tr));
          } else if (tr.email_account) {
            _results.push(this.email_triggers.push(tr));
          } else {
            _results.push(this.triggers.push(tr));
          }
        }
        return _results;
      };


      /*
      		 * Update the enabled state of a trigger
       */

      Admin_TicketTriggers_Ctrl_List.prototype.updateTriggerEnabledState = function(trigger) {
        return this.dpTriggers.saveEnabledStateById(trigger.id, trigger.is_enabled);
      };

      Admin_TicketTriggers_Ctrl_List.prototype.updateDepTriggersEnabledState = function() {};

      Admin_TicketTriggers_Ctrl_List.prototype.updateEmailTriggersEnabledState = function() {};


      /*
      		 * Show the delete dlg
       */

      Admin_TicketTriggers_Ctrl_List.prototype.startTriggerDelete = function(trigger_id) {
        var inst, trigger, v, _i, _len, _ref;
        trigger = null;
        _ref = this.triggers;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          v = _ref[_i];
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
        return inst.result.then((function(_this) {
          return function() {
            return _this.dpTriggers.deleteTriggerById(trigger.id).then(function() {
              _this.sortTriggers();
              return _this.$state.go('tickets.triggers', {
                type: _this.$stateParams.type
              });
            });
          };
        })(this));
      };

      return Admin_TicketTriggers_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketTriggers_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
