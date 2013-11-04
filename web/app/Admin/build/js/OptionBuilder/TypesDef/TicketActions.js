(function() {
  define(function() {
    var Admin_OptionBuilder_TypesDef_TicketActions;
    return Admin_OptionBuilder_TypesDef_TicketActions = (function() {
      function Admin_OptionBuilder_TypesDef_TicketActions($q, Api, dpTemplateManager) {
        this.$q = $q;
        this.Api = Api;
        this.dpTemplateManager = dpTemplateManager;
        this.options_data = null;
      }

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getOptionsForTypes = function(types, typesData) {
        var options, set_options;
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
        return set_options;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.loadDataOptions = function() {
        var p,
          _this = this;
        if (this.options_data) {
          p = this.$q.fcall(function() {
            return _this.options_data;
          });
        } else {
          this.options_data = {};
          p = this.Api.sendDataGet({
            'agents': '/agents',
            'agent_teams': '/agent_teams',
            'ticket_deps': '/ticket_deps',
            'ticket_cats': '/ticket_cats',
            'ticket_prods': '/ticket_prods',
            'ticket_pris': '/ticket_pris',
            'ticket_works': '/ticket_works',
            'ticket_slas': '/ticket_slas',
            'ticket_accounts': '/ticket_accounts',
            'usergroups': '/usergroups'
          }).then(function(result) {
            var data, _ref, _ref1;
            data = result.data;
            _this.options_data['agents'] = data.agents.agents;
            _this.options_data['agent_teams'] = data.agent_teams.agent_teams;
            _this.options_data['ticket_deps'] = data.ticket_deps.departments;
            _this.options_data['ticket_cats'] = data.ticket_cats.categories;
            _this.options_data['ticket_pris'] = data.ticket_pris.priorities;
            _this.options_data['ticket_works'] = data.ticket_works.workflows;
            _this.options_data['ticket_prods'] = (_ref = data.ticket_prods) != null ? _ref.products : void 0;
            _this.options_data['ticket_slas'] = (_ref1 = data.ticket_slas) != null ? _ref1.slas : void 0;
            _this.options_data['ticket_accounts'] = data.ticket_accounts.ticket_accounts;
            return _this.options_data['usergroups'] = data.usergroups.usergroups;
          });
        }
        return p;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getDef = function(type, options) {
        var me, typeFunc, typeName;
        if (options == null) {
          options = {};
        }
        typeName = type;
        options.type = type;
        typeFunc = "get" + typeName;
        if (this[typeFunc] != null) {
          return this[typeFunc](options);
        } else {
          console.error("Bad type with no definition getter: " + typeFunc);
          me = this;
          return {
            getTemplate: function() {
              return me.dpTemplateManager.get('OptionBuilder/type-actions-input.html');
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
                  return {};
                },
                getValue: function(model, data) {
                  if (model == null) {
                    model = {};
                  }
                  return null;
                }
              };
            }
          };
        }
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getStandardSelect = function(options) {
        var data_name, is_multi, me, options_formatter, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        data_name = options.dataName;
        options_formatter = options.optionsFormatter || null;
        is_multi = options.isMulti;
        if (!options_formatter) {
          options_formatter = function(options) {
            var opt, opts, title, val, _i, _len;
            opts = [];
            for (_i = 0, _len = options.length; _i < _len; _i++) {
              opt = options[_i];
              if (opt.title) {
                title = opt.title;
              } else if (opt.display_name) {
                title = opt.display_name;
              } else if (opt.name) {
                title = opt.name;
              } else {
                title = null;
              }
              if (opt.id) {
                val = opt.id;
              } else if (opt.value) {
                val = opt.value;
              } else {
                val = null;
              }
              if (title !== null && val !== null) {
                opts.push({
                  title: title,
                  value: val
                });
              }
            }
            return opts;
          };
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-select.html');
          },
          getData: function() {
            var defer,
              _this = this;
            if (data_name) {
              defer = me.$q.defer();
              me.loadDataOptions().then(function() {
                return defer.resolve({
                  options: options_formatter ? options_formatter(me.options_data[data_name]) : me.options_data[data_name],
                  multiselect: is_multi
                });
              });
              return defer.promise;
            } else {
              return {};
            }
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                return {
                  value: value[prop_name]
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = type;
                value.options = {};
                value.options[prop_name] = model.value;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getStandardIs = function(options) {
        var me, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-is.html');
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
                  value: true,
                  op: 'is'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = type;
                value.options = {};
                value.options[prop_name] = true;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getStandardInput = function(options) {
        var me, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-input.html');
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
                  value: value[prop_name]
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = type;
                value.options = {};
                value.options[prop_name] = model.value;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetAgent = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_id';
        options.dataName = 'agents';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetAgentFollowers = function(options) {
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

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetAgentTeam = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_team_id';
        options.dataName = 'agent_teams';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
        options.dataName = 'ticket_works';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
        options.dataName = 'ticket_works';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetPriority = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'priority_ids';
        options.dataName = 'ticket_pris';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetCategory = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'category_ids';
        options.dataName = 'ticket_cats';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetDepartment = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'department_ids';
        options.dataName = 'ticket_deps';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetProduct = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'product_ids';
        options.dataName = 'ticket_prods';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetEmailAccount = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'gateway_ids';
        options.dataName = 'ticket_accounts';
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

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetEmailSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetUrgency = function(options) {
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

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetFlag = function(options) {
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

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getDeleteTicket = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'delete_ticket';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getModForceEmailValidation = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'force_email_validation';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getModStopTriggers = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'stop_triggers';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getModQuietUserEmails = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getModQuietAgentEmails = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketActions.prototype.getSetSlas = function(options) {
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

      return Admin_OptionBuilder_TypesDef_TicketActions;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=TicketActions.js.map
*/