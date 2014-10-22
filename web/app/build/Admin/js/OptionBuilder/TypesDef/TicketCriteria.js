(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/OptionBuilder/TypesDef/BaseCriteriaTypesDef'], function(BaseCriteriaTypesDef) {
    var Admin_OptionBuilder_TypesDef_TicketCriteria;
    return Admin_OptionBuilder_TypesDef_TicketCriteria = (function(_super) {
      __extends(Admin_OptionBuilder_TypesDef_TicketCriteria, _super);

      function Admin_OptionBuilder_TypesDef_TicketCriteria() {
        return Admin_OptionBuilder_TypesDef_TicketCriteria.__super__.constructor.apply(this, arguments);
      }

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.init = function() {
        return this.options_data = null;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.setWithChangedOps = function(with_changed_ops) {
        this.with_changed_ops = with_changed_ops;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getOperators = function(options) {
        var o, ops, _i, _len, _ref;
        if (!options.operators) {
          return ['is', 'not'];
        }
        if (this.with_changed_ops) {
          return options.operators;
        }
        ops = [];
        _ref = options.operators;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          o = _ref[_i];
          if (o !== 'changed' && o !== 'changed_to' && o !== 'changed_from') {
            ops.push(o);
          }
        }
        return ops;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getOptionsForTypes = function(types, typesData) {
        var f, options, set_options, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9;
        if (typesData == null) {
          typesData = null;
        }
        set_options = [];
        if (types.indexOf('email') !== -1) {
          options = [];
          options.push({
            title: 'Email Account',
            value: 'CheckEmailAccount'
          });
          options.push({
            title: 'Email Subject',
            value: 'CheckEmailSubject'
          });
          options.push({
            title: 'Email Body',
            value: 'CheckEmailBody'
          });
          options.push({
            title: 'To Name',
            value: 'CheckEmailToName'
          });
          options.push({
            title: 'To Address',
            value: 'CheckEmailToAddress'
          });
          options.push({
            title: 'From Name',
            value: 'CheckEmailFromName'
          });
          options.push({
            title: 'From Address',
            value: 'CheckEmailFromAddress'
          });
          options.push({
            title: 'CCd Name',
            value: 'CheckEmailCcName'
          });
          options.push({
            title: 'CCd Address',
            value: 'CheckEmailCcAddress'
          });
          options.push({
            title: 'Email Header',
            value: 'CheckEmailHeader'
          });
          set_options.push({
            title: 'Email Criteria',
            subOptions: options
          });
        }
        options = [];
        options.push({
          title: 'Department',
          value: 'CheckDepartment'
        });
        options.push({
          title: 'Status',
          value: 'CheckStatus'
        });
        options.push({
          title: 'Agent',
          value: 'CheckAgent'
        });
        options.push({
          title: 'Agent Team',
          value: 'CheckAgentTeam'
        });
        options.push({
          title: 'Product',
          value: 'CheckProduct'
        });
        options.push({
          title: 'Category',
          value: 'CheckCategory'
        });
        options.push({
          title: 'Priority',
          value: 'CheckPriority'
        });
        options.push({
          title: 'Urgency',
          value: 'CheckUrgency'
        });
        if (types.indexOf('web.agent') !== -1) {
          options.push({
            title: 'Workflow',
            value: 'CheckWorkflow'
          });
        }
        options.push({
          title: 'Subject',
          value: 'CheckSubject'
        });
        options.push({
          title: 'Labels',
          value: 'CheckLabel'
        });
        options.push({
          title: 'SLAs',
          value: 'CheckSlaStatus'
        });
        options.push({
          title: 'Creation System',
          value: 'CheckCreationSystem'
        });
        options.push({
          title: 'Created via URL',
          value: 'CheckCreationSystemOption'
        });
        options.push({
          title: 'Agent Message',
          value: 'CheckAgentMessage'
        });
        options.push({
          title: 'Agent Note',
          value: 'CheckAgentNote'
        });
        options.push({
          title: 'User Message',
          value: 'CheckUserMessage'
        });
        if ((_ref = this.options_data) != null ? (_ref1 = _ref.ticket_settings) != null ? _ref1.satisfaction_enabled : void 0 : void 0) {
          options.push({
            title: 'Ticket Satisfaction',
            value: 'CheckTicketSatisfaction'
          });
        }
        set_options.push({
          title: 'Ticket Criteria',
          subOptions: options
        });
        options = [];
        if ((_ref2 = this.options_data) != null ? _ref2.ticket_fields : void 0) {
          _ref3 = this.options_data.ticket_fields;
          for (_i = 0, _len = _ref3.length; _i < _len; _i++) {
            f = _ref3[_i];
            options.push({
              title: f.title,
              value: this.initFieldGetter('CheckTicketField', f)
            });
          }
        }
        if ((_ref4 = this.options_data) != null ? _ref4.contextual_fields : void 0) {
          _ref5 = this.options_data.contextual_fields;
          for (_j = 0, _len1 = _ref5.length; _j < _len1; _j++) {
            f = _ref5[_j];
            options.push({
              title: f.title,
              value: this.initFieldGetter('CheckTicketContextualField', f)
            });
          }
        }
        if (options.length) {
          set_options.push({
            title: 'Ticket Fields',
            subOptions: options
          });
        }
        options = [];
        options.push({
          title: 'User',
          value: 'CheckUserId'
        });
        options.push({
          title: 'User Name',
          value: 'CheckUserName'
        });
        options.push({
          title: 'User Email Address',
          value: 'CheckUserEmail'
        });
        options.push({
          title: 'User Label',
          value: 'CheckUserLabel'
        });
        options.push({
          title: 'Usergroup',
          value: 'CheckUserUsergroups'
        });
        options.push({
          title: 'User Language',
          value: 'CheckUserLanguage'
        });
        options.push({
          title: 'User is manager of organization',
          value: 'CheckUserOrgManager'
        });
        options.push({
          title: 'User is new',
          value: 'CheckUserIsNew'
        });
        options.push({
          title: 'Check agent validation status',
          value: 'CheckUserValidAgent'
        });
        options.push({
          title: 'Check email validation status',
          value: 'CheckUserValidEmail'
        });
        options.push({
          title: 'User is disabled',
          value: 'CheckUserIsDisabled'
        });
        set_options.push({
          title: 'User Criteria',
          subOptions: options
        });
        if ((_ref6 = this.options_data) != null ? _ref6.user_fields : void 0) {
          options = [];
          _ref7 = this.options_data.user_fields;
          for (_k = 0, _len2 = _ref7.length; _k < _len2; _k++) {
            f = _ref7[_k];
            options.push({
              title: f.title,
              value: this.initFieldGetter('CheckUserField', f)
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
          title: 'Organization',
          value: 'CheckOrgId'
        });
        options.push({
          title: 'Organization Name',
          value: 'CheckOrgName'
        });
        options.push({
          title: 'Organization Label',
          value: 'CheckOrgLabel'
        });
        options.push({
          title: 'Organization Email Domain',
          value: 'CheckOrgEmailDomain'
        });
        options.push({
          title: 'Organization Usergroup',
          value: 'CheckOrgUsergroups'
        });
        set_options.push({
          title: 'Organization Criteria',
          subOptions: options
        });
        if ((_ref8 = this.options_data) != null ? _ref8.org_fields : void 0) {
          options = [];
          _ref9 = this.options_data.org_fields;
          for (_l = 0, _len3 = _ref9.length; _l < _len3; _l++) {
            f = _ref9[_l];
            options.push({
              title: f.title,
              value: this.initFieldGetter('OrgField', f)
            });
          }
          if (options.length) {
            set_options.push({
              title: 'Organization Fields',
              subOptions: options
            });
          }
        }
        options = [];
        options.push({
          title: 'Current day of week',
          value: 'CheckDayOfWeek'
        });
        options.push({
          title: 'Current time of day',
          value: 'CheckTimeOfDay'
        });
        options.push({
          title: 'Ticket Created Date',
          value: 'CheckDateCreated'
        });
        options.push({
          title: 'During Working Hours',
          value: 'CheckWorkingHours'
        });
        set_options.push({
          title: 'Dates',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Check if user was emailed',
          value: 'CheckUserIsEmailed'
        });
        options.push({
          title: 'Check if agents were emailed',
          value: 'CheckAgentIsEmailed'
        });
        options.push({
          title: 'Check Trigger Variable',
          value: 'CheckUserVar'
        });
        options.push({
          title: 'Check Current Agent',
          value: 'CheckPerformer'
        });
        options.push({
          title: 'Check Performer Email',
          value: 'CheckPerformerEmail'
        });
        set_options.push({
          title: 'Trigger Control',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Check API key',
          value: 'CheckApiKey'
        });
        set_options.push({
          title: 'API Criteria',
          subOptions: options
        });
        return set_options;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.resetData = function() {
        this.options_data = null;
        return this.loadDataPromise = null;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.loadDataOptions = function() {
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
              'ticket_accounts': '/email_accounts',
              'usergroups': '/user_groups',
              'langs': '/langs',
              'email_tpls': '/email-templates-info',
              'api_keys': '/api_keys',
              'ticket_settings': '/ticket_settings',
              'contextual_fields': '/custom_fields'
            }).then((function(_this) {
              return function(result) {
                var data, f, options_data, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref10, _ref11, _ref12, _ref13, _ref14, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9, _results;
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
                options_data['email_accounts'] = data.ticket_accounts.email_accounts;
                options_data['usergroups'] = data.usergroups.groups;
                options_data['langs'] = (_ref5 = data.langs) != null ? _ref5.languages : void 0;
                options_data['custom_email_tpls'] = data.email_tpls.list['custom'].groups['custom'].templates;
                options_data['api_keys'] = data.api_keys.api_keys;
                options_data['ticket_settings'] = (_ref6 = data.ticket_settings) != null ? _ref6.ticket_settings : void 0;
                options_data['contextual_fields'] = data.contextual_fields;
                _this.options_data = options_data;
                if ((_ref7 = _this.options_data) != null ? _ref7.ticket_fields : void 0) {
                  _ref8 = _this.options_data.ticket_fields;
                  for (_i = 0, _len = _ref8.length; _i < _len; _i++) {
                    f = _ref8[_i];
                    _this.initFieldGetter('CheckTicketField', f);
                  }
                }
                if ((_ref9 = _this.options_data) != null ? _ref9.contextual_fields : void 0) {
                  _ref10 = _this.options_data.contextual_fields;
                  for (_j = 0, _len1 = _ref10.length; _j < _len1; _j++) {
                    f = _ref10[_j];
                    _this.initFieldGetter('CheckTicketContextualField', f);
                  }
                }
                if ((_ref11 = _this.options_data) != null ? _ref11.user_fields : void 0) {
                  _ref12 = _this.options_data.user_fields;
                  for (_k = 0, _len2 = _ref12.length; _k < _len2; _k++) {
                    f = _ref12[_k];
                    _this.initFieldGetter('CheckUserField', f);
                  }
                }
                if ((_ref13 = _this.options_data) != null ? _ref13.org_fields : void 0) {
                  _ref14 = _this.options_data.org_fields;
                  _results = [];
                  for (_l = 0, _len3 = _ref14.length; _l < _len3; _l++) {
                    f = _ref14[_l];
                    _results.push(_this.initFieldGetter('CheckOrgField', f));
                  }
                  return _results;
                }
              };
            })(this));
          }
          return this.loadDataPromise;
        }
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
        options.dataName = 'ticket_works';
        options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
        options.extraOptions = [
          {
            title: 'None',
            value: 0
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckPriority = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'priority_ids';
        options.dataName = 'ticket_pris';
        options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
        options.extraOptions = [
          {
            title: 'None',
            value: 0
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUrgency = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'urgency1';
        options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckCategory = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'category_ids';
        options.dataName = 'ticket_cats';
        options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
        options.extraOptions = [
          {
            title: 'None',
            value: 0
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckDepartment = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'department_ids';
        options.dataName = 'ticket_deps';
        options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckAgent = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'agent_ids';
        options.dataName = 'agents';
        options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
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

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckPerformer = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'person_ids';
        options.dataName = 'agents';
        options.operators = ['contains', 'notcontains'];
        options.template = 'OptionBuilder/type-criteria-performer.html';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckPerformerEmail = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckAgentTeam = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'team_ids';
        options.dataName = 'agent_teams';
        options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
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

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckProduct = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'product_ids';
        options.dataName = 'ticket_prods';
        options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
        options.extraOptions = [
          {
            title: 'None',
            value: 0
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailAccount = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_account_ids';
        options.dataName = 'email_accounts';
        options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from'];
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

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailBody = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'body';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailToName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailToAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailFromName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailFromAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailCcAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailCcName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailHeader = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-emailheader.html');
          },
          getData: function() {
            return {};
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var _ref, _ref1;
                if (value == null) {
                  value = {};
                }
                return {
                  op: value.op || 'is',
                  name: ((_ref = value.options) != null ? _ref.name : void 0) || '',
                  value: ((_ref1 = value.options) != null ? _ref1.value : void 0) || ''
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {
                  type: 'CheckEmailHeader',
                  op: model.op || 'is',
                  options: {
                    name: model.name || '',
                    value: model.value || ''
                  }
                };
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckLabel = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.type_title = 'Labels';
        options.tags = true;
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckSlaStatus = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-slas.html');
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
                  op: value.op || 'contains',
                  sla_ids: options.sla_ids || [],
                  is_complete: !!options.is_complete,
                  sla_status: options.sla_status || 'passing',
                  show_status: !!options.sla_status
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {
                  type: 'CheckSlaStatus',
                  op: model.op || 'contains',
                  options: {
                    sla_ids: model.sla_ids || [],
                    is_complete: model.op === 'contains' ? !!model.is_complete : null,
                    sla_status: model.show_status && model.sla_status && model.op === 'contains' ? model.sla_status : null
                  }
                };
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckStatus = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'status';
        options.template = 'OptionBuilder/type-criteria-status.html';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckAgentMessage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckCreationSystem = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'creation_system';
        options.operators = ['is', 'not'];
        options.options = [
          {
            title: "Created by a user via the portal",
            value: "web.person.portal"
          }, {
            title: "Created by a user via the Feedback and Support tab",
            value: "web.person.widget"
          }, {
            title: "Created by a user via an embedded form",
            value: "web.person.embed"
          }, {
            title: "Created by a user via email",
            value: "gateway.person"
          }, {
            title: "Created by an agent via the agent interface",
            value: "web.agent.portal"
          }, {
            title: "Created by an agent via email",
            value: "gateway.agent"
          }, {
            title: "Create by the API in a user context",
            value: "web.api.person"
          }, {
            title: "Create by the API in an agent context",
            value: "web.api.agent"
          }
        ];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckCreationSystemOption = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'creation_system_option';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckAgentNote = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserMessage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckHasAttach = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'with_attach';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckHasAttachType = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'attach_type';
        options.operators = ['is', 'not'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckHasAttachName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'attach_name';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserId = function(options) {
        var format;
        if (options == null) {
          options = {};
        }
        options.propName = 'id';
        options.operators = ['is', 'not'];
        options.url = '/people/quick_search';
        format = function(item) {
          return "" + item['name'] + " (" + (item.email || '') + ")";
        };
        options.inputOptions = {
          formatResult: format,
          formatSelection: format,
          ajax: {
            data: function(term, page) {
              return {
                query: term,
                limit: 10,
                with_agents: false,
                start_with: true
              };
            }
          }
        };
        return this.getRemoteInput(options);
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserEmail = function(options) {
        var format;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        options.url = '/people/quick_search';
        format = function(item) {
          return "" + item[options.propName] + " (" + (item.name || '') + ")";
        };
        options.inputOptions = {
          formatResult: format,
          formatSelection: format,
          ajax: {
            data: function(term, page) {
              return {
                query: term,
                limit: 10,
                with_agents: false,
                start_with: true
              };
            }
          }
        };
        return this.getRemoteInput(options);
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserLabel = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserUsergroups = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'usergroup_ids';
        options.dataName = 'usergroups';
        options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from'];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserLanguage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'language_ids';
        options.dataName = 'langs';
        options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from'];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserOrgManager = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_manager';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserIsDisabled = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_disabled';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserIsNew = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_new';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserValidAgent = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html');
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
                  op: value.op || 'is',
                  options: [
                    {
                      value: 'is',
                      title: 'User has been validated by an agent'
                    }, {
                      value: 'not',
                      title: 'User is waiting to be validated by an agent'
                    }
                  ]
                };
              },
              getValue: function(model, data) {
                if (model == null) {
                  model = {};
                }
                return {
                  type: 'CheckUserValidEmail',
                  op: model.op || 'is',
                  options: {
                    run: true
                  }
                };
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserValidEmail = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html');
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
                  op: value.op || 'is',
                  options: [
                    {
                      value: 'is',
                      title: 'User has validated their email address'
                    }, {
                      value: 'not',
                      title: 'User has not yet validated their email address'
                    }
                  ]
                };
              },
              getValue: function(model, data) {
                if (model == null) {
                  model = {};
                }
                return {
                  type: 'CheckUserValidEmail',
                  op: model.op || 'is',
                  options: {
                    run: true
                  }
                };
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckOrgName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckOrgId = function(options) {
        var format;
        if (options == null) {
          options = {};
        }
        options.propName = 'id';
        options.operators = ['is', 'not', 'isset', 'not_isset'];
        options.url = '/organizations/quick_search';
        format = function(item) {
          return item['name'];
        };
        options.inputOptions = {
          formatResult: format,
          formatSelection: format,
          ajax: {
            data: function(term, page) {
              return {
                query: term,
                limit: 10
              };
            }
          }
        };
        return this.getRemoteInput(options);
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckOrgLabel = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'notcontains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckOrgEmailDomain = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_domain';
        options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckOrgUsergroups = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'usergroup_ids';
        options.dataName = 'usergroups';
        options.operators = ['is', 'not'];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckDayOfWeek = function(options) {
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
                var d, days, _i, _len, _ref;
                if (value == null) {
                  value = {};
                }
                options = value.options || {};
                days = [null, false, false, false, false, false, false, false];
                if (options.days) {
                  _ref = options.days;
                  for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    d = _ref[_i];
                    days[d] = true;
                  }
                }
                return {
                  op: value.op || 'is',
                  tz: options.tz || 'UTC',
                  days: days
                };
              },
              getValue: function(model, data) {
                var days, k, v, value, _i, _len, _ref;
                if (model == null) {
                  model = {};
                }
                days = [];
                _ref = model.days;
                for (k = _i = 0, _len = _ref.length; _i < _len; k = ++_i) {
                  v = _ref[k];
                  if (v) {
                    days.push(k);
                  }
                }
                value = {
                  type: 'CheckDayOfWeek',
                  op: 'is',
                  options: {
                    tz: model.tz || 'UTC',
                    days: days,
                    "var": 'now'
                  }
                };
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckDateCreated = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        def = this.getDateInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckTimeOfDay = function(options) {
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
                var time1, time2;
                if (value == null) {
                  value = {};
                }
                options = value.options || {};
                time1 = (options.time1 || '8:0').split(':');
                time2 = (options.time2 || '18:0').split(':');
                return {
                  tz: options.tz || 'UTC',
                  start_hour: time1[0],
                  start_min: time1[1],
                  end_hour: time2[0],
                  end_min: time2[1]
                };
              },
              getValue: function(model, data) {
                var time1, time2, value;
                if (model == null) {
                  model = {};
                }
                time1 = (model.start_hour || '8') + ':' + (model.start_min || '0');
                time2 = (model.end_hour || '18') + ':' + (model.end_min || '0');
                value = {
                  type: 'CheckTimeOfDay',
                  op: 'between',
                  options: {
                    "var": 'now',
                    tz: model.tz || 'UTC',
                    time1: time1,
                    time2: time2
                  }
                };
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckWorkingHours = function(options) {
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
                var _ref, _ref1;
                if (value == null) {
                  value = {};
                }
                return {
                  op: value.op || 'is',
                  set_name: ((_ref = value.options) != null ? _ref.set_name : void 0) || 'default',
                  working_hours: ((_ref1 = value.options) != null ? _ref1.working_hours : void 0) || {}
                };
              },
              getValue: function(model, data) {
                if (model == null) {
                  model = {};
                }
                return {
                  type: 'CheckWorkingHours',
                  op: model.op,
                  options: {
                    set_name: model.set_name || 'default',
                    working_hours: model.working_hours
                  }
                };
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserVar = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-var.html');
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
                  op: value.op || 'isset',
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
                value.type = 'CheckUserVar';
                value.op = model.op || 'isset';
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

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getIsEmailed = function(name, tpl) {
        var me;
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/' + tpl);
          },
          getData: function() {
            return me.loadDataOptions();
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var options;
                if (value == null) {
                  value = {};
                }
                options = (value != null ? value.options : void 0) || {};
                return {
                  op: value.op || 'is',
                  template: options.template || null,
                  with_template: options.template ? true : false
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = name;
                value.op = model.op || 'is';
                value.options = {
                  template: model.with_template && model.template ? model.template : null
                };
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserIsEmailed = function() {
        return this.getIsEmailed('CheckUserIsEmailed', 'type-criteria-userisemailed.html');
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckAgentIsEmailed = function() {
        return this.getIsEmailed('CheckAgentIsEmailed', 'type-criteria-agentisemailed.html');
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckApiKey = function(options) {
        if (options == null) {
          options = {};
        }
        options.propName = 'api_key_id';
        options.dataName = 'api_keys';
        options.operators = ['is', 'not'];
        options.single = true;
        options.optionsFormatter = function(options) {
          var key, name, opts, _i, _len;
          opts = [];
          for (_i = 0, _len = options.length; _i < _len; _i++) {
            key = options[_i];
            name = key.person ? key.person.display_name : 'Super User';
            opts.push({
              value: key.id,
              title: [name, key.note].join(' | ')
            });
          }
          return opts;
        };
        return this.getStandardSelect(options);
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckTicketSatisfaction = function(options) {
        if (options == null) {
          options = {};
        }
        options.propName = 'feedback_rating';
        options.dataName = 'feedback_rating';
        options.operators = ['is', 'isset', 'not_isset', 'changed', 'changed_to'];
        options.single = true;
        options.optionsFormatter = function(options) {
          return [
            {
              value: -1,
              title: 'Negative'
            }, {
              value: 0,
              title: 'Neutral'
            }, {
              value: 1,
              title: 'Positive'
            }
          ];
        };
        return this.getStandardSelect(options);
      };

      return Admin_OptionBuilder_TypesDef_TicketCriteria;

    })(BaseCriteriaTypesDef);
  });

}).call(this);

//# sourceMappingURL=TicketCriteria.js.map
