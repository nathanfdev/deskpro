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
  'DeskPRO/Util/Arrays',
  'Admin/Main/Ctrl/Base',
  'Admin/TicketTriggers/TriggerEditFormMapper',
  'Admin/TicketTriggers/Ctrl/EditBase',
], function(
  Arrays,
  Admin_Ctrl_Base,
  TriggerEditFormMapper,
  Admin_TicketTriggers_Ctrl_EditBase
) {
  class Admin_TicketWebhooks_Ctrl_TriggerEdit extends Admin_TicketTriggers_Ctrl_EditBase {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketWebhooks_Ctrl_TriggerEdit';
      this.CTRL_AS   = 'TicketTriggersEdit';
      this.DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];
    }

    init() {
      super.init(...arguments);
      this.webhookId   = this.$stateParams.webhookId;
      this.enableEventsSection = false;
      this.enableCopyFromAnotherTrigger = false;
      return this.dpWebhookTriggers = this.DataService.get('Webhooks');
    }

    getPostData() {
      const legacyPostData = super.getPostData();
      const postData = {
        title: legacyPostData.title,
        actions: {
          version:1,
          actions: legacyPostData.actions
        },
        terms: legacyPostData.criteria_sets
      };

      return postData;
    }

    onTriggerSave(trigger) {
      const { has_stop_triggers_action, has_delete_ticket_action } = this.getPostActionsDetails();

      this.trigger.title = trigger.title;
      this.trigger.has_stop_triggers_action = has_stop_triggers_action;
      this.trigger.has_delete_ticket_action = has_delete_ticket_action;

      return this.dpTriggers.mergeDataModel({
        id: this.trigger.id,
        title: this.trigger.title,
        is_enabled: this.trigger.is_enabled,
        has_stop_triggers_action,
        has_delete_ticket_action
      });
    }

    /*
     * Save the trigger
     */
    saveTrigger() {
      let promise;
      if (this.$scope.form_props.$invalid) { return; }

      this.resetErrors();
      const triggerData = this.getPostData();

      this.startSpinner('saving');
      const is_new = !this.trigger || !this.trigger.id;

      if (is_new) {
        promise = this.Api2.sendPostJson(`/${['webhooks', this.webhookId, 'triggers'].join('/')}`, triggerData);
      } else {
        promise = this.Api2.sendPutJson(`/${['webhooks', this.webhookId, 'triggers', this.trigger.id].join('/')}`, triggerData);
      }

      promise = promise.success(response => {
          const trigger = response.data;
          this.onTriggerSave(trigger);
          return trigger;
        }).success( trigger => {
          this.skipDirtyState();
          this.stopSpinner('saving', true).then( () => this.Growl.success("Saved"));
          if (is_new) {
            // close this view
            window.location.hash = '/webhooks';
            this.$state.go('tickets.webhooks');
            return this.$scope.$parent.List.onTriggerAdded(trigger, this.webhookId);
          }
      }).error( (result, code) => {
        this.stopSpinner('saving', true);
        if ((result != null ? result.error_code : undefined) === 'invalid') {
          return this.showErrors(result.error_info);
        }
      });

      return promise;
    }
  }
  Admin_TicketWebhooks_Ctrl_TriggerEdit.initClass();


  return Admin_TicketWebhooks_Ctrl_TriggerEdit.EXPORT_CTRL();
});
