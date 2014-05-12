(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Arrays', 'Admin/Main/Ctrl/Base', 'Admin/TicketTriggers/TriggerEditFormMapper'], function(Arrays, Admin_Ctrl_Base, TriggerEditFormMapper) {
    var Admin_TicketTriggers_Ctrl_Edit;
    Admin_TicketTriggers_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketTriggers_Ctrl_Edit, _super);

      function Admin_TicketTriggers_Ctrl_Edit() {
        return Admin_TicketTriggers_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketTriggers_Ctrl_Edit.CTRL_ID = 'Admin_TicketTriggers_Ctrl_Edit';

      Admin_TicketTriggers_Ctrl_Edit.CTRL_AS = 'TicketTriggersEdit';

      Admin_TicketTriggers_Ctrl_Edit.DEPS = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];

      Admin_TicketTriggers_Ctrl_Edit.prototype.init = function() {
        var with_changed_ops;
        this.triggerType = this.$stateParams.type;
        this.trigger = null;
        this.triggerId = this.$stateParams.id;
        this.options = {};
        this.editFormMapper = new TriggerEditFormMapper();
        this.$scope.form = this.editFormMapper.getFormFromModel({});
        this.$scope.triggerType = this.$stateParams.type;
        this.$scope.triggerId = this.$stateParams.id;
        with_changed_ops = true;
        if (this.$stateParams.type === 'newticket') {
          with_changed_ops = false;
          this.dpTriggers = this.DataService.get('TriggersNew');
        } else if (this.$stateParams.type === 'newreply') {
          this.dpTriggers = this.DataService.get('TriggersReply');
        } else {
          this.dpTriggers = this.DataService.get('TriggersUpdate');
        }
        this.criteraTypeDef = this.dpObTypesDefTicketCriteria;
        this.criteraTypeDef.setWithChangedOps(with_changed_ops);
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        this.$scope.criteriaOptionTypes = [];
        this.$scope.actionOptionTypes = [];
      };

      Admin_TicketTriggers_Ctrl_Edit.prototype.updateCriteriaOptionTypes = function() {
        var opt, setActionOptions, setCritOptions, types, _i, _j, _len, _len1, _results;
        types = [];
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
        setCritOptions = this.criteraTypeDef.getOptionsForTypes(types);
        this.$scope.criteriaOptionTypes.length = 0;
        for (_i = 0, _len = setCritOptions.length; _i < _len; _i++) {
          opt = setCritOptions[_i];
          this.$scope.criteriaOptionTypes.push(opt);
        }
        setActionOptions = this.actionsTypeDef.getOptionsForTypes(types, {
          dynamicOptions: this.customActions
        });
        this.$scope.actionOptionTypes.length = 0;
        _results = [];
        for (_j = 0, _len1 = setActionOptions.length; _j < _len1; _j++) {
          opt = setActionOptions[_j];
          _results.push(this.$scope.actionOptionTypes.push(opt));
        }
        return _results;
      };


      /*
      		 * Load the trigger
       */

      Admin_TicketTriggers_Ctrl_Edit.prototype.initialLoad = function() {
        var get, promise, promise2;
        get = {
          customActions: '/ticket_triggers/get-custom-actions'
        };
        if (this.triggerId) {
          get.trigger = "/ticket_triggers/" + this.triggerId;
        }
        promise = this.Api.sendDataGet(get).then((function(_this) {
          return function(result) {
            _this.customActions = result.data.customActions.action_defs;
            if (_this.triggerId) {
              _this.trigger = result.data.trigger.trigger;
            } else {
              _this.trigger = {};
            }
            return _this.$scope.form = _this.editFormMapper.getFormFromModel(_this.trigger);
          };
        })(this));
        promise2 = this.criteraTypeDef.loadDataOptions().then((function(_this) {
          return function() {
            _this.updateCriteriaOptionTypes();
            return _this.$scope.$watch('form.typeForm', function() {
              return _this.updateCriteriaOptionTypes();
            }, true);
          };
        })(this));
        return this.$q.all([promise, promise2]);
      };


      /*
      		 * Save the trigger
       */

      Admin_TicketTriggers_Ctrl_Edit.prototype.saveTrigger = function() {
        var act, crit, crit_set, enabled, is_new, mode, postData, promise, set, _, _ref, _ref1, _ref2, _ref3;
        postData = {
          title: this.$scope.form.title,
          event_trigger: this.triggerType,
          by_user_mode: [],
          by_agent_mode: [],
          criteria_sets: [],
          actions: []
        };
        if (this.$scope.form.typeForm.by_user) {
          _ref = this.$scope.form.typeForm.by_user_mode;
          for (mode in _ref) {
            if (!__hasProp.call(_ref, mode)) continue;
            enabled = _ref[mode];
            if (enabled) {
              postData.by_user_mode.push(mode);
            }
          }
        }
        if (this.$scope.form.typeForm.by_agent) {
          _ref1 = this.$scope.form.typeForm.by_agent_mode;
          for (mode in _ref1) {
            if (!__hasProp.call(_ref1, mode)) continue;
            enabled = _ref1[mode];
            if (enabled) {
              postData.by_agent_mode.push(mode);
            }
          }
        }
        _ref2 = this.$scope.form.terms_set;
        for (_ in _ref2) {
          if (!__hasProp.call(_ref2, _)) continue;
          crit_set = _ref2[_];
          set = [];
          for (_ in crit_set) {
            if (!__hasProp.call(crit_set, _)) continue;
            crit = crit_set[_];
            if (crit.type) {
              set.push(crit);
            }
          }
          if (set.length) {
            postData.criteria_sets.push(set);
          }
        }
        if (this.$scope.form.actions) {
          _ref3 = this.$scope.form.actions;
          for (_ in _ref3) {
            if (!__hasProp.call(_ref3, _)) continue;
            act = _ref3[_];
            if (act.type) {
              postData.actions.push(act);
            }
          }
        }
        this.startSpinner('saving');
        if (this.trigger.id) {
          is_new = false;
          promise = this.Api.sendPostJson('/ticket_triggers/' + this.trigger.id, postData);
        } else {
          is_new = true;
          promise = this.Api.sendPutJson('/ticket_triggers', postData);
        }
        promise.success((function(_this) {
          return function(result) {
            _this.trigger.id = result.trigger_id;
            if (is_new) {
              _this.trigger.is_enabled = true;
            }
            _this.trigger.title = postData.title;
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success("Saved");
            });
            _this.dpTriggers.mergeDataModel({
              id: _this.trigger.id,
              title: _this.trigger.title
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('tickets.triggers.gocreate');
            }
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
        return promise;
      };

      return Admin_TicketTriggers_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
