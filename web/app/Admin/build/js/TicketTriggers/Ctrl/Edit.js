(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Arrays', 'Admin/Main/Ctrl/Base', 'Admin/Main/Model/DepAgentPermMatrix'], function(Arrays, Admin_Ctrl_Base, Admin_Main_Model_DepAgentPermMatrix) {
    var Admin_TicketTriggers_Ctrl_Edit, _ref;
    Admin_TicketTriggers_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketTriggers_Ctrl_Edit, _super);

      function Admin_TicketTriggers_Ctrl_Edit() {
        _ref = Admin_TicketTriggers_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketTriggers_Ctrl_Edit.CTRL_ID = 'Admin_TicketTriggers_Ctrl_Edit';

      Admin_TicketTriggers_Ctrl_Edit.CTRL_AS = 'TicketTriggersEdit';

      Admin_TicketTriggers_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketTriggers_Ctrl_Edit.DEPS = ['em', '$stateParams', 'dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions', 'TriggersNew', 'TriggersReply', 'TriggersUpdate'];

      Admin_TicketTriggers_Ctrl_Edit.prototype.init = function() {
        var _this = this;
        this.triggerType = this.$stateParams.type;
        this.trigger = null;
        this.triggerId = this.$stateParams.id;
        this.options = {};
        this.$scope.triggerType = this.$stateParams.type;
        this.$scope.triggerId = this.$stateParams.id;
        if (this.$stateParams.type === 'newticket') {
          this.dpTriggers = this.TriggersNew;
        } else if (this.$stateParams.type === 'newreply') {
          this.dpTriggers = this.TriggersReply;
        } else {
          this.dpTriggers = this.TriggersUpdate;
        }
        this.$scope.typeForm = {
          by_user: true,
          by_agent: false,
          by_agent_mode: {
            web: true,
            email: true,
            api: true
          },
          by_user_mode: {
            portal: true,
            widget: true,
            form: true,
            email: true,
            api: true
          }
        };
        this.criteraTypeDef = this.dpObTypesDefTicketCriteria;
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        this.$scope.criteriaOptionTypes = [];
        this.$scope.actionOptionTypes = [];
        this.updateCriteriaOptionTypes();
        this.$scope.trigger_criteria_set = {
          first: {}
        };
        this.$scope.trigger_actions = [];
        this.$scope.$watch('typeForm', function() {
          return _this.updateCriteriaOptionTypes();
        }, true);
      };

      Admin_TicketTriggers_Ctrl_Edit.prototype.updateCriteriaOptionTypes = function() {
        var opt, setActionOptions, setCritOptions, types, _i, _j, _len, _len1, _results;
        types = [];
        if (this.$scope.typeForm.by_user) {
          if (this.$scope.typeForm.by_user_mode.portal || this.$scope.typeForm.by_user_mode.widget || this.$scope.typeForm.by_user_mode.form) {
            Arrays.pushUnique(types, 'web');
            Arrays.pushUnique(types, 'web.user');
          }
          if (this.$scope.typeForm.by_user_mode.email) {
            Arrays.pushUnique(types, 'email');
            Arrays.pushUnique(types, 'email.user');
          }
          if (this.$scope.typeForm.by_user_mode.api) {
            Arrays.pushUnique(types, 'api');
            Arrays.pushUnique(types, 'api.user');
          }
        }
        if (this.$scope.typeForm.by_agent) {
          if (this.$scope.typeForm.by_agent_mode.web) {
            Arrays.pushUnique(types, 'web');
            Arrays.pushUnique(types, 'web.agent');
          }
          if (this.$scope.typeForm.by_agent_mode.email) {
            Arrays.pushUnique(types, 'email');
            Arrays.pushUnique(types, 'email.agent');
          }
          if (this.$scope.typeForm.by_agent_mode.api) {
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
        setActionOptions = this.actionsTypeDef.getOptionsForTypes(types);
        this.$scope.actionOptionTypes.length = 0;
        _results = [];
        for (_j = 0, _len1 = setActionOptions.length; _j < _len1; _j++) {
          opt = setActionOptions[_j];
          _results.push(this.$scope.actionOptionTypes.push(opt));
        }
        return _results;
      };

      /*
      		# Load the trigger
      */


      Admin_TicketTriggers_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.triggerId) {
          promise = this.dpTriggers.loadTrigger(this.triggerId).then(function(trigger) {
            var x, _i, _j, _len, _len1, _ref1, _ref2, _results;
            _this.trigger = trigger;
            _this.$scope.form = {
              title: _this.trigger.title
            };
            if (_this.trigger.by_agent_mode.length) {
              _this.$scope.typeForm.by_agent = true;
              _ref1 = _this.trigger.by_agent_mode;
              for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
                x = _ref1[_i];
                _this.$scope.typeForm.by_agent_mode[x] = true;
              }
            }
            if (_this.trigger.by_user_mode.length) {
              _this.$scope.typeForm.by_user = true;
              _ref2 = _this.trigger.by_user_mode;
              _results = [];
              for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
                x = _ref2[_j];
                _results.push(_this.$scope.typeForm.by_user_mode[x] = true);
              }
              return _results;
            }
          });
          return promise;
        } else {
          this.trigger = {};
          this.$scope.form = {
            title: ''
          };
        }
        return null;
      };

      /*
      		# Save the trigger
      */


      Admin_TicketTriggers_Ctrl_Edit.prototype.saveTrigger = function() {
        var act, crit, crit_set, enabled, is_new, mode, postData, promise, set, _, _ref1, _ref2, _ref3, _ref4,
          _this = this;
        postData = {
          title: this.$scope.form.title,
          event_trigger: this.triggerType,
          by_user_mode: [],
          by_agent_mode: [],
          criteria_sets: [],
          actions: []
        };
        if (this.$scope.typeForm.by_user) {
          _ref1 = this.$scope.typeForm.by_user_mode;
          for (mode in _ref1) {
            if (!__hasProp.call(_ref1, mode)) continue;
            enabled = _ref1[mode];
            if (enabled) {
              postData.by_user_mode.push(mode);
            }
          }
        }
        if (this.$scope.typeForm.by_agent) {
          _ref2 = this.$scope.typeForm.by_agent_mode;
          for (mode in _ref2) {
            if (!__hasProp.call(_ref2, mode)) continue;
            enabled = _ref2[mode];
            if (enabled) {
              postData.by_agent_mode.push(mode);
            }
          }
        }
        _ref3 = this.$scope.trigger_criteria_set;
        for (_ in _ref3) {
          if (!__hasProp.call(_ref3, _)) continue;
          crit_set = _ref3[_];
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
        if (this.$scope.trigger_actions) {
          _ref4 = this.$scope.trigger_actions;
          for (_ in _ref4) {
            if (!__hasProp.call(_ref4, _)) continue;
            act = _ref4[_];
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
        promise.success(function(result) {
          _this.trigger.id = result.id;
          if (is_new) {
            _this.trigger.is_enabled = true;
          }
          _this.trigger.title = postData.title;
          _this.stopSpinner('saving', true).then(function() {
            return _this.Growl.success("Saved");
          });
          if (is_new) {
            _this.dpTriggers.addTriggerModel(trigger);
          } else {
            _this.dpTriggers.updateTriggerModel(trigger);
          }
          _this.skipDirtyState();
          if (is_new) {
            return _this.$state.go('tickets.ticket_triggers.gocreate');
          } else {
            return _this.$state.go('tickets.ticket_triggers');
          }
        });
        promise.error(function(info, code) {
          _this.stopSpinner('saving', true);
          return _this.applyErrorResponseToView(info);
        });
        return promise;
      };

      return Admin_TicketTriggers_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/