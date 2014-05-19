(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/OptionBuilder/TypesDef/BaseCriteriaTypesDef'], function(BaseCriteriaTypesDef) {
    var Admin_OptionBuilder_TypesDef_TicketFilter;
    return Admin_OptionBuilder_TypesDef_TicketFilter = (function(_super) {
      __extends(Admin_OptionBuilder_TypesDef_TicketFilter, _super);

      function Admin_OptionBuilder_TypesDef_TicketFilter() {
        return Admin_OptionBuilder_TypesDef_TicketFilter.__super__.constructor.apply(this, arguments);
      }

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.init = function() {
        return this.options_data = null;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getOptionsForTypes = function(types, typesData) {
        var options, set_options;
        if (typesData == null) {
          typesData = null;
        }
        set_options = [];
        options = [];
        options.push({
          title: 'Status',
          value: 'FilterStatus'
        });
        options.push({
          title: 'Department',
          value: 'FilterDepartment'
        });
        options.push({
          title: 'Agent',
          value: 'FilterAgent'
        });
        options.push({
          title: 'Agent Team',
          value: 'FilterAgentTeam'
        });
        options.push({
          title: 'Product',
          value: 'FilterProduct'
        });
        options.push({
          title: 'Category',
          value: 'FilterCategory'
        });
        options.push({
          title: 'Priority',
          value: 'FilterPriority'
        });
        options.push({
          title: 'Workflow',
          value: 'FilterWorkflow'
        });
        options.push({
          title: 'Subject',
          value: 'FilterSubject'
        });
        options.push({
          title: 'Hold',
          value: 'FilterHoldStatus'
        });
        set_options.push({
          title: 'Ticket Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Name',
          value: 'FilterUserName'
        });
        options.push({
          title: 'Email Address',
          value: 'FilterUserEmailAddress'
        });
        options.push({
          title: 'Label',
          value: 'FilterUserLabels'
        });
        options.push({
          title: 'Usergroup',
          value: 'FilterUserUsergroups'
        });
        options.push({
          title: 'Language',
          value: 'FilterUserLanguage'
        });
        options.push({
          title: 'Is manager of organization',
          value: 'FilterUserIsManager'
        });
        options.push({
          title: 'Is disabled',
          value: 'FilterUserIsDisabled'
        });
        options.push({
          title: 'User Contact Phone',
          value: 'FilterUserContactPhone'
        });
        options.push({
          title: 'User Contact Address',
          value: 'FilterUserContactAddress'
        });
        options.push({
          title: 'User Contact Instant Messaging',
          value: 'FilterUserContactIm'
        });
        set_options.push({
          title: 'User Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Organization Name',
          value: 'FilterOrgName'
        });
        options.push({
          title: 'Organization Label',
          value: 'FilterOrgLabels'
        });
        options.push({
          title: 'Organization Contact Phone',
          value: 'FilterOrgContactPhone'
        });
        options.push({
          title: 'Organization Contact Address',
          value: 'FilterOrgContactAddress'
        });
        options.push({
          title: 'Organization Contact Instant Messaging',
          value: 'FilterOrgContactIm'
        });
        options.push({
          title: 'Organization Email Domain',
          value: 'FilterOrgEmailDomain'
        });
        options.push({
          title: 'Organization Linked Usergroup',
          value: 'FilterOrgGroups'
        });
        set_options.push({
          title: 'Organization Criteria',
          subOptions: options
        });
        return set_options;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.loadDataOptions = function() {
        var p;
        if (this.options_data) {
          p = this.$q.fcall((function(_this) {
            return function() {
              return _this.options_data;
            };
          })(this));
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
            'ticket_accounts': '/email_accounts',
            'usergroups': '/user_groups'
          }).then((function(_this) {
            return function(result) {
              var data, _ref;
              data = result.data;
              _this.options_data['agents'] = data.agents.agents;
              _this.options_data['agent_teams'] = data.agent_teams.agent_teams;
              _this.options_data['ticket_deps'] = data.ticket_deps.departments;
              _this.options_data['ticket_cats'] = data.ticket_cats.categories;
              _this.options_data['ticket_pris'] = data.ticket_pris.priorities;
              _this.options_data['ticket_works'] = data.ticket_works.workflows;
              _this.options_data['ticket_prods'] = (_ref = data.ticket_prods) != null ? _ref.products : void 0;
              _this.options_data['ticket_accounts'] = data.ticket_accounts.ticket_accounts;
              return _this.options_data['usergroups'] = data.usergroups.groups;
            };
          })(this));
        }
        return p;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterPriority = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'priority_ids';
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterCategory = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'category_ids';
        options.dataName = 'ticket_cats';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterStatus = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'status';
        options.template = 'OptionBuilder/type-filter-status.html';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterHoldStatus = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-filter-hold.html');
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
                  op: 'is',
                  value: value.is_hold ? '1' : '0'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = 'FilterHoldStatus';
                value.op = model.op;
                value.options = {
                  is_hold: parseInt(model.value) === 1 ? true : false
                };
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterDepartment = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'department_ids';
        options.dataName = 'ticket_deps';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterAgent = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_ids';
        options.dataName = 'agents';
        options.extraOptions = [
          {
            title: 'Unassigned',
            value: 0
          }, {
            title: 'Current Agent',
            value: -1
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterAgentTeam = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_team_ids';
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterProduct = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'product_ids';
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterEmailAccount = function(options) {
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterCcAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterCcName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterEmailHeader = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_header_match';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterMessage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterHasAttach = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'with_attach';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterHasAttachType = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'attach_type';
        options.operators = ['is', 'not'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterHasAttachName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'attach_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserEmailAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserLabels = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserUsergroups = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'group_ids';
        options.dataName = 'usergroups';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserLanguage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'language_ids';
        options.dataName = 'languages';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserIsManager = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_manager';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserIsDisabled = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_disabled';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserContactPhone = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'address';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserContactAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'address';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserContactIm = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'im';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgLabels = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgContactPhone = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'address';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgContactAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'address';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgContactIm = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'im';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgEmailDomain = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgGroups = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'group_ids';
        options.dataName = 'usergroups';
        options.operators = ['is', 'not'];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterDayOfWeek = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-dayofweek.html');
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
                  op: value.op || 'is'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterTimeOfDay = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-timeofday.html');
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
                  op: value.op || 'is'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterWorkingHours = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-workinghours.html');
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
                  op: value.op || 'is'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                return value;
              }
            };
          }
        };
      };

      return Admin_OptionBuilder_TypesDef_TicketFilter;

    })(BaseCriteriaTypesDef);
  });

}).call(this);

//# sourceMappingURL=TicketFilter.js.map
