(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/OptionBuilder/TypesDef/BaseActionTypesDef', 'DeskPRO/Util/Numbers'], function(BaseActionTypesDef, Numbers) {
    var Admin_OptionBuilder_TypesDef_TicketFilter;
    return Admin_OptionBuilder_TypesDef_TicketFilter = (function(_super) {
      __extends(Admin_OptionBuilder_TypesDef_TicketFilter, _super);

      function Admin_OptionBuilder_TypesDef_TicketFilter() {
        return Admin_OptionBuilder_TypesDef_TicketFilter.__super__.constructor.apply(this, arguments);
      }

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.init = function() {
        return this.options_data = null;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getOptionsForTypes = function(types, typesData, mode) {
        var f, opt, options, set_options, typeFunc, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref10, _ref11, _ref12, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9;
        if (types == null) {
          types = [];
        }
        if (typesData == null) {
          typesData = null;
        }
        set_options = [];
        options = [];
        options.push({
          title: 'Set Assigned Agent',
          value: 'SetAgent'
        });
        if ((((_ref = this.options_data) != null ? _ref.round_robin : void 0) != null) && ((_ref1 = this.options_data) != null ? _ref1.round_robin.enabled : void 0)) {
          options.push({
            title: 'Set Assigned Agent from Round Robin',
            value: 'SetRoundRobin'
          });
        }
        options.push({
          title: 'Set Assigned Team',
          value: 'SetAgentTeam'
        });
        options.push({
          title: 'Set Agent Followers',
          value: 'SetAgentFollowers'
        });
        set_options.push({
          title: 'Ticket Assignment',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Set Status',
          value: 'SetStatus'
        });
        options.push({
          title: 'Set Department',
          value: 'SetDepartment'
        });
        options.push({
          title: 'Set Product',
          value: 'SetProduct'
        });
        options.push({
          title: 'Set Category',
          value: 'SetCategory'
        });
        options.push({
          title: 'Set Priority',
          value: 'SetPriority'
        });
        options.push({
          title: 'Set Workflow',
          value: 'SetWorkflow'
        });
        options.push({
          title: 'Set Language',
          value: 'SetLanguage'
        });
        options.push({
          title: 'Set Urgency',
          value: 'SetUrgency'
        });
        options.push({
          title: 'Set Subject',
          value: 'SetSubject'
        });
        options.push({
          title: 'Set Labels',
          value: 'SetLabels'
        });
        options.push({
          title: 'Set Flag',
          value: 'SetFlag'
        });
        options.push({
          title: 'Set Email Account',
          value: 'SetEmailAccount'
        });
        options.push({
          title: 'Set CC\'d Users',
          value: 'SetCcs'
        });
        set_options.push({
          title: 'Ticket Properties',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Set SLAs',
          value: 'SetSlas'
        });
        options.push({
          title: 'Complete SLAs',
          value: 'SetSlasComplete'
        });
        set_options.push({
          title: 'Ticket SLAs',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Set Ticket User',
          value: 'SetUserOwner'
        });
        options.push({
          title: 'Delete Ticket',
          value: 'SetDeleted'
        });
        options.push({
          title: 'Add Agent Reply',
          value: 'AddAgentReply'
        });
        options.push({
          title: 'Add Agent Note',
          value: 'AddAgentNote'
        });
        options.push({
          title: 'Require User Email Validation',
          value: 'SetRequireValidation'
        });
        options.push({
          title: 'Set Hold',
          value: 'SetHold'
        });
        options.push({
          title: 'Call Web Hook',
          value: 'WebHook'
        });
        set_options.push({
          title: 'Ticket Actions',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Send Email To User',
          value: 'SendUserEmail'
        });
        options.push({
          title: 'Send Email To Agents',
          value: 'SendAgentEmail'
        });
        options.push({
          title: 'Send Email to a specific email address',
          value: 'SendSpecificUserEmail'
        });
        set_options.push({
          title: 'Send Email',
          subOptions: options
        });
        if (((_ref2 = this.options_data) != null ? (_ref3 = _ref2.jira_settings) != null ? _ref3.enabled : void 0 : void 0) && 'TriggersUpdate' === mode) {
          options = [];
          options.push({
            title: 'Add Comment to linked JIRA issues',
            value: 'AddJIRAComment'
          });
          set_options.push({
            title: 'JIRA Actions',
            subOptions: options
          });
        }
        options = [];
        options.push({
          title: 'Stop Processing Triggers',
          value: 'ModStopTriggers'
        });
        options.push({
          title: 'Prevent Emails To User',
          value: 'ModMuteUserEmails'
        });
        options.push({
          title: 'Prevent Emails To Agents',
          value: 'ModMuteAgentEmails'
        });
        options.push({
          title: 'Force Agent Email Subscriptions',
          value: 'ModForceAgentEmails'
        });
        options.push({
          title: 'Set Trigger Variable',
          value: 'ModSetUserVar'
        });
        options.push({
          title: 'Ticket Log',
          value: 'TicketLogText'
        });
        set_options.push({
          title: 'Trigger Control',
          subOptions: options
        });
        options = [];
        if ((_ref4 = this.options_data) != null ? _ref4.ticket_fields : void 0) {
          _ref5 = this.options_data.ticket_fields;
          for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
            f = _ref5[_i];
            options.push({
              title: f.title,
              value: this.initFieldGetter('SetTicketField', f)
            });
          }
        }
        if ((_ref6 = this.options_data) != null ? _ref6.contextual_fields : void 0) {
          _ref7 = this.options_data.contextual_fields;
          for (_j = 0, _len1 = _ref7.length; _j < _len1; _j++) {
            f = _ref7[_j];
            options.push({
              title: f.title,
              value: this.initFieldGetter('SetTicketContextualField', f)
            });
          }
        }
        if (options.length) {
          set_options.push({
            title: 'Ticket Fields',
            subOptions: options
          });
        }
        if ((_ref8 = this.options_data) != null ? _ref8.user_fields : void 0) {
          options = [];
          _ref9 = this.options_data.user_fields;
          for (_k = 0, _len2 = _ref9.length; _k < _len2; _k++) {
            f = _ref9[_k];
            options.push({
              title: f.title,
              value: this.initFieldGetter('SetUserField', f)
            });
          }
          if (options.length) {
            set_options.push({
              title: 'Person Fields',
              subOptions: options
            });
          }
        }
        if ((_ref10 = this.options_data) != null ? (_ref11 = _ref10.tasks) != null ? _ref11.enabled : void 0 : void 0) {
          options = [];
          options.push({
            title: 'Create Task',
            value: 'CreateTask'
          });
          set_options.push({
            title: 'Tasks',
            subOptions: options
          });
        }
        if ((typesData != null ? typesData.dynamicOptions : void 0) != null) {
          options = [];
          _ref12 = typesData.dynamicOptions;
          for (_l = 0, _len3 = _ref12.length; _l < _len3; _l++) {
            opt = _ref12[_l];
            options.push({
              title: opt.action_title,
              value: opt.action_name
            });
            typeFunc = "get" + opt.action_name;
            if (opt.action_name.indexOf('Sms') === 0) {
              this[typeFunc] = this.generateSmsAction(opt.app.title, opt);
            } else {
              this[typeFunc] = function(options) {
                var me;
                if (options == null) {
                  options = {};
                }
                me = this;
                return {
                  getTemplate: function() {
                    return me.dpTemplateManager.get(opt.builder_template);
                  },
                  getData: function() {
                    return {};
                  },
                  getDataFormatter: function() {
                    return {
                      getViewValue: function(value, data) {
                        if (value == null) {
                          value = {};
                        }
                        return value.options || {};
                      },
                      getValue: function(model, data) {
                        var value;
                        if (model == null) {
                          model = {};
                        }
                        value = {};
                        value.type = opt.action_name;
                        value.options = model || {};
                        return value;
                      }
                    };
                  }
                };
              };
            }
          }
          if (options.length) {
            set_options.push({
              title: 'Other Actions',
              subOptions: options
            });
          }
        }
        return set_options;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.generateSmsAction = function(app_title, opt) {
        return function(options) {
          var me;
          if (options == null) {
            options = {};
          }
          me = this;
          return {
            scopeInit: [
              '$scope', function($scope) {
                $scope.sms_num_characters = 0;
                $scope.sms_vars = [];
                $scope.sms_app_name = app_title;
                me = this;
                return $scope.calculateCharacterLength = function() {
                  var countable_string, matches, proposed_length, tmp_string;
                  tmp_string = $scope.model.message;
                  matches = tmp_string.match(/(\{\{.*?\}\})/gi);
                  countable_string = tmp_string.replace(/(\{\{.*?\}\})/gi, '!');
                  if (matches) {
                    proposed_length = countable_string.length - matches.length;
                  } else {
                    proposed_length = countable_string.length;
                  }
                  if (proposed_length < 0) {
                    proposed_length = 0;
                  }
                  $scope.sms_num_characters = proposed_length;
                  return $scope.sms_vars = matches || [];
                };
              }
            ],
            getTemplate: function() {
              return me.dpTemplateManager.get('OptionBuilder/type-actions-set-sms.html');
            },
            getData: function() {
              return me.loadDataOptions();
            },
            getDataFormatter: function() {
              return {
                getViewValue: function(value, data) {
                  var agent_ids, aid, department_ids, did, team_ids, tid, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2;
                  if (value == null) {
                    value = {};
                  }
                  options = value.options || {};
                  department_ids = {};
                  if (options.department_ids) {
                    _ref = options.department_ids;
                    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                      did = _ref[_i];
                      did = parseInt(did);
                      department_ids[did] = true;
                    }
                  }
                  agent_ids = {};
                  if (options.agent_ids) {
                    _ref1 = options.agent_ids;
                    for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                      aid = _ref1[_j];
                      if (aid !== 'followers' && aid !== 'assigned') {
                        aid = parseInt(aid);
                      }
                      agent_ids[aid] = true;
                    }
                  } else {
                    agent_ids['followers'] = false;
                    agent_ids['assigned'] = false;
                  }
                  team_ids = {};
                  if (options.agent_teams) {
                    _ref2 = options.agent_teams;
                    for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
                      tid = _ref2[_k];
                      if (tid !== 'assigned') {
                        tid = parseInt(tid);
                      }
                      team_ids[tid] = true;
                    }
                  } else {
                    team_ids['assigned'] = false;
                  }
                  return {
                    agents: options.agents || [],
                    agent_ids: agent_ids,
                    agent_teams: team_ids,
                    department_ids: department_ids,
                    to_number: options.to_number || '',
                    message: options.message || ''
                  };
                },
                getValue: function(model, data) {
                  var k, v, value, _ref, _ref1, _ref2;
                  if (model == null) {
                    model = {};
                  }
                  options = {
                    agents: model.agents || [],
                    agent_teams: [],
                    department_ids: [],
                    to_number: model.to_number || '',
                    message: model.message || '',
                    agent_ids: []
                  };
                  if (model.department_ids) {
                    _ref = model.department_ids;
                    for (k in _ref) {
                      if (!__hasProp.call(_ref, k)) continue;
                      v = _ref[k];
                      if (v) {
                        options.department_ids.push(parseInt(k));
                      }
                    }
                  }
                  if (model.agent_ids) {
                    _ref1 = model.agent_ids;
                    for (k in _ref1) {
                      if (!__hasProp.call(_ref1, k)) continue;
                      v = _ref1[k];
                      if (v) {
                        if (k === 'assigned') {
                          options.agent_ids.push('assigned');
                        } else if (k === 'followers') {
                          options.agent_ids.push('followers');
                        } else {
                          options.agent_ids.push(parseInt(k));
                        }
                      }
                    }
                  }
                  if (model.agent_teams) {
                    _ref2 = model.agent_teams;
                    for (k in _ref2) {
                      if (!__hasProp.call(_ref2, k)) continue;
                      v = _ref2[k];
                      if (v) {
                        if (k === 'assigned') {
                          options.agent_teams.push('assigned');
                        } else {
                          options.agent_teams.push(parseInt(k));
                        }
                      }
                    }
                  }
                  value = {};
                  value.type = opt.action_name;
                  value.options = options;
                  return value;
                }
              };
            }
          };
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.resetData = function() {
        this.options_data = null;
        return this.loadDataPromise = null;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.loadDataOptions = function() {
        var defer;
        if (this.options_data) {
          defer = this.$q.defer();
          defer.resolve(this.options_data);
          return defer.promise;
        } else {
          if (!this.loadDataPromise) {
            this.loadDataPromise = this.Api.sendDataGet({
              'agents': '/agents',
              'agent_teams': '/agent_teams',
              'ticket_deps': '/ticket_deps',
              'ticket_cats': '/ticket_cats',
              'ticket_prods': '/ticket_prods',
              'ticket_pris': '/ticket_pris',
              'ticket_works': '/ticket_works',
              'ticket_fields': '/ticket_fields',
              'user_fields': '/user_fields',
              'org_fields': '/org_fields',
              'ticket_slas': '/ticket_slas',
              'email_accounts': '/email_accounts',
              'usergroups': '/user_groups',
              'langs': '/langs',
              'email_tpls': '/email-templates-info',
              round_robin: '/round_robin/settings',
              round_robins: '/round_robin',
              tasks: '/tasks/settings',
              contextual_fields: '/custom_fields',
              'jira_settings': '/apps/jira'
            }).then((function(_this) {
              return function(result) {
                var data, f, options_data, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref10, _ref11, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9, _results;
                data = result.data;
                options_data = {};
                options_data['agents'] = data.agents.agents;
                options_data['agent_teams'] = data.agent_teams.agent_teams;
                options_data['ticket_deps'] = data.ticket_deps.departments;
                options_data['ticket_cats'] = data.ticket_cats.categories;
                options_data['ticket_pris'] = data.ticket_pris.priorities;
                options_data['ticket_works'] = data.ticket_works.workflows;
                options_data['ticket_prods'] = (_ref = data.ticket_prods) != null ? _ref.products : void 0;
                options_data['ticket_fields'] = (_ref1 = data.ticket_fields) != null ? _ref1.custom_fields : void 0;
                options_data['org_fields'] = (_ref2 = data.org_fields) != null ? _ref2.custom_fields : void 0;
                options_data['user_fields'] = (_ref3 = data.user_fields) != null ? _ref3.custom_fields : void 0;
                options_data['ticket_slas'] = (_ref4 = data.ticket_slas) != null ? _ref4.slas : void 0;
                options_data['email_accounts'] = data.email_accounts.email_accounts;
                options_data['usergroups'] = data.usergroups.groups;
                options_data['langs'] = (_ref5 = data.langs) != null ? _ref5.languages : void 0;
                options_data['custom_email_tpls'] = data.email_tpls.list['custom'].groups['custom'].templates;
                options_data['round_robin'] = data.round_robin;
                options_data['round_robins'] = data.round_robins;
                options_data['tasks'] = data.tasks;
                options_data['contextual_fields'] = data.contextual_fields;
                options_data['jira_settings'] = data.jira_settings;
                options_data['ticket_dep_options'] = _this.standardOptionsFormatter(options_data['ticket_deps']);
                _this.options_data = options_data;
                if ((_ref6 = _this.options_data) != null ? _ref6.ticket_fields : void 0) {
                  _ref7 = _this.options_data.ticket_fields;
                  for (_i = 0, _len = _ref7.length; _i < _len; _i++) {
                    f = _ref7[_i];
                    _this.initFieldGetter('SetTicketField', f);
                  }
                }
                if ((_ref8 = _this.options_data) != null ? _ref8.contextual_fields : void 0) {
                  _ref9 = _this.options_data.contextual_fields;
                  for (_j = 0, _len1 = _ref9.length; _j < _len1; _j++) {
                    f = _ref9[_j];
                    _this.initFieldGetter('SetTicketContextualField', f);
                  }
                }
                if ((_ref10 = _this.options_data) != null ? _ref10.user_fields : void 0) {
                  _ref11 = _this.options_data.user_fields;
                  _results = [];
                  for (_k = 0, _len2 = _ref11.length; _k < _len2; _k++) {
                    f = _ref11[_k];
                    _results.push(_this.initFieldGetter('SetUserField', f));
                  }
                  return _results;
                }
              };
            })(this));
          }
          return this.loadDataPromise;
        }
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetAgent = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_id';
        options.dataName = 'agents';
        options.extraOptions = [
          {
            title: 'Unassign',
            value: 0
          }, {
            title: 'Current Agent',
            value: -1
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetRoundRobin = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'id';
        options.dataName = 'round_robins';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetAgentFollowers = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'add_agent_ids';
        options.dataName = 'agents';
        options.isMulti = true;
        options.extraOptions = [
          {
            title: 'Current Agent',
            value: -1
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetAgentTeam = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_team_id';
        options.dataName = 'agent_teams';
        options.extraOptions = [
          {
            title: 'No Team',
            value: 0
          }, {
            title: 'Current Agent\'s Team',
            value: -1
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_id';
        options.dataName = 'ticket_works';
        options.extraOptions = [
          {
            title: 'None',
            value: 0
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetLanguage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'language_id';
        options.dataName = 'langs';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetHold = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_hold';
        options.options = [
          {
            title: "Put ticket on hold",
            value: "1"
          }, {
            title: "Take ticket off hold",
            value: "0"
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetPriority = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'priority_id';
        options.dataName = 'ticket_pris';
        options.extraOptions = [
          {
            title: 'None',
            value: 0
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetCategory = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'category_id';
        options.dataName = 'ticket_cats';
        options.extraOptions = [
          {
            title: 'None',
            value: 0
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetLabels = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-set-labels.html');
          },
          getData: function() {
            return {};
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var viewValue;
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                viewValue = {
                  add_labels: options.add_labels || [],
                  remove_labels: options.remove_labels || [],
                  select2_add: {
                    multiple: true,
                    simple_tags: true,
                    tags: options.add_labels || []
                  },
                  select2_remove: {
                    multiple: true,
                    simple_tags: true,
                    tags: options.remove_labels || []
                  }
                };
                viewValue.with_add = viewValue.add_labels.length > 0;
                viewValue.with_remove = viewValue.remove_labels.length > 0;
                return viewValue;
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'SetLabels';
                value.options = {};
                value.options.add_labels = model.with_add ? model.add_labels : [];
                value.options.remove_labels = model.with_remove ? model.remove_labels : [];
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetSubject = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-set-subject.html');
          },
          getData: function() {
            return {};
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                return {
                  subject: options.subject || '',
                  with_formatter: options.with_formatter || false
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'SetSubject';
                value.options = {};
                value.options.subject = model.subject || '';
                value.options.with_formatter = !!model.with_formatter;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetStatus = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'status';
        options.template = 'OptionBuilder/type-actions-status.html';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetDepartment = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'department_id';
        options.dataName = 'ticket_deps';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetProduct = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'product_id';
        options.dataName = 'ticket_prods';
        options.extraOptions = [
          {
            title: 'None',
            value: 0
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetEmailAccount = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_account_id';
        options.dataName = 'email_accounts';
        options.optionsFormatter = function(options) {
          var acc, opts, _i, _len;
          opts = [];
          for (_i = 0, _len = options.length; _i < _len; _i++) {
            acc = options[_i];
            opts.push({
              value: acc.id,
              title: acc.use_email_address || acc.address
            });
          }
          return opts;
        };
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetEmailSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetUrgency = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-urgency.html');
          },
          getData: function() {
            return {};
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var mode, only_if_lower;
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                mode = options.mode || 'add';
                only_if_lower = false;
                if (mode === 'raise') {
                  mode = 'set';
                  only_if_lower = true;
                }
                return {
                  value: options.urgency,
                  op: mode,
                  only_if_lower: only_if_lower
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'SetUrgency';
                value.options = {};
                value.options.urgency = model.value;
                if (model.op === 'set' && model.only_if_lower) {
                  value.options.mode = 'raise';
                } else {
                  value.options.mode = model.op;
                }
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetCcs = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-set-ccs.html');
          },
          getData: function() {
            return {};
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                return {
                  add_emails: (options.add_emails || []).join(', '),
                  remove_emails: (options.remove_emails || []).join(', '),
                  add_org_managers: options.add_org_managers || false
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'SetCcs';
                value.options = {};
                value.options.add_emails = (model.add_emails || '').split(',');
                value.options.remove_emails = (model.remove_emails || '').split(',');
                value.options.add_org_managers = model.add_org_managers || false;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetFlag = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-select.html');
          },
          getData: function() {
            return {
              options: [
                {
                  title: 'Red',
                  'red': 'red'
                }, {
                  title: 'Blue',
                  'blue': 'blue'
                }, {
                  title: 'Green',
                  'green': 'green'
                }, {
                  title: 'Orange',
                  'orange': 'orange'
                }, {
                  title: 'Purple',
                  'purple': 'purple'
                }, {
                  title: 'Pink',
                  'Pink': 'Pink'
                }
              ]
            };
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                return {
                  value: value.color
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'SetFlag';
                value.options = {};
                value.options.color = model.value || 'red';
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetUserOwner = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_address';
        options.placeholder = 'Enter an email address';
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetDeleted = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetRequireValidation = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'require_validation';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModStopTriggers = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'stop_triggers';
        options.icon = 'fa-chain-broken';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModSetUserVar = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-var.html');
          },
          getData: function() {
            return {};
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                return {
                  name: options.name || '',
                  value: options.value || ''
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'ModSetUserVar';
                value.options = {
                  name: model.name || '',
                  value: model.value || ''
                };
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModMuteUserEmails = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModMuteAgentEmails = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModForceAgentEmails = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-force-agent-emails.html');
          },
          getData: function() {
            return me.loadDataOptions();
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var agent_ids, aid, _i, _len, _ref;
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                agent_ids = {};
                if (options.agent_ids) {
                  _ref = options.agent_ids;
                  for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    aid = _ref[_i];
                    agent_ids[aid + ""] = true;
                  }
                }
                return {
                  agent_ids: agent_ids
                };
              },
              getValue: function(model, data) {
                var k, v, value, _ref;
                if (model == null) {
                  model = {};
                }
                options = {
                  agent_ids: []
                };
                if (model.agent_ids) {
                  _ref = model.agent_ids;
                  for (k in _ref) {
                    if (!__hasProp.call(_ref, k)) continue;
                    v = _ref[k];
                    if (v) {
                      if (Numbers.isNumeric(k)) {
                        options.agent_ids.push(parseInt(k));
                      } else {
                        options.agent_ids.push(k);
                      }
                    }
                  }
                }
                value = {};
                value.type = 'ModForceAgentEmails';
                value.options = options;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getTicketLogText = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.placeholder = 'Enter text here to add to the ticket log';
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSendUserEmail = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-senduseremail.html');
          },
          getData: function() {
            return me.loadDataOptions();
          },
          scopeInit: [
            '$scope', '$modal', '$timeout', function($scope, $modal, $timeout) {
              $scope.handleTemplateChange = function() {
                if ($scope.model.template === 'CREATE') {
                  $scope.model.template = null;
                  $scope.is_creating = true;
                  return $modal.open({
                    templateUrl: DP_BASE_ADMIN_URL + '/load-view/Templates/modal-email-editor.html',
                    controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
                    resolve: {
                      templateName: function() {
                        return null;
                      }
                    }
                  }).result.then((function(_this) {
                    return function(info) {
                      var title, tpl, _ref;
                      if (info.templateName) {
                        title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                        tpl = {
                          name: info.templateName,
                          title: title
                        };
                        if ((((_ref = me.options_data) != null ? _ref.custom_email_tpls : void 0) != null) && me.options_data.custom_email_tpls.indexOf(tpl) === -1) {
                          me.options_data.custom_email_tpls.push(tpl);
                        }
                        $scope.model.template = info.templateName;
                        return $timeout(function() {
                          $scope.model.template = info.templateName;
                          return $scope.is_creating = false;
                        }, 100);
                      } else {
                        return $scope.is_creating = false;
                      }
                    };
                  })(this), function() {
                    return $scope.is_creating = false;
                  });
                }
              };
              return $scope.editTemplate = function() {
                return $modal.open({
                  templateUrl: DP_BASE_ADMIN_URL + '/load-view/Templates/modal-email-editor.html',
                  controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
                  resolve: {
                    templateName: function() {
                      return $scope.model.template;
                    }
                  }
                });
              };
            }
          ],
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var from_name, from_name_custom;
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (from_name !== 'performer' && from_name !== 'helpdesk_name' && from_name !== 'site_name') {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
                return {
                  template: options.template || '',
                  do_cc_users: options.do_cc_users ? "all" : "owner",
                  from_name: from_name,
                  from_name_custom: from_name_custom,
                  from_account: (parseInt(options.from_account || 0) || 0) + '',
                  headers: options.headers || []
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                options = {
                  template: model.template || '',
                  do_cc_users: model.do_cc_users && model.do_cc_users === "all",
                  from_name: '',
                  from_account: parseInt(model.from_account || 0),
                  headers: model.headers.filter(function(header) {
                    return header.name;
                  })
                };
                if (model.from_name === 'custom') {
                  options.from_name = model.from_name_custom || '';
                } else {
                  options.from_name = model.from_name || '';
                }
                value = {};
                value.type = 'SendUserEmail';
                value.options = options;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSendAgentEmail = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-sendagentemail.html');
          },
          getData: function() {
            return me.loadDataOptions();
          },
          scopeInit: [
            '$scope', '$modal', '$timeout', function($scope, $modal, $timeout) {
              $scope.$watch(function() {
                return $scope.model.agent_ids.all_agents;
              }, (function(_this) {
                return function(newVal, oldVal) {
                  var k, v, _ref, _results;
                  if (!newVal) {
                    return;
                  }
                  _ref = $scope.model.agent_ids;
                  _results = [];
                  for (k in _ref) {
                    if (!__hasProp.call(_ref, k)) continue;
                    v = _ref[k];
                    if ('all_agents' === k) {
                      continue;
                    }
                    _results.push($scope.model.agent_ids[k] = false);
                  }
                  return _results;
                };
              })(this));
              $scope.handleTemplateChange = function() {
                if ($scope.model.template === 'CREATE') {
                  $scope.model.template = null;
                  $scope.is_creating = true;
                  return $modal.open({
                    templateUrl: DP_BASE_ADMIN_URL + '/load-view/Templates/modal-email-editor.html',
                    controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
                    resolve: {
                      templateName: function() {
                        return null;
                      }
                    }
                  }).result.then((function(_this) {
                    return function(info) {
                      var title, tpl, _ref;
                      if (info.templateName) {
                        title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                        tpl = {
                          name: info.templateName,
                          title: title
                        };
                        if ((((_ref = me.options_data) != null ? _ref.custom_email_tpls : void 0) != null) && me.options_data.custom_email_tpls.indexOf(tpl) === -1) {
                          me.options_data.custom_email_tpls.push(tpl);
                        }
                        $scope.model.template = info.templateName;
                        return $timeout(function() {
                          $scope.model.template = info.templateName;
                          return $scope.is_creating = false;
                        }, 100);
                      } else {
                        return $scope.is_creating = false;
                      }
                    };
                  })(this), function() {
                    return $scope.is_creating = false;
                  });
                }
              };
              return $scope.editTemplate = function() {
                return $modal.open({
                  templateUrl: DP_BASE_ADMIN_URL + '/load-view/Templates/modal-email-editor.html',
                  controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
                  resolve: {
                    templateName: function() {
                      return $scope.model.template;
                    }
                  }
                });
              };
            }
          ],
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var agent_ids, aid, from_name, from_name_custom, _i, _len, _ref;
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (from_name !== 'performer' && from_name !== 'helpdesk_name' && from_name !== 'site_name') {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
                agent_ids = {};
                if (options.agent_ids) {
                  _ref = options.agent_ids;
                  for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    aid = _ref[_i];
                    agent_ids[aid + ""] = true;
                  }
                } else {
                  agent_ids['notify_list'] = true;
                }
                return {
                  template: options.template || '',
                  agent_ids: agent_ids,
                  from_name: from_name,
                  from_name_custom: from_name_custom,
                  from_account: (parseInt(options.from_account || 0) || 0) + '',
                  headers: options.headers || []
                };
              },
              getValue: function(model, data) {
                var k, v, value, _ref;
                if (model == null) {
                  model = {};
                }
                options = {
                  template: model.template || '',
                  agent_ids: [],
                  from_name: '',
                  from_account: parseInt(model.from_account || 0),
                  headers: model.headers.filter(function(header) {
                    return header.name;
                  })
                };
                if (model.from_name === 'custom') {
                  options.from_name = model.from_name_custom || '';
                } else {
                  options.from_name = model.from_name || '';
                }
                if (model.agent_ids) {
                  _ref = model.agent_ids;
                  for (k in _ref) {
                    if (!__hasProp.call(_ref, k)) continue;
                    v = _ref[k];
                    if (v) {
                      if (Numbers.isNumeric(k)) {
                        options.agent_ids.push(parseInt(k));
                      } else {
                        options.agent_ids.push(k);
                      }
                    }
                  }
                }
                value = {};
                value.type = 'SendAgentEmail';
                value.options = options;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSendSpecificUserEmail = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-sendemail.html');
          },
          getData: function() {
            return me.loadDataOptions();
          },
          scopeInit: [
            '$scope', '$modal', '$timeout', function($scope, $modal, $timeout) {
              $scope.handleTemplateChange = function() {
                if ($scope.model.template === 'CREATE') {
                  $scope.model.template = null;
                  $scope.is_creating = true;
                  return $modal.open({
                    templateUrl: DP_BASE_ADMIN_URL + '/load-view/Templates/modal-email-editor.html',
                    controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
                    resolve: {
                      templateName: function() {
                        return null;
                      }
                    }
                  }).result.then((function(_this) {
                    return function(info) {
                      var title, tpl, _ref;
                      if (info.templateName) {
                        title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                        tpl = {
                          name: info.templateName,
                          title: title
                        };
                        if ((((_ref = me.options_data) != null ? _ref.custom_email_tpls : void 0) != null) && me.options_data.custom_email_tpls.indexOf(tpl) === -1) {
                          me.options_data.custom_email_tpls.push(tpl);
                        }
                        $scope.model.template = info.templateName;
                        return $timeout(function() {
                          $scope.model.template = info.templateName;
                          return $scope.is_creating = false;
                        }, 100);
                      } else {
                        return $scope.is_creating = false;
                      }
                    };
                  })(this), function() {
                    return $scope.is_creating = false;
                  });
                }
              };
              return $scope.editTemplate = function() {
                return $modal.open({
                  templateUrl: DP_BASE_ADMIN_URL + '/load-view/Templates/modal-email-editor.html',
                  controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
                  resolve: {
                    templateName: function() {
                      return $scope.model.template;
                    }
                  }
                });
              };
            }
          ],
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var emails, from_name, from_name_custom;
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                emails = options.emails || [];
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (from_name !== 'performer' && from_name !== 'helpdesk_name' && from_name !== 'site_name') {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
                return {
                  emails: emails,
                  template: options.template || '',
                  from_name: from_name,
                  from_name_custom: from_name_custom,
                  from_account: (parseInt(options.from_account || 0) || 0) + '',
                  headers: options.headers || []
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                options = {
                  emails: model.emails,
                  template: model.template || '',
                  from_name: '',
                  from_account: parseInt(model.from_account || 0),
                  headers: model.headers.filter(function(header) {
                    return header.name;
                  })
                };
                if (model.from_name === 'custom') {
                  options.from_name = model.from_name_custom || '';
                } else {
                  options.from_name = model.from_name || '';
                }
                value = {};
                value.type = 'SendSpecificUserEmail';
                value.options = options;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getWebHook = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-webhook.html');
          },
          getData: function() {
            return {};
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                return value.options || {};
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'WebHook';
                value.options = model;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetSlas = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-slas.html');
          },
          getData: function() {
            var defer;
            defer = me.$q.defer();
            me.loadDataOptions().then((function(_this) {
              return function() {
                var sla, _i, _len, _ref;
                options = [];
                _ref = me.options_data['ticket_slas'];
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  sla = _ref[_i];
                  options.push({
                    title: sla.title,
                    value: sla.id
                  });
                }
                return defer.resolve({
                  options: options
                });
              };
            })(this));
            return defer.promise;
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                options = value.options || {};
                return {
                  add_slas: options.add_sla_ids || [],
                  remove_slas: options.remove_sla_ids || []
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'SetSlas';
                value.options = {};
                value.options.add_sla_ids = model.add_slas;
                value.options.remove_sla_ids = model.remove_slas;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getAddAgentReply = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-addagentreply.html');
          },
          getData: function() {
            return me.loadDataOptions();
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var by_agent_id, opt;
                if (value == null) {
                  value = {};
                }
                opt = value.options || {};
                by_agent_id = opt.by_agent_id || null;
                if (!by_agent_id) {
                  by_agent_id = data.agents[0].id;
                }
                by_agent_id = by_agent_id + "";
                return {
                  type: 'AddAgentReply',
                  text: opt.reply_text || '',
                  by_assigned_agent: opt.by_assigned_agent || false,
                  by_agent_id: by_agent_id
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'AddAgentReply';
                value.options = {};
                value.options.reply_text = model.text;
                value.options.by_assigned_agent = model.by_assigned_agent || false;
                value.options.by_agent_id = parseInt(model.by_agent_id || 0) || 0;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getAddAgentNote = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-addagentreply.html');
          },
          getData: function() {
            return me.loadDataOptions();
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var by_agent_id, opt;
                if (value == null) {
                  value = {};
                }
                opt = value.options || {};
                by_agent_id = opt.by_agent_id || null;
                if (!by_agent_id) {
                  by_agent_id = data.agents[0].id;
                }
                by_agent_id = by_agent_id + "";
                return {
                  type: 'AddAgentNote',
                  text: opt.note_text || '',
                  by_assigned_agent: opt.by_assigned_agent || false,
                  by_agent_id: by_agent_id
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'AddAgentNote';
                value.options = {};
                value.options.note_text = model.text;
                value.options.by_assigned_agent = model.by_assigned_agent || false;
                value.options.by_agent_id = parseInt(model.by_agent_id || 0) || 0;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetSlasComplete = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-setslascomplete.html');
          },
          getData: function() {
            var defer;
            defer = me.$q.defer();
            me.loadDataOptions().then((function(_this) {
              return function() {
                var sla, _i, _len, _ref;
                options = [];
                _ref = me.options_data['ticket_slas'];
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  sla = _ref[_i];
                  options.push({
                    title: sla.title,
                    value: sla.id
                  });
                }
                return defer.resolve({
                  options: options
                });
              };
            })(this));
            return defer.promise;
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                options = value.options || {};
                return {
                  sla_ids: options.sla_ids || [],
                  sla_status: options.sla_status || 'ok'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'SetSlasComplete';
                value.options = {
                  sla_ids: model.sla_ids,
                  sla_status: model.sla_status || 'ok'
                };
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCreateTask = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-create-task.html');
          },
          getData: function() {
            return me.loadDataOptions();
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var agents, _public;
                if (value == null) {
                  value = {};
                }
                options = value.options || {};
                agents = [
                  {
                    id: -1,
                    display_name: 'Current Agent'
                  }
                ];
                data.tasks.agents.map(function(agent) {
                  var _ref, _ref1;
                  if ((_ref = agent.perms) != null ? (_ref1 = _ref.tasks) != null ? _ref1.use : void 0 : void 0) {
                    return agents.push(agent);
                  }
                });
                _public = options["public"];
                if (_public == null) {
                  _public = true;
                }
                return {
                  agents: agents,
                  teams: data.agent_teams,
                  title: options.title,
                  date_due: options.date_due,
                  "public": _public,
                  creator: options.creator,
                  assignee: options.assignee
                };
              },
              getValue: function(model, data) {
                if (model == null) {
                  model = {};
                }
                return {
                  type: 'CreateTask',
                  options: {
                    title: model.title,
                    date_due: model.date_due,
                    "public": model["public"],
                    creator: model.creator,
                    assignee: model.assignee
                  }
                };
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getAddJIRAComment = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-addagentreply.html');
          },
          getData: function() {
            return me.loadDataOptions();
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var by_agent_id, opt;
                if (value == null) {
                  value = {};
                }
                opt = value.options || {};
                by_agent_id = (opt.by_agent_id || data.agents[0].id) + "";
                return {
                  type: 'AddJIRAComment',
                  text: opt.note_text || '',
                  by_assigned_agent: opt.by_assigned_agent || false,
                  by_agent_id: by_agent_id
                };
              },
              getValue: function(model, data) {
                if (model == null) {
                  model = {};
                }
                return {
                  type: 'AddJIRAComment',
                  options: {
                    note_text: model.text,
                    by_assigned_agent: model.by_assigned_agent || false,
                    by_agent_id: parseInt(model.by_agent_id || 0) || 0
                  }
                };
              }
            };
          }
        };
      };

      return Admin_OptionBuilder_TypesDef_TicketFilter;

    })(BaseActionTypesDef);
  });

}).call(this);

//# sourceMappingURL=TicketActions.js.map
