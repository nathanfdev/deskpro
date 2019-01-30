// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'Admin/Main/Collection/OrderedDictionary',
], function(
  Admin_Ctrl_Base,
  OrderedDictionary
) {
  class Admin_TicketWebhooks_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketWebhooks_Ctrl_List';
      this.CTRL_AS = 'List';
      this.DEPS = ['$state', '$stateParams', '$q', 'TicketAccountsData', '$timeout'];
    }

    init() {
      this.webhooks        = [];
      this.dpWebhooks = this.DataService.get('Webhooks');
      return this.dpTriggers = this.DataService.get('TriggersNew');
    }

    /*
     * Loads the triggers list
     */
    initialLoad() {
      this.dpWebhooks.loadList().then( list => { return this.webhooks = list; });
    }

    onWebhookAdded(webhook) {
      this.dpWebhooks.mergeDataModel(webhook);
      return this.dpWebhooks.loadList().then(
        list => {
          return this.webhooks = [].concat(list);
      });
    }

    /*
      * Sorts triggers into display groups
      */
    sortTriggers() {
      return null;
    }

    addTrigger(webhook) {
      this.$state.go('tickets.webhooks.trigger-create', { webhookId: webhook.id });
    }

    onTriggerAdded(trigger, webhookId) {
      this.dpWebhooks.loadList(true).then(
        list => {
          this.webhooks = [].concat(list);
          return this.$state.go('tickets.webhooks.trigger-edit', {webhookId, id: trigger.data.id});
      });
    }

    changeEnabledStatus(webhook) {
      webhook.is_enabled = webhook.is_enabled != null ? webhook.is_enabled : {false : true};

      this.dpWebhooks.set(webhook).then(
        wh => {
          return this.dpWebhooks.loadList().then(
            list => {
              return this.webhooks = [].concat(list);
          });
      });
    }

    /*
     * Update the enabled state of a trigger
     */
    changeEnabledStatusTrigger(trigger) {
      return this.dpTriggers.saveEnabledStateById(trigger.id, trigger.is_enabled);
    }

    /*
     * Show the delete dlg
     */
    startTriggerDelete(trigger_id) {

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketTriggers/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then( () => {
        return this.dpWebhooks.deleteTriggerById(trigger_id).then(() => {
          return this.dpWebhooks.loadList().then(
            list => {
              this.webhooks = [].concat(list);
              return this.$state.go('tickets.webhooks');
          });
        });
      });
    }

    startDelete(webhookId) {
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketWebhooks/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then( () => {
        const model = this.dpWebhooks.removeListModelById(webhookId);
        return this.dpWebhooks.remove(model).then(() => {
          return this.dpWebhooks.loadList().then(
            list => {
              this.webhooks = [].concat(list);
              return this.$state.go('tickets.webhooks');
          }).catch(err => {
            return console.log('err ', err);
          });
        });
      });
    }
  }
  Admin_TicketWebhooks_Ctrl_List.initClass();



  return Admin_TicketWebhooks_Ctrl_List.EXPORT_CTRL();
});
