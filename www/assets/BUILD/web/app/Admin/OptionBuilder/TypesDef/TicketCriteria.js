/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/OptionBuilder/TypesDef/BaseCriteriaTypesDef',
], function(
  BaseCriteriaTypesDef
) {
  let Admin_OptionBuilder_TypesDef_TicketCriteria;
  return (Admin_OptionBuilder_TypesDef_TicketCriteria = class Admin_OptionBuilder_TypesDef_TicketCriteria extends BaseCriteriaTypesDef {
    init() {
      return this.options_data = null;
    }

    setWithChangedOps(with_changed_ops) {
      this.with_changed_ops = with_changed_ops;
    }

    getOperators(options) {

      if (!options.operators) {
        return ['is', 'not'];
      }

      if (this.with_changed_ops) {
        return options.operators;
      }

      const ops = [];
      for (let o of Array.from(options.operators)) {
        if ((o !== 'changed') && (o !== 'changed_to') && (o !== 'changed_from')) {
          ops.push(o);
        }
      }

      return ops;
    }


    getOptionsForTypes(types, typesData = null, mode) {
      let f, options;
      const set_options = [];

      //------------------------------
      // Email Criteria
      //------------------------------

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
          title: 'CC(s) were added',
          value: 'CheckEmailCcAdded'
        });

        options.push({
          title: 'Email Header',
          value: 'CheckEmailHeader'
        });

        options.push({
          title: 'Email bounced',
          value: 'CheckEmailIsBounce'
        });

        options.push({
          title: 'Automated email',
          value: 'CheckEmailIsRobot'
        });

        set_options.push({
          title: 'Email Criteria',
          subOptions: options
        });
      }

      //------------------------------
      // Ticket Criteria
      //------------------------------

      options = [];

      options.push({
        title: 'Brand',
        value: 'CheckBrand'
      });

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

      options.push({
        title: 'Workflow',
        value: 'CheckWorkflow'
      });

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
      options.push({
        title: 'New Ticket Charge',
        value: 'CheckTicketCharge'
      });

      options.push({
        title: 'Check Expression [Expert]',
        value: 'CheckExpression'
      });

      if (__guard__(this.options_data != null ? this.options_data.ticket_settings : undefined, x => x.satisfaction_enabled)) {
        options.push({
          title: 'Ticket Satisfaction',
          value: 'CheckTicketSatisfaction'
        });
      }

      set_options.push({
        title: 'Ticket Criteria',
        subOptions: options
      });

      //------------------------------
      // Ticket Fields
      //------------------------------

      options = [];

      if (this.options_data != null ? this.options_data.ticket_fields : undefined) {
        for (f of Array.from(this.options_data.ticket_fields)) {
          options.push({
            title: f.title,
            value: this.initFieldGetter('CheckTicketField', f)
          });
        }
      }

      if (this.options_data != null ? this.options_data.contextual_fields : undefined) {
        for (f of Array.from(this.options_data.contextual_fields)) {
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

      //------------------------------
      // JIRA
      //------------------------------

      if (__guard__(this.options_data != null ? this.options_data.jira_settings : undefined, x1 => x1.enabled)) {
        options = [];

        if ('TriggersUpdate' === mode) { options.push({
          title: 'New JIRA Comment',
          value: 'CheckJIRANewComment'
        }); }

        options.push({
          title: 'Issue Status',
          value: 'CheckJIRAIssueStatus'
        });

        if ('TriggersUpdate' === mode) { options.push({
          title: 'New Linked Issue',
          value: 'CheckJIRANewLinkedIssue'
        }); }

        set_options.push({
          title: 'JIRA',
          subOptions: options
        });
      }

      //------------------------------
      // Person
      //------------------------------

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

      //------------------------------
      // User Fields
      //------------------------------

      if (this.options_data != null ? this.options_data.user_fields : undefined) {
        options = [];

        for (f of Array.from(this.options_data.user_fields)) {
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

      //------------------------------
      // Org
      //------------------------------

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

      //------------------------------
      // Org Fields
      //------------------------------

      if (this.options_data != null ? this.options_data.org_fields : undefined) {
        options = [];

        for (f of Array.from(this.options_data.org_fields)) {
          options.push({
            title: f.title,
            value: this.initFieldGetter('CheckOrgField', f)
          });
        }

        if (options.length) {
          set_options.push({
            title: 'Organization Fields',
            subOptions: options
          });
        }
      }

      //------------------------------
      // Dates
      //------------------------------

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

      //options.push({
      // title: 'Within working hours',
      // value: 'CheckWorkingHours'
      //})

      set_options.push({
        title: 'Dates',
        subOptions: options
      });

      //------------------------------
      // Trigger Control
      //------------------------------

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
        title: 'Check Webhook Variable',
        value: 'CheckWebhookVar'
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

      //------------------------------
      // API Criteria
      //------------------------------

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
    }

    resetData() {
      this.options_data = null;
      return this.loadDataPromise = null;
    }

    loadDataOptions() {
      if (!this.loadDataPromise) {
        this.loadDataPromise = this.Api.sendDataGet({
          agents:             '/agents',
          agent_teams:        '/agent_teams',
          ticket_brands:      '/ticket_brands',
          ticket_deps:        '/ticket_deps',
          ticket_cats:        '/ticket_cats',
          ticket_prods:       '/ticket_prods',
          ticket_pris:        '/ticket_pris',
          ticket_works:       '/ticket_works',
          ticket_fields:      '/ticket_fields',
          ticket_labels:      '/labels/definitions/tickets',
          user_fields:        '/user_fields',
          org_fields:         '/org_fields',
          ticket_slas:        '/ticket_slas',
          ticket_accounts:    '/email_accounts',
          usergroups:         '/user_groups',
          langs:              '/langs',
          email_tpls:         '/email-templates-info',
          api_keys:           '/api_keys',
          ticket_settings:    '/ticket_settings',
          contextual_fields:  '/custom_fields',
          jira_settings    :  '/apps/jira'
        }).then( result => {
          let f;
          const { data } = result;
          const options_data = {};
          options_data['agents']            = data.agents.agents;
          options_data['agent_teams']       = data.agent_teams.agent_teams;
          options_data['ticket_brands']     = data.ticket_brands.brands;
          options_data['ticket_deps']       = data.ticket_deps.departments;
          options_data['ticket_cats']       = data.ticket_cats.categories;
          options_data['ticket_pris']       = data.ticket_pris.priorities;
          options_data['ticket_works']      = data.ticket_works.workflows;
          options_data['ticket_prods']      = data.ticket_prods != null ? data.ticket_prods.products : undefined;
          options_data['ticket_fields']     = data.ticket_fields != null ? data.ticket_fields.custom_fields : undefined;
          options_data['org_fields']        = data.org_fields != null ? data.org_fields.custom_fields : undefined;
          options_data['user_fields']       = data.user_fields != null ? data.user_fields.custom_fields : undefined;
          options_data['ticket_slas']       = data.ticket_slas != null ? data.ticket_slas.slas : undefined;
          options_data['email_accounts']    = data.ticket_accounts.email_accounts;
          options_data['usergroups']        = data.usergroups.groups;
          options_data['langs']             = data.langs != null ? data.langs.languages : undefined;
          options_data['custom_email_tpls'] = data.email_tpls.list['custom'].groups['custom'].templates;
          options_data['api_keys']          = data.api_keys.api_keys;
          options_data['ticket_settings']   = data.ticket_settings != null ? data.ticket_settings.ticket_settings : undefined;
          options_data['contextual_fields'] = data.contextual_fields;
          options_data['jira_settings']     = data.jira_settings;
          options_data['ticket_labels']     = data.ticket_labels;
          this.options_data = options_data;

          if (this.options_data != null ? this.options_data.ticket_fields : undefined) {
            for (f of Array.from(this.options_data.ticket_fields)) {
              this.initFieldGetter('CheckTicketField', f, true);
            }
          }
          if (this.options_data != null ? this.options_data.contextual_fields : undefined) {
            for (f of Array.from(this.options_data.contextual_fields)) {
              this.initFieldGetter('CheckTicketContextualField', f, true);
            }
          }
          if (this.options_data != null ? this.options_data.user_fields : undefined) {
            for (f of Array.from(this.options_data.user_fields)) {
              this.initFieldGetter('CheckUserField', f, true);
            }
          }
          if (this.options_data != null ? this.options_data.org_fields : undefined) {
            return (() => {
              const result1 = [];
              for (f of Array.from(this.options_data.org_fields)) {
                result1.push(this.initFieldGetter('CheckOrgField', f, true));
              }
              return result1;
            })();
          }
        });
      }

      return this.loadDataPromise;
    }

    getCheckWorkflow(options) {
      if (options == null) { options = {}; }
      options.propName = 'workflow_ids';
      options.dataName = 'ticket_works';
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
      options.extraOptions = [
        {title: 'None', value: 0}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckPriority(options) {
      if (options == null) { options = {}; }
      options.propName = 'priority_ids';
      options.dataName = 'ticket_pris';
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
      options.extraOptions = [
        {title: 'None', value: 0}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckUrgency(options) {
      if (options == null) { options = {}; }
      options.propName = 'urgency1';
      options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckCategory(options) {
      if (options == null) { options = {}; }
      options.propName = 'category_ids';
      options.dataName = 'ticket_cats';
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
      options.extraOptions = [
        {title: 'None', value: 0}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckBrand(options) {
      if (options == null) { options = {}; }
      options.propName = 'brand_ids';
      options.dataName = 'ticket_brands';
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckDepartment(options) {
      if (options == null) { options = {}; }
      options.propName = 'department_ids';
      options.dataName = 'ticket_deps';
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckAgent(options) {
      if (options == null) { options = {}; }
      options.propName = 'agent_ids';
      options.dataName = 'agents';
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
      options.extraOptions = [
        {title: 'Unassigned', value: 0},
        {title: 'Current Agent', value: -1}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckPerformer(options) {
      if (options == null) { options = {}; }
      options.propName = 'person_ids';
      options.dataName = 'agents';
      options.operators = ['contains', 'notcontains'];
      options.template = 'OptionBuilder/type-criteria-performer.html';
      options.extraOptions = [
        {title: 'Assigned Agent', value: -1},
        {title: 'Member of assigned team', value: -2},
        {title: 'Follower of the ticket', value: -3}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckPerformerEmail(options) {
      if (options == null) { options = {}; }
      options.propName = 'email';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckAgentTeam(options) {
      if (options == null) { options = {}; }
      options.propName = 'team_ids';
      options.dataName = 'agent_teams';
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
      options.extraOptions = [
        {title: 'No Team', value: 0},
        {title: 'Current Agent\'s Team', value: -1}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckProduct(options) {
      if (options == null) { options = {}; }
      options.propName = 'product_ids';
      options.dataName = 'ticket_prods';
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from'];
      options.extraOptions = [
        {title: 'None', value: 0}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckEmailAccount(options) {
      if (options == null) { options = {}; }
      options.propName = 'email_account_ids';
      options.dataName = 'email_accounts';
      options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from'];
      options.optionsFormatter = function(options) {
        const opts = [];

        for (let acc of Array.from(options)) {
          opts.push({
            value: acc.id,
            title: acc.use_email_address || acc.address
          });
        }

        return opts;
      };

      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckEmailSubject(options) {
      if (options == null) { options = {}; }
      options.propName = 'subject';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckEmailBody(options) {
      if (options == null) { options = {}; }
      options.propName = 'body';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckEmailToName(options) {
      if (options == null) { options = {}; }
      options.propName = 'name';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckEmailToAddress(options) {
      if (options == null) { options = {}; }
      options.propName = 'email';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckEmailFromName(options) {
      if (options == null) { options = {}; }
      options.propName = 'name';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckEmailFromAddress(options) {
      if (options == null) { options = {}; }
      options.propName = 'email';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckEmailCcAddress(options) {
      if (options == null) { options = {}; }
      options.propName = 'email';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckEmailCcAdded(options) {
      if (options == null) { options = {}; }
      options.propName = 'ccs_added';
      const def = this.getStandardIs(options);
      return def;
    }

    getCheckEmailCcName(options) {
      if (options == null) { options = {}; }
      options.propName = 'name';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckEmailHeader(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-emailheader.html');
        },

        getData() {
          return {

          };
        },

        getDataFormatter() {
          return {
          getViewValue(value, data) {
            if (value == null) { value = {}; }
            return {
              op: value.op || 'is',
              name: (value.options != null ? value.options.name : undefined) || '',
              value: (value.options != null ? value.options.value : undefined) || '',
            };
          },

          getValue(model, data) {
            if (model == null) { model = {}; }
            const value = {
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
    }

    getCheckEmailIsBounce(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
              op: value.op || 'is',
              options: [
                { value: 'is', title: 'Email message IS a bounced message' },
                { value: 'not', title: 'Email message IS NOT a bounced message' }
              ]
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              return {
              type: 'CheckEmailIsBounce',
              op: model.op || 'is',
              options: { run:true }
              };
            }
          };
        }
      };
    }

    getCheckEmailIsRobot(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
              op: value.op || 'is',
              options: [
                { value: 'is', title: 'Email message IS an automated message' },
                { value: 'not', title: 'Email message IS NOT an automated message' }
              ]
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              return {
                type: 'CheckEmailIsRobot',
                op: model.op || 'is',
                options: { run:true }
              };
            }
          };
        }
      };
    }

    getCheckLabel(options) {
      if (options == null) { options = {}; }
      options.propName = 'labels';
      options.type_title = 'Labels';
      options.tags = true;
      options.options = [];
      this.options_data.ticket_labels.map(def => options.options.push({title: def.label, value: def.label}));
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckSlaStatus(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-slas.html');
        },

        getData() {
          const defer = me.$q.defer();
          me.loadDataOptions().then(function() {
            options = [];
            for (let sla of Array.from(me.options_data['ticket_slas'])) {
              options.push({
                title: sla.title,
                value: sla.id
              });
            }

            return defer.resolve({
              options
            });
          });

          return defer.promise;
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = value.options || {};
              return {
                op:          value.op || 'contains',
                sla_ids:     options.sla_ids || [],
                is_complete: !!options.is_complete,
                sla_status:  options.sla_status || 'passing',
                show_status: !!options.sla_status
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {
                type: 'CheckSlaStatus',
                op: model.op || 'contains',
                options: {
                  sla_ids: model.sla_ids || [],
                  is_complete: model.op === 'contains' ? !!model.is_complete : null,
                  sla_status: model.show_status && model.sla_status && (model.op === 'contains') ? model.sla_status : null
                }
              };
              return value;
            }
            };
        }
      };
    }

    getCheckStatus(options) {
      if (options == null) { options = {}; }
      options.propName = 'status';
      options.template = 'OptionBuilder/type-criteria-status.html';
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckSubject(options) {
      if (options == null) { options = {}; }
      options.propName = 'subject';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckAgentMessage(options) {
      if (options == null) { options = {}; }
      options.propName = 'message';
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckTicketCharge(options) {
      if (options == null) { options = {}; }
      options.propName = 'amount';
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex', 'is', 'not', 'gt', 'gte', 'lt', 'lte'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckExpression(options) {
      if (options == null) { options = {}; }
      options.propName = 'expr';
      options.operators = ['is', 'not'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckCreationSystem(options) {
      if (options == null) { options = {}; }
      options.propName = 'creation_system';
      options.operators = ['is', 'not'];
      options.options = [
        {title: "Created by a user via the portal", value: "web.person.portal"},
        {title: "Created by a user via the widget", value: "web.person.widget"},
        {title: "Created by a user via an embedded form", value: "web.person.embed"},
        {title: "Created by a user via email", value: "gateway.person"},
        {title: "Created by an agent via the agent interface", value: "web.agent.portal"},
        {title: "Created by an agent via email", value: "gateway.agent"},
        {title: "Create by the API in a user context", value: "web.api.person"},
        {title: "Create by the API in an agent context", value: "web.api.agent"},
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckCreationSystemOption(options) {
      if (options == null) { options = {}; }
      options.propName = 'creation_system_option';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckAgentNote(options) {
      if (options == null) { options = {}; }
      options.propName = 'message';
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckUserMessage(options) {
      if (options == null) { options = {}; }
      options.propName = 'message';
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckHasAttach(options) {
      if (options == null) { options = {}; }
      options.propName = 'with_attach';
      const def = this.getStandardIs(options);
      return def;
    }

    getCheckHasAttachType(options) {
      if (options == null) { options = {}; }
      options.propName = 'attach_type';
      options.operators = ['is', 'not'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckHasAttachName(options) {
      if (options == null) { options = {}; }
      options.propName = 'attach_name';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckUserName(options) {
      if (options == null) { options = {}; }
      options.propName = 'name';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckUserId(options) {
      if (options == null) { options = {}; }
      options.propName = 'id';
      options.operators = ['is', 'not'];
      options.url = '/people/quick_search';
      options.map = data =>
        ({
          id: data.person.id,
          email: (data.person.primary_email != null ? data.person.primary_email.email : undefined),
          first_name: data.person.first_name,
          last_name: data.person.last_name,
          name: data.person.name
        })
      ;
      const format = function(item) {
        if (item.name && item.email) {
          return `${item['name'] || ''} (${item.email || ''})`;
        } else if (item.name) {
          return `${item['name'] || ''}`;
        } else if (item.email) {
          return `${item.email || ''}`;
        } else {
          return "";
        }
      };
      options.inputOptions = {
        formatResult: format,
        formatSelection: format,
        ajax: {
          data(term, page) { return { query: term, limit: 10, with_agents: false, start_with: true }; }
        }
      };
      return this.getRemoteInput(options);
    }

    getCheckUserEmail(options) {
      if (options == null) { options = {}; }
      options.propName = 'email';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      options.url = '/people/quick_search_email';
      options.hardcodedSkipLoadById = true;
      options.map = email => ({id: email, email});
      const format = item => (item != null ? item.email : undefined) || '';
      options.inputOptions = {
        formatResult: format,
        formatSelection: format,
        tags: true,
        createSearchChoice(term) { return {id: term, email: term}; },
        ajax: {
          data(term, page) { return { query: term, limit: 10 }; }
        }
      };
      return this.getRemoteInput(options);
    }

    getCheckUserLabel(options) {
      if (options == null) { options = {}; }
      options.propName = 'labels';
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckUserUsergroups(options) {
      if (options == null) { options = {}; }
      options.propName = 'usergroup_ids';
      options.dataName = 'usergroups';
      options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckUserLanguage(options) {
      if (options == null) { options = {}; }
      options.propName = 'language_ids';
      options.dataName = 'langs';
      options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckUserOrgManager(options) {
      if (options == null) { options = {}; }
      options.propName = 'is_manager';
      const def = this.getStandardIs(options);
      return def;
    }

    getCheckUserIsDisabled(options) {
      if (options == null) { options = {}; }
      options.propName = 'is_disabled';
      const def = this.getStandardIs(options);
      return def;
    }

    getCheckUserIsNew(options) {
      if (options == null) { options = {}; }
      options.propName = 'is_new';
      const def = this.getStandardIs(options);
      return def;
    }

    getCheckUserValidAgent(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
                op: value.op || 'is',
                options: [
                  { value: 'is', title: 'User has been validated by an agent' },
                  { value: 'not', title: 'User is waiting to be validated by an agent' }
                ]
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              return {
                type: 'CheckUserValidAgent',
                op: model.op || 'is',
                options: { run:true }
              };
            }
          };
        }
      };
    }

    getCheckUserValidEmail(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
              op: value.op || 'is',
              options: [
                { value: 'is', title: 'User has validated their email address' },
                { value: 'not', title: 'User has not yet validated their email address' }
              ]
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              return {
              type: 'CheckUserValidEmail',
              op: model.op || 'is',
              options: { run:true }
              };
            }
          };
        }
      };
    }

    getCheckOrgName(options) {
      if (options == null) { options = {}; }
      options.propName = 'name';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckOrgId(options) {
      if (options == null) { options = {}; }
      options.propName = 'id';
      options.operators = ['is', 'not', 'isset', 'not_isset'];
      options.url = '/organizations';
      options.map = data =>
        ({
          id: (data.organization != null ? data.organization.id : undefined),
          name: (data.organization != null ? data.organization.name : undefined)
        })
      ;
      const format = item => item['name'];
      options.inputOptions = {
        formatResult: format,
        formatSelection: format,
        ajax: {
          data(term, page) { return { name: term, limit: 10 }; },
          results(data, page) {
            const results = [];
            const obj = data.data != null ? data.data.organizations : undefined;
            for (let k of Object.keys(obj || {})) {
              const v = obj[k];
              results.push({id: v.id, name: v.name});
            }
            return {results};
          }
        }
      };
      return this.getRemoteInput(options);
    }

    getCheckOrgLabel(options) {
      if (options == null) { options = {}; }
      options.propName = 'labels';
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckOrgEmailDomain(options) {
      if (options == null) { options = {}; }
      options.propName = 'email_domain';
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      const def = this.getStandardInput(options);
      return def;
    }

    getCheckOrgUsergroups(options) {
      if (options == null) { options = {}; }
      options.propName  = 'usergroup_ids';
      options.dataName  = 'usergroups';
      options.operators = ['is', 'not'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckDayOfWeek(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
      getTemplate() {
        return me.dpTemplateManager.get('OptionBuilder/type-criteria-dayofweek.html');
      },

      getData() {
        return {};
      },

      getDataFormatter() {
        return {
          getViewValue(value, data) {
            if (value == null) { value = {}; }
            options = value.options || {};
            const days = [null, false, false, false, false, false, false, false];
            if (options.days) {
              for (let d of Array.from(options.days)) {
                days[d] = true;
              }
            }

            return {
              op: value.op || 'is',
              tz: options.tz || 'UTC',
              days
            };
          },

          getValue(model, data) {
            if (model == null) { model = {}; }
            const days = [];
            for (let k = 0; k < model.days.length; k++) {
              const v = model.days[k];
              if (v) {
                days.push(k);
              }
            }

            const value = {
              type: 'CheckDayOfWeek',
              op: 'is',
              options:{
                tz: model.tz || 'UTC',
                days,
                var: 'now'
              }
            };
            return value;
          }
          };
      }
      };
    }

    getCheckDateCreated(options) {
      if (options == null) { options = {}; }
      const def = this.getDateInput(options);
      return def;
    }

    getCheckTimeOfDay(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-timeofday.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = value.options || {};

              const time1 = (options.time1 || '8:0').split(':');
              const time2 = (options.time2 || '18:0').split(':');

              return {
                tz:          options.tz || 'UTC',
                start_hour:  time1[0],
                start_min:   time1[1],
                end_hour:    time2[0],
                end_min:     time2[1]
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const time1 = (model.start_hour || '8') + ':' + (model.start_min || '0');
              const time2 = (model.end_hour || '18') + ':' + (model.end_min || '0');

              const value = {
                type: 'CheckTimeOfDay',
                op: 'between',
                options: {
                  var:   'now',
                  tz:    model.tz || 'UTC',
                  time1,
                  time2
                }
              };
              return value;
            }
          };
        }
      };
    }

    getCheckWorkingHours(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-workinghours.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
                op: value.op || 'is',
                set_name: (value.options != null ? value.options.set_name : undefined) || 'default',
                working_hours: (value.options != null ? value.options.working_hours : undefined) || {}
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
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
    }

    getCheckWebhookVar(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-var.html');
        },

        getData() {
          return {
            label : 'Check Webhook Var'
          };
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};
              return {
                op: value.op || 'isset',
                name: options.name || '',
                value: options.value || ''
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'CheckWebhookVar';
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
    }

    getCheckUserVar(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-var.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};
              return {
                op: value.op || 'isset',
                name: options.name || '',
                value: options.value || ''
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
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
    }

    getIsEmailed(name, tpl) {
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get(`OptionBuilder/${tpl}`);
        },

        getData() {
          return me.loadDataOptions();
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              const options = (value != null ? value.options : undefined) || {};
              return {
                op: value.op || 'is',
                template: options.template || null,
                custom_email_tpls: (me.options_data != null ? me.options_data.custom_email_tpls : undefined),
                with_template: options.template ? true : false
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
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
    }

    getCheckUserIsEmailed() {
      return this.getIsEmailed('CheckUserIsEmailed', 'type-criteria-userisemailed.html');
    }

    getCheckAgentIsEmailed() {
      return this.getIsEmailed('CheckAgentIsEmailed', 'type-criteria-agentisemailed.html');
    }

    getCheckApiKey(options) {
      if (options == null) { options = {}; }
      options.propName = 'api_key_id';
      options.dataName = 'api_keys';
      options.operators = ['is', 'not'];
      options.single = true;
      options.optionsFormatter = function(options) {
        const opts = [];

        for (let key of Array.from(options)) {
          const name = key.person ? key.person.display_name : 'Super User';
          opts.push({
            value: key.id,
            title: [name, key.note].join(' | ')
          });
        }

        return opts;
      };

      return this.getStandardSelect(options);
    }

    getCheckTicketSatisfaction(options) {
      if (options == null) { options = {}; }
      options.propName = 'feedback_rating';
      options.dataName = 'feedback_rating';
      options.operators = ['is', 'isset', 'not_isset', 'changed', 'changed_to'];
      options.single = true;
      options.optionsFormatter = options => [{value: -1, title: 'Negative'}, {value: 0, title: 'Neutral'}, {value: 1, title: 'Positive'}];
      return this.getStandardSelect(options);
    }

    getCheckJIRANewComment(options) {
      if (options == null) { options = {}; }
      options.propName = 'message';
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex'];
      return this.getStandardInput(options);
    }

    getCheckJIRAIssueStatus(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() { return me.dpTemplateManager.get('OptionBuilder/type-criteria-jira-issue-status.html'); },
        getData() { return {}; },
        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
                op: value.op || 'changed',
                all: (value.options != null ? value.options.all : undefined),
                status: (value.options != null ? value.options.status : undefined),
                statuses: me.options_data.jira_settings.meta.statuses
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              return {
                type: 'CheckJIRAIssueStatus',
                op: model.op,
                options: {
                  all: model.all || '',
                  status: model.status
                }
              };
            }
          };
        }
      };
    }

    getCheckJIRANewLinkedIssue(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() { return me.dpTemplateManager.get('OptionBuilder/type-criteria-jira-linked-issue.html'); },
        getData() { return {}; },
        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
                strict_project: ((value.options != null ? value.options.project : undefined) != null),
                project: (value.options != null ? value.options.project : undefined),
                projects: me.options_data.jira_settings.meta.projects
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              let { project } = model;
              if (!model.strict_project) { project = null; }
              return {
                type: 'CheckJIRANewLinkedIssue',
                op: 'is',
                options: {
                  project
                }
              };
            }
          };
        }
      };
    }
  });
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}