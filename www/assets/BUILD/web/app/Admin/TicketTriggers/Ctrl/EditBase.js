// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Arrays',
  'Admin/Main/Ctrl/Base',
  'Admin/TicketTriggers/TriggerEditFormMapper',
], function(
  Arrays,
  Admin_Ctrl_Base,
  TriggerEditFormMapper
) {
  let Admin_TicketTriggers_Ctrl_EditBase;
  return Admin_TicketTriggers_Ctrl_EditBase = (function() {
    Admin_TicketTriggers_Ctrl_EditBase = class Admin_TicketTriggers_Ctrl_EditBase extends Admin_Ctrl_Base {
      static initClass() {
        this.CTRL_ID   = 'Admin_TicketTriggers_Ctrl_Edit';
        this.CTRL_AS   = 'TicketTriggersEdit';
        this.DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];
      }

      init() {
        this.triggerType = this.$stateParams.type;
        this.trigger     = null;
        this.triggerId   = this.$stateParams.id;
        this.options     = {};
        this.editFormMapper = new TriggerEditFormMapper();
        this.mode = null;
        this.appTriggerEvents = [];
        this.allTriggers = [];

        // by default we want to enable the trigger events section
        this.enableEventsSection = true;
        // by default we want to allow copying from another trigger
        this.enableCopyFromAnotherTrigger = true;

        this.$scope.form = this.editFormMapper.getFormFromModel({});

        this.$scope.triggerType = this.$stateParams.type;
        this.$scope.triggerId   = this.$stateParams.id;

        let with_changed_ops = true;
        switch (this.$stateParams.type) {
          case 'newticket':
            this.mode = 'TriggersNew';
            with_changed_ops = false;
            break;
          case 'newreply':
            this.mode = 'TriggersReply';
            break;
          default:
            this.mode = 'TriggersUpdate';
        }

        this.dpTriggers = this.DataService.get(this.mode);

        this.criteraTypeDef = this.dpObTypesDefTicketCriteria;
        this.criteraTypeDef.setWithChangedOps(with_changed_ops);

        this.actionsTypeDef = this.dpObTypesDefTicketActions;

        this.$scope.criteriaOptionTypes = [];
        this.$scope.actionOptionTypes = [];

        this.criteraTypeDef.setVar('object_type', 'trigger');
        this.actionsTypeDef.setVar('object_type', 'trigger');

        this.dpTriggers.loadList().then( list => { return this.allTriggers = list; });

        this.customInit();
      }

      customInit() { return null; }

      updateCriteriaOptionTypes() {
        const types = [];

        if (this.$scope.form.typeForm.by_user) {
          if (this.$scope.form.typeForm.by_user_mode.portal || this.$scope.form.typeForm.by_user_mode.widget || this.$scope.form.typeForm.by_user_mode.form) {
            Arrays.pushUnique(types, 'web');
            Arrays.pushUnique(types, 'web.user');
          }
          if (this.$scope.form.typeForm.by_user_mode.email) {
            Arrays.pushUnique(types, 'email');
            Arrays.pushUnique(types, 'email.user');
          }
          if (this.$scope.form.typeForm.by_user_mode.api) {
            Arrays.pushUnique(types, 'api');
            Arrays.pushUnique(types, 'api.user');
          }
        }

        if (this.$scope.form.typeForm.by_agent) {
          if (this.$scope.form.typeForm.by_agent_mode.web) {
            Arrays.pushUnique(types, 'web');
            Arrays.pushUnique(types, 'web.agent');
          }
          if (this.$scope.form.typeForm.by_agent_mode.email) {
            Arrays.pushUnique(types, 'email');
            Arrays.pushUnique(types, 'email.agent');
          }
          if (this.$scope.form.typeForm.by_agent_mode.api) {
            Arrays.pushUnique(types, 'api');
            Arrays.pushUnique(types, 'api.agent');
          }
        }

        const setCritOptions = this.criteraTypeDef.getOptionsForTypes(types, null, this.mode);
        this.$scope.criteriaOptionTypes.length = 0;
        for (var opt of Array.from(setCritOptions)) {
          this.$scope.criteriaOptionTypes.push(opt);
        }

        const setActionOptions = this.actionsTypeDef.getOptionsForTypes(types, { dynamicOptions: this.customActions }, this.mode);
        this.$scope.actionOptionTypes.length = 0;
        for (opt of Array.from(setActionOptions)) {
          this.$scope.actionOptionTypes.push(opt);
        }

        // filter out usergroup 'Everyone'
        // doesn't make sense to use it in Triggers
        const { options_data } = this.criteraTypeDef;
        if (options_data != null ? options_data.usergroups : undefined) {
          return options_data.usergroups = options_data.usergroups.filter(group => group.sys_name !== 'everyone');
        }
      }

      /*
       * Load the trigger
       */
      initialLoad() {
        const get = {
          customActions: '/ticket_triggers/get-custom-actions'
        };

        if (this.mode === 'TriggersUpdate') {
          get.appEvents = '/ticket_triggers/app-events/update';
        }

        if (this.triggerId) {
          get.trigger = `/ticket_triggers/${this.triggerId}`;
        }

        const promise = this.Api.sendDataGet(get).then( result => {

          this.customActions = result.data.customActions.action_defs;

          if (__guard__(result.data.appEvents != null ? result.data.appEvents.app_events : undefined, x => x.length)) {
            this.appTriggerEvents = result.data.appEvents.app_events;
          }

          if (__guard__(result.data != null ? result.data.trigger : undefined, x1 => x1.trigger) != null) {
            this.trigger = result.data.trigger.trigger;
            this.triggerId = this.trigger.id;
          } else {
            this.trigger = {};
            this.triggerId = 0;
          }

          return this.$scope.form = this.editFormMapper.getFormFromModel(this.trigger);
        });

        const promise2 = this.criteraTypeDef.loadDataOptions();
        const promise3 = this.actionsTypeDef.loadDataOptions();

        const promises = [promise, promise2, promise3];

        return this.$q.all(promises).then(() => {
          this.updateCriteriaOptionTypes();

          return this.$scope.$watch('form.typeForm', () => {
            return this.updateCriteriaOptionTypes();
          }
          , true);
        });
      }

      getPostActionsDetails() {

        let has_stop_triggers_action = false;
        let has_delete_ticket_action = false;
        if (this.$scope.form.actions) {
          for (let _x of Object.keys(this.$scope.form.actions || {})) {
            const act = this.$scope.form.actions[_x];
            if (act.type === 'ModStopTriggers') {
              has_stop_triggers_action = true;
            }
            if (act.type === 'SetDeleted') {
              has_delete_ticket_action = true;
            }
          }
        }

        return {
          has_stop_triggers_action: false,
          has_delete_ticket_action: false,
        };
      }

      getPostData() {
        let enabled, mode;
        this.resetErrors();

        const postData = {
          title:         this.$scope.form.title,
          event_trigger: this.triggerType,
          flags:         this.$scope.form.flags,
          by_user_mode:  [],
          by_agent_mode: [],
          by_app_mode:   [],
          criteria_sets: [],
          actions:       [],
        };

        if (this.$scope.form.typeForm.by_user) {
          for (mode of Object.keys(this.$scope.form.typeForm.by_user_mode || {})) {
            enabled = this.$scope.form.typeForm.by_user_mode[mode];
            if (enabled) {
              postData.by_user_mode.push(mode);
            }
          }
        }
        if (this.$scope.form.typeForm.by_agent) {
          for (mode of Object.keys(this.$scope.form.typeForm.by_agent_mode || {})) {
            enabled = this.$scope.form.typeForm.by_agent_mode[mode];
            if (enabled) {
              postData.by_agent_mode.push(mode);
            }
          }
        }
        if (this.$scope.form.typeForm.by_app) {
          for (mode of Object.keys(this.$scope.form.typeForm.by_app_mode || {})) {
            enabled = this.$scope.form.typeForm.by_app_mode[mode];
            if (enabled) {
              postData.by_app_mode.push(mode);
            }
          }
        }

        for (var _x of Object.keys(this.$scope.form.terms_set || {})) {
          const crit_set = this.$scope.form.terms_set[_x];
          const set = [];
          for (_x of Object.keys(crit_set || {})) {
            const crit = crit_set[_x];
            if (crit.type) {
              set.push(crit);
            }
          }
          if (set.length) {
            postData.criteria_sets.push(set);
          }
        }

        if (this.$scope.form.actions) {
          for (_x of Object.keys(this.$scope.form.actions || {})) {
            const act = this.$scope.form.actions[_x];
            if (act.type) {
              postData.actions.push(act);
            }
          }
        }

        return postData;
      }


      /*
       * Save the trigger
       */
      saveTrigger() {
        let is_new, promise;
        if (this.$scope.form_props.$invalid) { return; }

        this.resetErrors();

        const postData = this.getPostData();
        const { has_stop_triggers_action, has_delete_ticket_action } = this.getPostActionsDetails();

        this.startSpinner('saving');
        if (this.trigger.id) {
          is_new = false;
          promise = this.Api.sendPostJson(`/ticket_triggers/${this.trigger.id}`, postData);
        } else {
          is_new = true;
          promise = this.Api.sendPutJson('/ticket_triggers', postData);
        }

        promise.success( result => {
          this.trigger.id = result.trigger_id;

          if (is_new) {
            this.trigger.is_enabled = true;
          }

          this.trigger.title = postData.title;
          this.trigger.has_stop_triggers_action = has_stop_triggers_action;
          this.trigger.has_delete_ticket_action = has_delete_ticket_action;

          this.stopSpinner('saving', true).then(() => {
            return this.Growl.success("Saved");
          });

          this.dpTriggers.mergeDataModel({
            id: this.trigger.id,
            title: this.trigger.title,
            is_enabled: this.trigger.is_enabled,
            has_stop_triggers_action,
            has_delete_ticket_action
          });

          this.skipDirtyState();
          if (is_new) {
            return this.$state.go('tickets.triggers.gocreate');
          }
        });
        promise.error( (result, code) => {
          this.stopSpinner('saving', true);
          if ((result != null ? result.error_code : undefined) === 'invalid') {
            return this.showErrors(result.error_info);
          }
        });

        return promise;
      }

      findCriteriaTypeTitle(type) {
        let option_title = null;
        for (let v of Array.from(this.$scope.criteriaOptionTypes)) {
          if (v.subOptions) {
            for (let sb of Array.from(v.subOptions)) {
              if (type === sb.value) {
                option_title = sb.title;
                break;
              }
            }
          } else {
            if (type === v.value) {
              option_title = v.title;
            }
          }
          if (option_title) { break; }
        }

        return option_title || type;
      }

      findActionTypeTitle(type) {
        let option_title = null;
        for (let v of Array.from(this.$scope.actionOptionTypes)) {
          if (v.subOptions) {
            for (let sb of Array.from(v.subOptions)) {
              if (type === sb.value) {
                option_title = sb.title;
                break;
              }
            }
          } else {
            if (type === v.value) {
              option_title = v.title;
            }
          }
          if (option_title) { break; }
        }

        return option_title || type;
      }

      resetErrors() {
        this.$scope.show_errors = false;
        this.$scope.criteria_errors = null;
        return this.$scope.action_errors = null;
      }

      showErrors(errors) {
        let t;
        this.$scope.show_errors = true;

        if (errors.criteria != null ? errors.criteria.length : undefined) {
          this.$scope.criteria_errors = [];
          for (t of Array.from(errors.criteria)) {
            this.$scope.criteria_errors.push(this.findCriteriaTypeTitle(t));
          }
        }

        if (errors.actions != null ? errors.actions.length : undefined) {
          this.$scope.actions_errors = [];
          return (() => {
            const result = [];
            for (t of Array.from(errors.actions)) {
              result.push(this.$scope.actions_errors.push(this.findActionTypeTitle(t)));
            }
            return result;
          })();
        }
      }

      showCopySettings() {
        const { allTriggers } = this;
        const mode        = this.$stateParams.type;
        const inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketTriggers/copy-trigger-modal.html'),
          controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
            $scope.dismiss = () => $modalInstance.dismiss();

            $scope.doCopySettings = () => $modalInstance.close($scope.triggerId);

            $scope.triggerId      = 0;
            switch (mode) {
              case 'newticket':
                $scope.title = 'New Ticket';
                break;
              case 'newreply':
                $scope.title = 'New Reply';
                break;
              default:
                $scope.title = 'Ticket Update';
            }
            return $scope.allTriggers    = allTriggers;
          }
          ]
        });

        return inst.result.then(triggerId => {
          return this.copyTrigger(triggerId);
        });
      }

      copyTrigger(triggerId) {

        const promise = this.Api.sendGet(`/ticket_triggers/${triggerId}`).then( result => {

          const triggerToCopy = result.data.trigger;
          triggerToCopy.id = this.$scope.form.id;
          triggerToCopy.title = (this.$scope.form.title != null) ? this.$scope.form.title : triggerToCopy.title;
          return this.$scope.form = this.editFormMapper.getFormFromModel(triggerToCopy, true);
        });

        const promise2 = this.criteraTypeDef.loadDataOptions();
        const promise3 = this.actionsTypeDef.loadDataOptions();

        const promises = [promise, promise2, promise3];

        return this.$q.all(promises).then(() => {
          return this.updateCriteriaOptionTypes();
        });
      }
    };
    Admin_TicketTriggers_Ctrl_EditBase.initClass();
    return Admin_TicketTriggers_Ctrl_EditBase;
  })();
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}