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
        var f, options, set_options, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2, _ref3, _ref4, _ref5;
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
              value: this.initFieldGetter('CheckTicketField', f)
            });
          }
          if (options.length) {
            set_options.push({
              title: 'Ticket Fields',
              subOptions: options
            });
          }
        }
        options = [];
        options.push({
          title: 'Has attachment',
          value: 'CheckHasAttach'
        });
        options.push({
          title: 'Has attachment type',
          value: 'CheckHasAttachType'
        });
        options.push({
          title: 'Has attachment named',
          value: 'CheckHasAttachName'
        });
        set_options.push({
          title: 'Attachment Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Name',
          value: 'CheckUserName'
        });
        options.push({
          title: 'Email Address',
          value: 'CheckUserEmailAddress'
        });
        options.push({
          title: 'Label',
          value: 'CheckUserLabels'
        });
        options.push({
          title: 'Usergroup',
          value: 'CheckUserUsergroups'
        });
        options.push({
          title: 'Language',
          value: 'CheckUserLanguage'
        });
        options.push({
          title: 'Is manager of organization',
          value: 'CheckUserIsManager'
        });
        options.push({
          title: 'User is new',
          value: 'CheckUserIsNew'
        });
        options.push({
          title: 'User is awaiting agent validation',
          value: 'CheckUserValidAgent'
        });
        options.push({
          title: 'User is awaiting email validation',
          value: 'CheckUserValidEmail'
        });
        options.push({
          title: 'Is disabled',
          value: 'CheckPersonIsDisabled'
        });
        set_options.push({
          title: 'User Criteria',
          subOptions: options
        });
        if ((_ref2 = this.options_data) != null ? _ref2.user_fields : void 0) {
          options = [];
          _ref3 = this.options_data.user_fields;
          for (_j = 0, _len1 = _ref3.length; _j < _len1; _j++) {
            f = _ref3[_j];
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
          title: 'Name',
          value: 'CheckOrgName'
        });
        options.push({
          title: 'Label',
          value: 'CheckOrgLabels'
        });
        options.push({
          title: 'Email Domain',
          value: 'CheckOrgEmailDomain'
        });
        options.push({
          title: 'Linked Usergroup',
          value: 'CheckOrgUsergroups'
        });
        set_options.push({
          title: 'Organization Criteria',
          subOptions: options
        });
        if ((_ref4 = this.options_data) != null ? _ref4.org_fields : void 0) {
          options = [];
          _ref5 = this.options_data.org_fields;
          for (_k = 0, _len2 = _ref5.length; _k < _len2; _k++) {
            f = _ref5[_k];
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
          title: 'Day of week',
          value: 'CheckDayOfWeek'
        });
        options.push({
          title: 'Time of day',
          value: 'CheckTimeOfDay'
        });
        options.push({
          title: 'Within working hours',
          value: 'CheckWorkingHours'
        });
        set_options.push({
          title: 'Dates',
          subOptions: options
        });
        return set_options;
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
              'ticket_accounts': '/email_accounts',
              'usergroups': '/user_groups'
            }).then((function(_this) {
              return function(result) {
                var data, f, options_data, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9, _results;
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
                options_data['email_accounts'] = data.ticket_accounts.email_accounts;
                options_data['usergroups'] = data.usergroups.groups;
                _this.options_data = options_data;
                if ((_ref4 = _this.options_data) != null ? _ref4.ticket_fields : void 0) {
                  _ref5 = _this.options_data.ticket_fields;
                  for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
                    f = _ref5[_i];
                    _this.initFieldGetter('CheckTicketField', f);
                  }
                }
                if ((_ref6 = _this.options_data) != null ? _ref6.user_fields : void 0) {
                  _ref7 = _this.options_data.user_fields;
                  for (_j = 0, _len1 = _ref7.length; _j < _len1; _j++) {
                    f = _ref7[_j];
                    _this.initFieldGetter('CheckUserField', f);
                  }
                }
                if ((_ref8 = _this.options_data) != null ? _ref8.org_fields : void 0) {
                  _ref9 = _this.options_data.org_fields;
                  _results = [];
                  for (_k = 0, _len2 = _ref9.length; _k < _len2; _k++) {
                    f = _ref9[_k];
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
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailBody = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'body';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailToName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'to_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailToAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'to_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailFromName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'from_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailFromAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'from_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckCcAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckCcName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckEmailHeader = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_header_match';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckLabel = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.type_title = 'Labels';
        options.tags = true;
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckAgentMessage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['isset', 'not_isset', 'contains', 'not_contains', 'is_regex', 'not_regex'];
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
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckAgentNote = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['isset', 'not_isset', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserMessage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['isset', 'not_isset', 'contains', 'not_contains', 'is_regex', 'not_regex'];
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
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserEmailAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserLabels = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'not_contains'];
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
        options.dataName = 'languages';
        options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from'];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserIsManager = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_manager';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckPersonIsDisabled = function(options) {
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
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_valid_agent';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckUserValidEmail = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_valid_email';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckOrgName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckOrgLabels = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCheckOrgEmailDomain = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
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

      return Admin_OptionBuilder_TypesDef_TicketCriteria;

    })(BaseCriteriaTypesDef);
  });

}).call(this);

//# sourceMappingURL=TicketCriteria.js.map
