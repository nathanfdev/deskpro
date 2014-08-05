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
        var f, options, set_options, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3;
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
          title: 'Email Account',
          value: 'FilterEmailAccount'
        });
        options.push({
          title: 'Subject',
          value: 'FilterSubject'
        });
        options.push({
          title: 'Hold',
          value: 'FilterHoldStatus'
        });
        options.push({
          title: 'Date Created',
          value: 'FilterDateCreated'
        });
        options.push({
          title: 'Date Resolved',
          value: 'FilterDateResolved'
        });
        options.push({
          title: 'Date Archived',
          value: 'FilterDateClosed'
        });
        options.push({
          title: 'Date Of Last Agent Reply',
          value: 'FilterDateLastAgentReply'
        });
        options.push({
          title: 'Date Of Last User Reply',
          value: 'FilterDateLastUserReply'
        });
        options.push({
          title: 'User Waiting Time',
          value: 'FilterUserWaiting'
        });
        options.push({
          title: 'Total User Waiting Time',
          value: 'FilterTotalUserWaiting'
        });
        set_options.push({
          title: 'Ticket Criteria',
          subOptions: options
        });
        if ((_ref = this.options_data) != null ? _ref.ticket_fields : void 0) {
          options = [];
          _ref1 = this.options_data.ticket_fields;
          for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
            f = _ref1[_i];
            options.push({
              title: f.title,
              value: this.initFieldGetter('FilterTicketField', f)
            });
          }
          if (options.length) {
            set_options.push({
              title: 'Ticket Fields',
              subOptions: options
            });
          }
        }
        if ((_ref2 = this.options_data) != null ? _ref2.user_fields : void 0) {
          options = [];
          _ref3 = this.options_data.user_fields;
          for (_j = 0, _len1 = _ref3.length; _j < _len1; _j++) {
            f = _ref3[_j];
            options.push({
              title: f.title,
              value: this.initFieldGetter('FilterUserField', f)
            });
          }
          if (options.length) {
            set_options.push({
              title: 'Person Fields',
              subOptions: options
            });
          }
        }
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
        options.push({
          title: 'Date User Created',
          value: 'FilterUserDateCreated'
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
        options.push({
          title: 'Date Organization Created',
          value: 'FilterOrgDateCreated'
        });
        set_options.push({
          title: 'Organization Criteria',
          subOptions: options
        });
        return set_options;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.resetData = function() {
        this.options_data = null;
        return this.loadDataPromise = null;
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
            'ticket_fields': '/ticket_fields',
            'user_fields': '/user_fields',
            'org_fields': '/org_fields',
            'ticket_accounts': '/email_accounts',
            'usergroups': '/user_groups'
          }).then((function(_this) {
            return function(result) {
              var data, f, options_data, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _results;
              data = result.data;
              options_data = {};
              options_data['agents'] = data.agents.agents;
              options_data['agent_teams'] = data.agent_teams.agent_teams;
              options_data['ticket_deps'] = data.ticket_deps.departments;
              options_data['ticket_cats'] = data.ticket_cats.categories;
              options_data['ticket_pris'] = data.ticket_pris.priorities;
              options_data['ticket_works'] = data.ticket_works.workflows;
              options_data['ticket_fields'] = (_ref = data.ticket_fields) != null ? _ref.custom_fields : void 0;
              options_data['org_fields'] = (_ref1 = data.org_fields) != null ? _ref1.custom_fields : void 0;
              options_data['user_fields'] = (_ref2 = data.user_fields) != null ? _ref2.custom_fields : void 0;
              options_data['ticket_prods'] = (_ref3 = data.ticket_prods) != null ? _ref3.products : void 0;
              options_data['ticket_accounts'] = data.ticket_accounts.email_accounts;
              options_data['usergroups'] = data.usergroups.groups;
              _this.options_data = options_data;
              if ((_ref4 = _this.options_data) != null ? _ref4.ticket_fields : void 0) {
                _ref5 = _this.options_data.ticket_fields;
                for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
                  f = _ref5[_i];
                  _this.initFieldGetter('FilterTicketField', f);
                }
              }
              if ((_ref6 = _this.options_data) != null ? _ref6.user_fields : void 0) {
                _ref7 = _this.options_data.user_fields;
                _results = [];
                for (_j = 0, _len1 = _ref7.length; _j < _len1; _j++) {
                  f = _ref7[_j];
                  _results.push(_this.initFieldGetter('FilterUserField', f));
                }
                return _results;
              }
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserWaiting = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'time';
        def = this.getTimeElapsedInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterTotalUserWaiting = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'time';
        def = this.getTimeElapsedInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterDateCreated = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getDateInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterDateResolved = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getDateInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterDateClosed = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getDateInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterDateLastAgentReply = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getDateInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterDateLastUserReply = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getDateInput(options);
        return def;
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
        options.propName = 'team_ids';
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
        options.propName = 'email_account_ids';
        options.dataName = 'ticket_accounts';
        options.optionsFormatter = function(options) {
          var acc, opts, _i, _len;
          opts = [];
          for (_i = 0, _len = options.length; _i < _len; _i++) {
            acc = options[_i];
            opts.push({
              value: acc.id,
              title: acc.address
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
        options.operators = ['is', 'not', 'contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterCcName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_name';
        options.operators = ['is', 'not', 'contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterEmailHeader = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_header_match';
        options.operators = ['is', 'not', 'contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        options.operators = ['is', 'not', 'contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterMessage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['is', 'not', 'contains', 'notcontains'];
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
        options.operators = ['is', 'not', 'contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserEmailAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserLabels = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'notcontains'];
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
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserContactAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'address';
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserContactIm = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'im';
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterUserDateCreated = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getDateInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgLabels = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgContactPhone = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'address';
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgContactAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'address';
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgContactIm = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'im';
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgEmailDomain = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'notcontains'];
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getFilterOrgDateCreated = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getDateInput(options);
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
