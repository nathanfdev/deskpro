// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'Admin/Main/Collection/OrderedDictionary',
], function(
  Admin_Ctrl_Base,
  OrderedDictionary
) {
  class Admin_TicketTriggers_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketTriggers_Ctrl_List';
      this.CTRL_AS = 'List';
      this.DEPS = ['$state', '$stateParams', '$q', 'TicketAccountsData', '$timeout'];
    }

    init() {
      this.dep_triggers   = [];
      this.email_triggers = [];
      this.satisfaction_triggers = [];
      this.all_triggers   = [];
      this.triggers       = [];

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
        update: (ev, data) => {
          const $list = data.item.closest('ul');
          const runOrders = [];

          const { eventType } = this;
          $list.find('li').each(function() {
            let id = $(this).data('trigger-id');
            if (id) {
              if (id === 'departments') {
                if (eventType === 'update') { id = 'departments_changed'; }
                return runOrders.push(id);
              } else if (id === 'emailaccounts') {
                return runOrders.push(id);
              } else {
                return runOrders.push(parseInt(id));
              }
            }
          });

          this.dpTriggers.saveRunOrder(runOrders);
          return this.pingElement('run_orders');
        }
      };

      return this.$scope.$watch('List.all_triggers', () => {
        return this.sortTriggers();
      }
      , true);
    }

    /*
     * Loads the triggers list
     */
    initialLoad() {
      const promises = [];

      promises.push(this.Api.sendGet('/ticket_settings').then(res => {
        return this.$scope.$parent.settings = res.data.ticket_settings;
      })
      );

      promises.push(this.dpTriggers.loadList(true).then( list => {
        window.all_triggers = list;
        this.all_triggers = list;

        this.depTriggersEnabled   = this.dpTriggers.department_triggers_enabled;
        this.emailTriggersEnabled = this.dpTriggers.emailaccount_triggers_enabled;

        this.sortTriggers();

        this.$scope.dep_order = 0;
        this.$scope.emailaccount_order = 0;
        this.$scope.satisfation_order = 0;

        return (() => {
          const result = [];
          for (let t of Array.from(this.all_triggers)) {
            if (!this.$scope.dep_order && t.department) {
              this.$scope.dep_order = t.run_order;
            }
            if (!this.$scope.emailaccount_order && t.email_account) {
              this.$scope.emailaccount_order = t.run_order;
            }
            if (!this.$scope.satisfaction_order && t.sys_name && t.sys_name.match(/^default_update_satisfaction.+/)) {
              result.push(this.$scope.satisfaction_order = t.run_order);
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      })
      );

      if ((this.eventType === 'newticket') || (this.eventType === 'update')) {
        promises.push(this.depData.loadList(true).then( list => {
          return this.depList = list;
        })
        );
      }

      if (this.eventType === 'newticket') {
        promises.push(this.TicketAccountsData.loadList(true).then( recs => {
          this.accounts = recs.values();
          return this.accounts = this.accounts.filter(x => x.account_type !== 'outgoing');
        })
        );
      }

      if (this.eventType === 'update') {
        this.satisfactions = [
          {id: 0, title: 'negative'},
          {id: 1, title: 'neutral'},
          {id: 2, title: 'positive'}
        ];
      }

      const d = this.$q.defer();

      // run re-order stuff (from dpMoveListToPos)
      // while loading indicator is still spinning,
      // eliminates the visual stutter
      this.$q.all(promises).then(() => {
        return this.$timeout(() => {
          this.$scope.$broadcast('resetDisplayOrders');
          return this.$timeout(() => d.resolve()
          , 1);
        }
        , 1);
      });

      return d.promise;
    }


    /*
      * Sorts triggers into display groups
      */
    sortTriggers() {
      this.dep_triggers   = [];
      this.email_triggers = [];
      this.triggers       = [];

      this.$scope.email_trigger_ids = {};
      this.$scope.department_trigger_ids = {};
      this.$scope.satisfaction_triggers = {};

      return (() => {
        const result = [];
        for (let tr of Array.from(this.all_triggers)) {
          if (tr.department) {
            this.dep_triggers.push(tr);
            result.push(this.$scope.department_trigger_ids[tr.department.id] = tr.id);
          } else if (tr.email_account) {
            this.email_triggers.push(tr);
            result.push(this.$scope.email_trigger_ids[tr.email_account.id] = tr.id);
          } else if (tr.sys_name && tr.sys_name.match(/^default_update_satisfaction.+/)) {
            result.push(this.$scope.satisfaction_triggers[tr.sys_name.replace('default_update_satisfaction_', '')] = tr);
          } else {
            result.push(this.triggers.push(tr));
          }
        }
        return result;
      })();
    }


    /*
     * Update the enabled state of a trigger
     */
    updateTriggerEnabledState(trigger) {
      return this.dpTriggers.saveEnabledStateById(trigger.id, trigger.is_enabled);
    }

    updateDepTriggersEnabledState() {
      this.dpTriggers.saveGroupEnabledState('departments', this.depTriggersEnabled);
    }

    updateEmailTriggersEnabledState() {
      this.dpTriggers.saveGroupEnabledState('email_accounts', this.emailTriggersEnabled);
    }

    /*
     * Show the delete dlg
     */
    startTriggerDelete(trigger_id) {
      let trigger = null;
      for (let v of Array.from(this.triggers)) {
        if (v.id === trigger_id) {
          trigger = v;
          break;
        }
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketTriggers/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then( () => {
        return this.dpTriggers.deleteTriggerById(trigger.id).then(() => {
          this.sortTriggers();
          return this.$state.go('tickets.triggers', {type: this.$stateParams.type});
        });
      });
    }
  }
  Admin_TicketTriggers_Ctrl_List.initClass();

  return Admin_TicketTriggers_Ctrl_List.EXPORT_CTRL();
});