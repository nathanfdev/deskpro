(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/OptionBuilder/TypesDef/BaseActionTypesDef'], function(BaseActionTypesDef) {
    var Admin_OptionBuilder_TypesDef_TicketFilter, _ref;
    return Admin_OptionBuilder_TypesDef_TicketFilter = (function(_super) {
      __extends(Admin_OptionBuilder_TypesDef_TicketFilter, _super);

      function Admin_OptionBuilder_TypesDef_TicketFilter() {
        _ref = Admin_OptionBuilder_TypesDef_TicketFilter.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.init = function() {
        return this.options_data = null;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getOptionsForTypes = function(types, typesData) {
        var opt, options, set_options, typeFunc, _i, _len, _ref1;
        if (types == null) {
          types = [];
        }
        if (typesData == null) {
          typesData = null;
        }
        set_options = [];
        options = [];
        options.push({
          title: 'Change Assigned Agent',
          value: 'SetAgent'
        });
        options.push({
          title: 'Change Assigned Team',
          value: 'SetAgentTeam'
        });
        options.push({
          title: 'Change Agent Followers',
          value: 'SetAgentFollowers'
        });
        set_options.push({
          title: 'Ticket Assignment',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Change Department',
          value: 'SetDepartment'
        });
        options.push({
          title: 'Change Product',
          value: 'SetProduct'
        });
        options.push({
          title: 'Change Category',
          value: 'SetCategory'
        });
        options.push({
          title: 'Change Priority',
          value: 'SetPriority'
        });
        options.push({
          title: 'Change Workflow',
          value: 'SetWorkflow'
        });
        options.push({
          title: 'Change Urgency',
          value: 'SetUrgency'
        });
        options.push({
          title: 'Change Subject',
          value: 'SetSubject'
        });
        options.push({
          title: 'Change Labels',
          value: 'SetLabels'
        });
        options.push({
          title: 'Change Flag',
          value: 'SetFlag'
        });
        options.push({
          title: 'Change Email Account',
          value: 'SetEmailAccount'
        });
        options.push({
          title: 'Change CC\'d Users',
          value: 'SetCcUsers'
        });
        set_options.push({
          title: 'Ticket Properties',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Change SLAs',
          value: 'SetSlas'
        });
        options.push({
          title: 'Change SLA Condition Status (Passing/Failing)',
          value: 'SetSlaStatus'
        });
        options.push({
          title: 'Change SLA State (Waiting/Finished)',
          value: 'SetSlaRequirements'
        });
        set_options.push({
          title: 'Ticket SLAs',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Change Ticket User',
          value: 'ChangeUser'
        });
        options.push({
          title: 'Delete Ticket',
          value: 'DeleteTicket'
        });
        options.push({
          title: 'Add Agent Reply',
          value: 'AddAgentReply'
        });
        options.push({
          title: 'Force User Email Validation',
          value: 'ModForceEmailValidation'
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
        options = [];
        options.push({
          title: 'Stop Processing Triggers',
          value: 'ModStopTriggers'
        });
        options.push({
          title: 'Prevent Emails To User',
          value: 'ModQuietUserEmails'
        });
        options.push({
          title: 'Prevent Emails To Agents',
          value: 'ModQuietAgentEmails'
        });
        set_options.push({
          title: 'Trigger Control',
          subOptions: options
        });
        if ((typesData != null ? typesData.dynamicOptions : void 0) != null) {
          options = [];
          _ref1 = typesData.dynamicOptions;
          for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
            opt = _ref1[_i];
            options.push({
              title: opt.action_title,
              value: opt.action_name
            });
            typeFunc = "get" + opt.action_name;
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
                      return data || {};
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
          if (options.length) {
            set_options.push({
              title: 'Ticket Options',
              subOptions: options
            });
          }
        }
        return set_options;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.loadDataOptions = function() {
        var _this = this;
        if (!this.loadDataPromise) {
          this.loadDataPromise = this.Api.sendDataGet({
            'agents': '/agents',
            'agent_teams': '/agent_teams',
            'ticket_deps': '/ticket_deps',
            'ticket_cats': '/ticket_cats',
            'ticket_prods': '/ticket_prods',
            'ticket_pris': '/ticket_pris',
            'ticket_works': '/ticket_works',
            'ticket_slas': '/ticket_slas',
            'ticket_accounts': '/email_accounts',
            'usergroups': '/user_groups'
          }).then(function(result) {
            var data, options_data, _ref1, _ref2;
            data = result.data;
            options_data = {};
            options_data['agents'] = data.agents.agents;
            options_data['agent_teams'] = data.agent_teams.agent_teams;
            options_data['ticket_deps'] = data.ticket_deps.departments;
            options_data['ticket_cats'] = data.ticket_cats.categories;
            options_data['ticket_pris'] = data.ticket_pris.priorities;
            options_data['ticket_works'] = data.ticket_works.workflows;
            options_data['ticket_prods'] = (_ref1 = data.ticket_prods) != null ? _ref1.products : void 0;
            options_data['ticket_slas'] = (_ref2 = data.ticket_slas) != null ? _ref2.slas : void 0;
            options_data['email_accounts'] = data.ticket_accounts.email_accounts;
            options_data['usergroups'] = data.usergroups.groups;
            return _this.options_data = options_data;
          });
        }
        return this.loadDataPromise;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetAgent = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_id';
        options.dataName = 'agents';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetAgentFollowers = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_ids';
        options.dataName = 'agents';
        options.isMulti = true;
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
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
        options.dataName = 'ticket_works';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
        options.dataName = 'ticket_works';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetPriority = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'priority_ids';
        options.dataName = 'ticket_pris';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetCategory = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'category_ids';
        options.dataName = 'ticket_cats';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetDepartment = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'department_ids';
        options.dataName = 'ticket_deps';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetProduct = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'product_ids';
        options.dataName = 'ticket_prods';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getSetEmailAccount = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'gateway_ids';
        options.dataName = 'email_accounts';
        options.optionsFormatter = function(options) {
          var acc, opts, _i, _len;
          opts = [];
          for (_i = 0, _len = options.length; _i < _len; _i++) {
            acc = options[_i];
            opts.push({
              value: acc.id,
              title: acc.email_address
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
                if (value == null) {
                  value = {};
                }
                return {
                  value: value.urgency,
                  op: value.op || 'add',
                  only_if_lower: !!value.only_if_lower
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'urgency';
                value.options = {};
                value.options.urgency = model.value;
                value.options.op = model.op;
                value.options.only_if_lower = !!model.only_if_lower;
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
                value.type = 'flag';
                value.options = {};
                value.options.color = model.value;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getDeleteTicket = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'delete_ticket';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModForceEmailValidation = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'force_email_validation';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModStopTriggers = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'stop_triggers';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModQuietUserEmails = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getModQuietAgentEmails = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getStandardIs(options);
        return def;
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
                return value;
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'webhook';
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
            var defer,
              _this = this;
            defer = me.$q.defer();
            me.loadDataOptions().then(function() {
              var sla, _i, _len, _ref1;
              options = [];
              _ref1 = me.options_data['ticket_slas'];
              for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
                sla = _ref1[_i];
                options.push({
                  title: sla.title,
                  value: sla.id
                });
              }
              return defer.resolve({
                options: options
              });
            });
            return defer.promise;
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                return {
                  add_slas: value.add_slas || [],
                  remove_slas: value.remove_slas || []
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'slas';
                value.options = {};
                value.options.add_slas = model.add_slas;
                value.options.remove_slas = model.remove_slas;
                return value;
              }
            };
          }
        };
      };

      return Admin_OptionBuilder_TypesDef_TicketFilter;

    })(BaseActionTypesDef);
  });

}).call(this);

/*
//@ sourceMappingURL=TicketActions.js.map
*/