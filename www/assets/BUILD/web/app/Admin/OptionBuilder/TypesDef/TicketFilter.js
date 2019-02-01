// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
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
  class Admin_OptionBuilder_TypesDef_TicketFilter extends BaseCriteriaTypesDef {
    init() {
      return this.options_data = null;
    }

    getOperators(options) {
      return options.operators || ['is', 'not'];
    }

    getOptionsForTypes(types, typesData = null) {
      let f;
      const set_options = [];
      //------------------------------
      // Ticket Criteria
      //------------------------------

      let options = [];

      options.push({
        title: 'Ticket ID',
        value: 'FilterRangeId'
      });

      options.push({
        title: 'Ticket Ref',
        value: 'FilterRef'
      });

      options.push({
        title: 'Status',
        value: 'FilterStatus'
      });

      options.push({
        title: 'Brand',
        value: 'FilterBrand'
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
        title: 'Followers',
        value: 'FilterAgentParticipant'
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
        title: 'Urgency',
        value: 'FilterUrgency'
      });

      options.push({
        title: 'Workflow',
        value: 'FilterWorkflow'
      });

      options.push({
        title: 'Labels',
        value: 'FilterLabels'
      });

      options.push({
        title: 'Linked feedback items',
        value: 'FilterFeedbackLinks'
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
        value: 'FilterDateArchived'
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

      options.push({
        title: 'Ticket SLA',
        value: 'FilterSla'
      });

      options.push({
        title: 'Ticket SLA Status',
        value: 'FilterSlaStatus'
      });

      set_options.push({
        title: 'Ticket Criteria',
        subOptions: options
      });

      //------------------------------
      // Ticket Fields
      //------------------------------

      if (this.options_data != null ? this.options_data.ticket_fields : undefined) {
        options = [];

        for (f of Array.from(this.options_data.ticket_fields)) {
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

      //------------------------------
      // User Fields
      //------------------------------

      if (this.options_data != null ? this.options_data.user_fields : undefined) {
        options = [];

        for (f of Array.from(this.options_data.user_fields)) {
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

      //------------------------------
      // Org Fields
      //------------------------------

      if (this.options_data != null ? this.options_data.org_fields : undefined) {
        options = [];

        for (f of Array.from(this.options_data.org_fields)) {
          options.push({
            title: f.title,
            value: this.initFieldGetter('FilterOrgField', f)
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
      // Person
      //------------------------------

      options = [];

      options.push({
        title: 'Person ID',
        value: 'FilterUserRangeId'
      });

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
        value: 'FilterUserGroups'
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

      //------------------------------
      // Org
      //------------------------------

      options = [];

      options.push({
        title: 'Organization',
        value: 'FilterOrgId'
      });

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
    }

    resetData() {
      this.options_data = null;
      return this.loadDataPromise = null;
    }

    loadDataOptions() {
      if (!this.loadDataPromise) {
        this.loadDataPromise = this.Api.sendDataGet({
          agents:          '/agents',
          agent_teams:      '/agent_teams',
          ticket_brands:      '/ticket_brands',
          ticket_deps:      '/ticket_deps',
          ticket_cats:      '/ticket_cats',
          ticket_prods:     '/ticket_prods',
          ticket_pris:      '/ticket_pris',
          ticket_works:     '/ticket_works',
          ticket_fields:    '/ticket_fields',
          ticket_slas:      '/ticket_slas',
          ticket_labels:    '/labels/definitions/tickets',
          user_fields:      '/user_fields',
          user_labels:      '/labels/definitions/people',
          org_fields:       '/org_fields',
          org_labels:       '/labels/definitions/organizations',
          ticket_accounts:  '/email_accounts',
          usergroups:       '/user_groups',
          organizations:    '/organizations?per_page=250'
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
          options_data['ticket_fields']     = data.ticket_fields != null ? data.ticket_fields.custom_fields : undefined;
          options_data['ticket_slas']       = data.ticket_slas;
          options_data['ticket_labels']     = data.ticket_labels;
          options_data['org_fields']        = data.org_fields != null ? data.org_fields.custom_fields : undefined;
          options_data['org_labels']        = data.org_labels;
          options_data['user_fields']       = data.user_fields != null ? data.user_fields.custom_fields : undefined;
          options_data['user_labels']       = data.user_labels;
          options_data['ticket_prods']      = data.ticket_prods != null ? data.ticket_prods.products : undefined;
          options_data['ticket_accounts']   = data.ticket_accounts.email_accounts;
          options_data['usergroups']        = data.usergroups.groups;
          options_data['organizations']     = data.organizations.organizations;

          this.options_data = options_data;

          if (this.options_data != null ? this.options_data.ticket_fields : undefined) {
            for (f of Array.from(this.options_data.ticket_fields)) {
              this.initFieldGetter('FilterTicketField', f, true);
            }
          }
          if (this.options_data != null ? this.options_data.user_fields : undefined) {
            for (f of Array.from(this.options_data.user_fields)) {
              this.initFieldGetter('FilterUserField', f, true);
            }
          }
          if (this.options_data != null ? this.options_data.org_fields : undefined) {
            return (() => {
              const result1 = [];
              for (f of Array.from(this.options_data.org_fields)) {
                result1.push(this.initFieldGetter('FilterOrgField', f, true));
              }
              return result1;
            })();
          }
        });
      }

      return this.loadDataPromise;
    }

    getFilterWorkflow(options) {
      if (options == null) { options = {}; }
      options.propName = 'workflow_ids';
      options.dataName = 'ticket_works';
      options.extraOptions = [
        {title: 'None', value: 0}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterLabels(options) {
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

    getFilterUrgency(options) {
      if (options == null) { options = {}; }
      options.propName = 'urgency';
      options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterPriority(options) {
      if (options == null) { options = {}; }
      options.propName = 'priority_ids';
      options.dataName = 'ticket_pris';
      options.extraOptions = [
        {title: 'None', value: 0}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getCheckUrgency(options) {
      if (options == null) { options = {}; }
      options.propName = 'urgency';
      options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterCategory(options) {
      if (options == null) { options = {}; }
      options.propName = 'category_ids';
      options.dataName = 'ticket_cats';
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterStatus(options) {
      if (options == null) { options = {}; }
      options.propName = 'status';
      options.template = 'OptionBuilder/type-filter-status.html';
      options.noArchive = true;
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterHoldStatus(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-filter-hold.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
                op: 'is',
                value: (value.options != null ? value.options.is_hold : undefined) ? '1' : '0'
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
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
    }

    getFilterSla(options) {
      if (options == null) { options = {}; }
      options.propName = 'sla_id';
      options.dataName = 'ticket_slas';
      options.optionsFormatter = res => (res.slas || []).map(item => ({title: item.title, value: item.id}));
      return this.getStandardSelect(options);
    }

    getFilterSlaStatus(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-filter-sla.html');
        },

        getData() {
          const defer = me.$q.defer();
          me.loadDataOptions().then(function() {
            options = [];
            for (let sla of Array.from((me.options_data.ticket_slas != null ? me.options_data.ticket_slas.slas : undefined))) {
              options.push({
                title: sla.title,
                value: sla.id
              });
            }
            return defer.resolve({options});});
          return defer.promise;
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = value.options || {};
              return {
                op:           value.op || 'is',
                sla_status:   options.sla_status || 'fail',
                sla_id:       options.sla_id || 0
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              return {
                type:         'FilterSlaStatus',
                op:           model.op,
                options: {
                  sla_status: model.sla_status || 'fail',
                  sla_id:     model.sla_id || 0
                }
              };
            }
          };
        }
      };
    }

    getFilterRangeId(options) {
      if (options == null) { options = {}; }
      options.propName = 'id';
      options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte'];
      let def = (options);
      def = this.getStandardInput(options);
      return def;
    }

    getFilterRef(options) {
      if (options == null) { options = {}; }
      options.propName = 'ref';
      let def = (options);
      def = this.getStandardInput(options);
      return def;
    }

    getFilterUserRangeId(options) {
      if (options == null) { options = {}; }
      options.propName = 'person_id';
      options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte'];
      let def = (options);
      def = this.getStandardInput(options);
      return def;
    }

    getFilterUserWaiting(options) {
      if (options == null) { options = {}; }
      options.propName = 'time';
      const def = this.getTimeElapsedInput(options);
      return def;
    }

    getFilterTotalUserWaiting(options) {
      if (options == null) { options = {}; }
      options.propName = 'time';
      const def = this.getTimeElapsedInput(options);
      return def;
    }

    getFilterDateCreated(options) {
      if (options == null) { options = {}; }
      const def = this.getDateInput(options);
      return def;
    }

    getFilterDateResolved(options) {
      if (options == null) { options = {}; }
      const def = this.getDateInput(options);
      return def;
    }

    getFilterDateArchived(options) {
      if (options == null) { options = {}; }
      const def = this.getDateInput(options);
      return def;
    }

    getFilterDateLastAgentReply(options) {
      if (options == null) { options = {}; }
      const def = this.getDateInput(options);
      return def;
    }

    getFilterDateLastUserReply(options) {
      if (options == null) { options = {}; }
      const def = this.getDateInput(options);
      return def;
    }

    getFilterBrand(options) {
      if (options == null) { options = {}; }
      options.propName = 'brand_ids';
      options.dataName = 'ticket_brands';
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterDepartment(options) {
      if (options == null) { options = {}; }
      options.propName = 'department_ids';
      options.dataName = 'ticket_deps';
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterAgent(options) {
      if (options == null) { options = {}; }
      options.propName = 'agent_ids';
      options.dataName = 'agents';
      options.extraOptions = [
        {title: 'Unassigned', value: 0},
        {title: 'Current Agent', value: -1}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterAgentParticipant(options) {
      if (options == null) { options = {}; }
      options.propName = 'agent_ids';
      options.dataName = 'agents';
      options.extraOptions = [
        {title: 'Current Agent', value: -1}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterAgentTeam(options) {
      if (options == null) { options = {}; }
      options.propName = 'team_ids';
      options.dataName = 'agent_teams';
      options.extraOptions = [
        {title: 'No Team', value: 0},
        {title: 'Current Agent\'s Team', value: -1}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterProduct(options) {
      if (options == null) { options = {}; }
      options.propName = 'product_ids';
      options.dataName = 'ticket_prods';
      options.extraOptions = [
        {title: 'None', value: 0}
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterEmailAccount(options) {
      if (options == null) { options = {}; }
      options.propName = 'email_account_ids';
      options.dataName = 'ticket_accounts';
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

    getFilterCcAddress(options) {
      if (options == null) { options = {}; }
      options.propName = 'cc_address';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterCcName(options) {
      if (options == null) { options = {}; }
      options.propName = 'cc_name';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterEmailHeader(options) {
      if (options == null) { options = {}; }
      options.propName = 'email_header_match';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterSubject(options) {
      if (options == null) { options = {}; }
      options.propName = 'subject';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterFeedbackLinks(options) {
      if (options == null) { options = {}; }
      options.propName = 'feedback_links';
      options.operators = ['isset', 'not_isset', 'is'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterMessage(options) {
      if (options == null) { options = {}; }
      options.propName = 'message';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterHasAttach(options) {
      if (options == null) { options = {}; }
      options.propName = 'with_attach';
      const def = this.getStandardIs(options);
      return def;
    }

    getFilterHasAttachType(options) {
      if (options == null) { options = {}; }
      options.propName = 'attach_type';
      options.operators = ['is', 'not'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterHasAttachName(options) {
      if (options == null) { options = {}; }
      options.propName = 'attach_name';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterUserName(options) {
      if (options == null) { options = {}; }
      options.propName = 'name';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterUserEmailAddress(options) {
      if (options == null) { options = {}; }
      options.propName = 'email';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterUserLabels(options) {
      if (options == null) { options = {}; }
      options.propName = 'labels';
      options.options = [];
      this.options_data.user_labels.map(def => options.options.push({title: def.label, value: def.label}));
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterUserGroups(options) {
      if (options == null) { options = {}; }
      options.propName = 'group_ids';
      options.dataName = 'usergroups';
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterUserLanguage(options) {
      if (options == null) { options = {}; }
      options.propName = 'language_ids';
      options.dataName = 'languages';
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterUserIsManager(options) {
      if (options == null) { options = {}; }
      options.propName = 'is_manager';
      const def = this.getStandardIs(options);
      return def;
    }

    getFilterUserIsDisabled(options) {
      if (options == null) { options = {}; }
      options.propName = 'is_disabled';
      const def = this.getStandardIs(options);
      return def;
    }

    getFilterUserContactPhone(options) {
      if (options == null) { options = {}; }
      options.propName = 'phone';
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterUserContactAddress(options) {
      if (options == null) { options = {}; }
      options.propName = 'address';
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterUserContactIm(options) {
      if (options == null) { options = {}; }
      options.propName = 'im';
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterUserDateCreated(options) {
      if (options == null) { options = {}; }
      const def = this.getDateInput(options);
      return def;
    }

    getFilterOrgId(options) {
      if (options == null) { options = {}; }
      options.propName = 'id';
      options.operators = ['is', 'not'];
      options.url = '/organizations';
      options.isMulti = true;
      options.map = data => ({id: data.organization.id, name: data.organization.name});
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

    getFilterOrgName(options) {
      if (options == null) { options = {}; }
      options.propName = 'name';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterOrgLabels(options) {
      if (options == null) { options = {}; }
      options.propName = 'labels';
      options.options = [];
      this.options_data.org_labels.map(def => options.options.push({title: def.label, value: def.label}));
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterOrgContactPhone(options) {
      if (options == null) { options = {}; }
      options.propName = 'phone';
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterOrgContactAddress(options) {
      if (options == null) { options = {}; }
      options.propName = 'address';
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterOrgContactIm(options) {
      if (options == null) { options = {}; }
      options.propName = 'im';
      options.operators = ['contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterOrgEmailDomain(options) {
      if (options == null) { options = {}; }
      options.propName = 'domain';
      options.operators = ['is', 'not', 'contains', 'notcontains'];
      const def = this.getStandardInput(options);
      return def;
    }

    getFilterOrgGroups(options) {
      if (options == null) { options = {}; }
      options.propName  = 'group_ids';
      options.dataName  = 'usergroups';
      options.operators = ['is', 'not'];
      const def = this.getStandardSelect(options);
      return def;
    }

    getFilterOrgDateCreated(options) {
      if (options == null) { options = {}; }
      const def = this.getDateInput(options);
      return def;
    }

    getFilterDayOfWeek(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
      getTemplate() {
        return me.dpTemplateManager.get('OptionBuilder/type-criteria-dayofweek.html');
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
            op: value.op || 'is'
          };
        },

        getValue(model, data) {
          if (model == null) { model = {}; }
          const value = {};
          return value;
        }
        };
      }
      };
    }

    getFilterTimeOfDay(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
      getTemplate() {
        return me.dpTemplateManager.get('OptionBuilder/type-criteria-timeofday.html');
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
          op: value.op || 'is'
          };
        },

        getValue(model, data) {
          if (model == null) { model = {}; }
          const value = {};
          return value;
        }
        };
      }
      };
    }

    getFilterWorkingHours(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-workinghours.html');
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
                op: value.op || 'is'
              };
            },

          getValue(model, data) {
            if (model == null) { model = {}; }
            const value = {};
            return value;
          }
          };
        }
      };
    }
  }
  return Admin_OptionBuilder_TypesDef_TicketFilter;
});
