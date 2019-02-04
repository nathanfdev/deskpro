define([
  'Admin/OptionBuilder/TypesDef/BaseActionTypesDef',
  'DeskPRO/Util/Numbers',
  '../../../../bower_components/moment/moment'
], (
  BaseActionTypesDef,
  Numbers,
  moment
) => {
  class Admin_OptionBuilder_TypesDef_TicketFilter extends BaseActionTypesDef {
    init() {
      return this.options_data = null;
    }

    getOptionsForTypes(types, typesData = null, mode) {
      let f;
      if (types == null) { types = []; }
      const set_options = [];

      //------------------------------
      // Ticket Assignment
      //------------------------------

      let options = [];
      options.push({
        title: 'Set Assigned Agent',
        value: 'SetAgent'
      });

      if (((this.options_data != null ? this.options_data.round_robin : undefined) != null) && (this.options_data != null ? this.options_data.round_robin.enabled : undefined)) {
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
        title:      'Ticket Assignment',
        subOptions: options
      });

      //------------------------------
      // Ticket Properties
      //------------------------------

      options = [];

      options.push({
        title: 'Set Status',
        value: 'SetStatus'
      });

      options.push({
        title: 'Set Brand',
        value: 'SetBrand'
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
        title:      'Ticket Properties',
        subOptions: options
      });

      //------------------------------
      // Ticket SLAs
      //------------------------------

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
        title:      'Ticket SLAs',
        subOptions: options
      });

      //------------------------------
      // Ticket Actions
      //------------------------------

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
        title: 'Delete Ticket Attachments',
        value: 'DeleteAttachments'
      });

      options.push({
        title: 'Delete Voice Phone Call Records',
        value: 'DeleteVoicePhoneCallRecords'
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
        title: 'Set Hold',
        value: 'SetHold'
      });

      options.push({
        title: 'Call Web Hook',
        value: 'WebHook'
      });

      options.push({
        title: 'Call Web Hook (Custom JSON Payload)',
        value: 'WebHook2'
      });

      options.push({
        title: 'Add brand to user',
        value: 'AddBrandToPerson'
      });

      set_options.push({
        title:      'Ticket Actions',
        subOptions: options
      });

      //------------------------------
      // Ticket Actions
      //------------------------------

      options = [];

      if (window.DP_HAS_NEW_EMAILS) {
        options.push({
          title: 'Send Email To User',
          value: 'SendUserNewEmail'
        });

        options.push({
          title: 'Send Email To Agents',
          value: 'SendAgentNewEmail'
        });

        options.push({
          title: 'Send Email to a specific email address',
          value: 'SendSpecificUserNewEmail'
        });
      } else {
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
      }

      set_options.push({
        title:      'Send Email',
        subOptions: options
      });

      //------------------------------
      // JIRA Actions
      //------------------------------

      if (__guard__(this.options_data != null ? this.options_data.jira_settings : undefined, x => x.enabled)) {
        options = [];

        options.push({
          title: 'Add Comment to linked JIRA issues',
          value: 'AddJIRAComment'
        });

        set_options.push({
          title:      'JIRA Actions',
          subOptions: options
        });
      }

      //------------------------------
      // Trigger Control
      //------------------------------

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
        title:      'Trigger Control',
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
            value: this.initFieldGetter('SetTicketField', f)
          });
        }
      }

      if (this.options_data != null ? this.options_data.contextual_fields : undefined) {
        for (f of Array.from(this.options_data.contextual_fields)) {
          options.push({
            title: f.title,
            value: this.initFieldGetter('SetTicketContextualField', f)
          });
        }
      }

      if (options.length) {
        set_options.push({
          title:      'Ticket Fields',
          subOptions: options
        });
      }

      //------------------------------
      // User Fields
      //------------------------------

      if (this.options_data != null ? this.options_data.user_fields : undefined) {
        options = [];

        for (f of Array.from(this.options_data.user_fields)) {
          options.push({
            title: f.title,
            value: this.initFieldGetter('SetUserField', f)
          });
        }

        if (options.length) {
          set_options.push({
            title:      'Person Fields',
            subOptions: options
          });
        }
      }

      //------------------------------
      // Tasks
      //------------------------------

      if (__guard__(this.options_data != null ? this.options_data.tasks : undefined, x1 => x1.enabled)) {
        options = [];

        options.push({
          title: 'Create Task',
          value: 'CreateTask'
        });

        set_options.push({
          title:      'Tasks',
          subOptions: options
        });
      }

      //------------------------------
      // Dynamic Options
      //------------------------------

      if ((typesData != null ? typesData.dynamicOptions : undefined) != null) {
        options = [];

        for (var opt of Array.from(typesData.dynamicOptions)) {
          options.push({
            title: opt.action_title,
            value: opt.action_name
          });

          const typeFunc = `get${opt.action_name}`;

          //------------------------------
          // Abstract SMS Options - if your action begins with "Sms"
          //------------------------------

          if (opt.action_name.indexOf('Sms') === 0) {
            this[typeFunc] = this.generateSmsAction(opt.app.title, opt);

          //------------------------------
          // Dynamic Options - All except for "SendSms" actions above
          //------------------------------
          } else {
            this[typeFunc] = function (options) {
              if (options == null) { options = {}; }
              const me = this;
              return {
                getTemplate() {
                  return me.dpTemplateManager.get(opt.builder_template);
                },
                getData() {
                  return {};
                },
                getDataFormatter() {
                  return {
                    getViewValue(value, data) {
                      if (value == null) { value = {}; }
                      return value.options || {};
                    },
                    getValue(model, data) {
                      if (model == null) { model = {}; }
                      const value = {};
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
            title:      'Other Actions',
            subOptions: options
          });
        }
      }

      return set_options;
    }

    generateSmsAction(app_title, opt) {
      return function (options) {
        if (options == null) { options = {}; }
        let me = this;
        return {
          scopeInit: ['$scope', function ($scope) {
            $scope.sms_num_characters = 0;
            $scope.sms_vars = [];
            $scope.sms_app_name = app_title;

            me = this;
            // TODO: Make this reusable in other areas of the admin area
            return $scope.calculateCharacterLength = function () {
              let proposed_length;
              const tmp_string = $scope.model.message;

              const matches = tmp_string.match(/(\{\{.*?\}\})/gi);
              const countable_string = tmp_string.replace(/(\{\{.*?\}\})/gi, '!');

              if (matches) {
                proposed_length = countable_string.length - matches.length;
              } else {
                proposed_length = countable_string.length;
              }
              if (proposed_length < 0) { proposed_length = 0; }

              $scope.sms_num_characters = proposed_length;
              return $scope.sms_vars = matches || [];
            };
          }
          ],
          getTemplate() {
            return me.dpTemplateManager.get('OptionBuilder/type-actions-set-sms.html');
          },
          getData() {
            return me.loadDataOptions();
          },
          getDataFormatter() {
            return {
              getViewValue(value, data) {
                if (value == null) { value = {}; }
                options = value.options || {};

                const department_ids = {};
                if (options.department_ids) {
                  for (let did of Array.from(options.department_ids)) {
                    did = parseInt(did);
                    department_ids[did] = true;
                  }
                }

                const agent_ids = {};
                if (options.agent_ids) {
                  for (let aid of Array.from(options.agent_ids)) {
                    if ((aid !== 'followers') && (aid !== 'assigned')) { aid = parseInt(aid); }
                    agent_ids[aid] = true;
                  }
                } else {
                  agent_ids.followers = false;
                  agent_ids.assigned = false;
                }

                const team_ids = {};
                if (options.agent_teams) {
                  for (let tid of Array.from(options.agent_teams)) {
                    if (tid !== 'assigned') { tid = parseInt(tid); }
                    team_ids[tid] = true;
                  }
                } else {
                  team_ids.assigned = false;
                }

                return {
                  agents:      options.agents || [],
                  agent_ids,
                  agent_teams: team_ids,
                  department_ids,
                  to_number:   options.to_number || '',
                  message:     options.message || ''
                };
              },
              getValue(model, data) {
                let k,
                  v;
                if (model == null) { model = {}; }
                options = {
                  agents:         model.agents || [],
                  agent_teams:    [],
                  department_ids: [],
                  to_number:      model.to_number || '',
                  message:        model.message || '',
                  agent_ids:      []
                };

                if (model.department_ids) {
                  for (k of Object.keys(model.department_ids || {})) {
                    v = model.department_ids[k];
                    if (v) {
                      options.department_ids.push(parseInt(k));
                    }
                  }
                }
                if (model.agent_ids) {
                  for (k of Object.keys(model.agent_ids || {})) {
                    v = model.agent_ids[k];
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
                  for (k of Object.keys(model.agent_teams || {})) {
                    v = model.agent_teams[k];
                    if (v) {
                      if (k === 'assigned') {
                        options.agent_teams.push('assigned');
                      } else {
                        options.agent_teams.push(parseInt(k));
                      }
                    }
                  }
                }
                const value = {};
                value.type = opt.action_name;
                value.options = options;

                return value;
              }
            };
          }
        };
      };
    }

    resetData() {
      this.options_data = null;
      return this.loadDataPromise = null;
    }

    loadDataOptions() {
      if (!this.loadDataPromise) {
        const apiV1 = this.Api.sendDataGet({
          agents:            '/agents',
          agent_teams:       '/agent_teams',
          ticket_brands:     '/ticket_brands',
          ticket_deps:       '/ticket_deps',
          ticket_cats:       '/ticket_cats',
          ticket_prods:      '/ticket_prods',
          ticket_pris:       '/ticket_pris',
          ticket_works:      '/ticket_works',
          ticket_fields:     '/ticket_fields',
          ticket_labels:     '/labels/definitions/tickets',
          user_fields:       '/user_fields',
          org_fields:        '/org_fields',
          ticket_slas:       '/ticket_slas',
          email_accounts:    '/email_accounts',
          usergroups:        '/user_groups',
          langs:             '/langs',
          email_tpls:        '/email-templates-info',
          round_robin:       '/round_robin/settings',
          round_robins:      '/round_robin',
          tasks:             '/tasks/settings',
          contextual_fields: '/custom_fields',
          jira_settings:     '/apps/jira'
        });

        this.loadDataPromise = this.$q.defer();

        const promises = [apiV1];

        if (window.DP_HAS_NEW_EMAILS) {
          const apiV2 = this.Api2.sendGet('/email_templates/info');
          promises.push(apiV2);
        }

        this.$q.all(promises).then((result) => {
          let f;
          const { data } = result[0];
          const options_data = {};
          options_data.agents            = data.agents.agents;
          options_data.agent_teams       = data.agent_teams.agent_teams;
          options_data.ticket_brands     = data.ticket_brands.brands;
          options_data.ticket_deps       = data.ticket_deps.departments;
          options_data.ticket_cats       = data.ticket_cats.categories;
          options_data.ticket_pris       = data.ticket_pris.priorities;
          options_data.ticket_works      = data.ticket_works.workflows;
          options_data.ticket_prods      = data.ticket_prods != null ? data.ticket_prods.products : undefined;
          options_data.ticket_fields     = data.ticket_fields != null ? data.ticket_fields.custom_fields : undefined;
          options_data.org_fields        = data.org_fields != null ? data.org_fields.custom_fields : undefined;
          options_data.user_fields       = data.user_fields != null ? data.user_fields.custom_fields : undefined;
          options_data.ticket_slas       = data.ticket_slas != null ? data.ticket_slas.slas : undefined;
          options_data.email_accounts    = data.email_accounts.email_accounts;
          options_data.usergroups        = data.usergroups.groups;
          options_data.langs             = data.langs != null ? data.langs.languages : undefined;
          options_data.custom_email_tpls = data.email_tpls.list.custom.groups.custom.templates;
          options_data.round_robin       = data.round_robin;
          options_data.round_robins      = data.round_robins;
          options_data.tasks             = data.tasks;
          options_data.contextual_fields = data.contextual_fields;
          options_data.jira_settings     = data.jira_settings;
          options_data.ticket_labels     = data.ticket_labels;

          options_data.ticket_dep_options = this.standardOptionsFormatter(options_data.ticket_deps);
          options_data.flags = [
            { title: 'none', value: '' },
            { title: 'Red', value: 'red' },
            { title: 'Blue', value: 'blue' },
            { title: 'Green', value: 'green' },
            { title: 'Yellow', value: 'yellow' },
            { title: 'Orange', value: 'orange' },
            { title: 'Purple', value: 'purple' },
            { title: 'Pink', value: 'pink' }
          ];

          if (window.DP_HAS_NEW_EMAILS) {
            const v2data = result[1].data.data;

            options_data.new_custom_email_tpls = v2data.list.custom.groups.custom.subGroups.primary.templates;
          }

          this.options_data = options_data;

          if (this.options_data != null ? this.options_data.ticket_fields : undefined) {
            for (f of Array.from(this.options_data.ticket_fields)) {
              this.initFieldGetter('SetTicketField', f, true);
            }
          }
          if (this.options_data != null ? this.options_data.contextual_fields : undefined) {
            for (f of Array.from(this.options_data.contextual_fields)) {
              this.initFieldGetter('SetTicketContextualField', f, true);
            }
          }
          if (this.options_data != null ? this.options_data.user_fields : undefined) {
            for (f of Array.from(this.options_data.user_fields)) {
              this.initFieldGetter('SetUserField', f, true);
            }
          }

          return this.loadDataPromise.resolve(options_data);
        });
      }

      return this.loadDataPromise.promise;
    }


    getSetAgent(options) {
      if (options == null) { options = {}; }
      options.propName = 'agent_id';
      options.dataName = 'agents';
      options.extraOptions = [
        { title: 'Unassign', value: 0 },
        { title: 'Current Agent', value: -1 }
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetRoundRobin(options) {
      if (options == null) { options = {}; }
      options.propName = 'id';
      options.dataName = 'round_robins';
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetAgentFollowers(options) {
      if (options == null) { options = {}; }
      options.propName = 'add_agent_ids';
      options.dataName = 'agents';
      options.isMulti = true;
      options.extraOptions = [
        { title: 'Current Agent', value: -1 }
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetAgentTeam(options) {
      if (options == null) { options = {}; }
      options.propName = 'agent_team_id';
      options.dataName = 'agent_teams';
      options.extraOptions = [
        { title: 'No Team', value: 0 },
        { title: 'Current Agent\'s Team', value: -1 },
        { title: 'Team of currently assigned agent', value: -2 }
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetWorkflow(options) {
      if (options == null) { options = {}; }
      options.propName = 'workflow_id';
      options.dataName = 'ticket_works';
      options.extraOptions = [
        { title: 'None', value: 0 }
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetLanguage(options) {
      if (options == null) { options = {}; }
      options.propName = 'language_id';
      options.dataName = 'langs';
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetHold(options) {
      if (options == null) { options = {}; }
      options.propName = 'is_hold';
      options.options = [
        { title: 'Put ticket on hold', value: '1' },
        { title: 'Take ticket off hold', value: '0' }
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetPriority(options) {
      if (options == null) { options = {}; }
      options.propName = 'priority_id';
      options.dataName = 'ticket_pris';
      options.extraOptions = [
        { title: 'None', value: 0 }
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetCategory(options) {
      if (options == null) { options = {}; }
      options.propName = 'category_id';
      options.dataName = 'ticket_cats';
      options.extraOptions = [
        { title: 'None', value: 0 }
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetLabels(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() { return me.dpTemplateManager.get('OptionBuilder/type-actions-set-labels.html'); },
        getData() { return {}; },
        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};
              const tags = [];
              if (me.options_data.ticket_labels) {
                me.options_data.ticket_labels.map(label => tags.push(label.label));
              }

              const viewValue = {
                add_labels:    options.add_labels || [],
                remove_labels: options.remove_labels || [],
                select2_add:   {
                  multiple:    true,
                  simple_tags: true,
                  tags
                },
                select2_remove: {
                  multiple:    true,
                  simple_tags: true,
                  tags
                }
              };

              viewValue.with_add    = viewValue.add_labels.length > 0;
              viewValue.with_remove = viewValue.remove_labels.length > 0;

              return viewValue;
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'SetLabels';
              value.options = {};
              value.options.add_labels    = model.with_add ? model.add_labels : [];
              value.options.remove_labels = model.with_remove ? model.remove_labels : [];
              return value;
            }
          };
        }
      };
    }

    getSetSubject(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() { return me.dpTemplateManager.get('OptionBuilder/type-actions-set-subject.html'); },
        getData() { return {}; },
        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};
              return {
                subject:        options.subject || '',
                with_formatter: options.with_formatter || false
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'SetSubject';
              value.options = {};
              value.options.subject = model.subject || '';
              value.options.with_formatter = !!model.with_formatter;
              return value;
            }
          };
        }
      };
    }

    getSetStatus(options) {
      if (options == null) { options = {}; }
      options.propName = 'status';
      options.template = 'OptionBuilder/type-actions-status.html';
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetBrand(options) {
      if (options == null) { options = {}; }
      options.propName = 'brand_id';
      options.dataName = 'ticket_brands';
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetDepartment(options) {
      if (options == null) { options = {}; }
      options.propName = 'department_id';
      options.dataName = 'ticket_deps';
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetProduct(options) {
      if (options == null) { options = {}; }
      options.propName = 'product_id';
      options.dataName = 'ticket_prods';
      options.extraOptions = [
        { title: 'None', value: 0 }
      ];
      const def = this.getStandardSelect(options);
      return def;
    }

    getSetEmailAccount(options) {
      if (options == null) { options = {}; }
      options.propName = 'email_account_id';
      options.dataName = 'email_accounts';
      options.optionsFormatter = function (options) {
        const opts = [];

        for (const acc of Array.from(options)) {
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

    getSetEmailSubject(options) {
      if (options == null) { options = {}; }
      options.propName = 'subject';
      const def = this.getStandardInput(options);
      return def;
    }

    getSetUrgency(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() { return me.dpTemplateManager.get('OptionBuilder/type-actions-urgency.html'); },
        getData() { return {}; },
        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};

              let mode = options.mode || 'add';
              let only_if_lower = false;
              if (mode === 'raise') {
                mode = 'set';
                only_if_lower = true;
              }

              return {
                value: options.urgency,
                op:    mode,
                only_if_lower
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'SetUrgency';
              value.options = {};
              value.options.urgency = model.value;

              if ((model.op === 'set') && model.only_if_lower) {
                value.options.mode = 'raise';
              } else {
                value.options.mode = model.op;
              }
              return value;
            }
          };
        }
      };
    }

    getSetCcs(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() { return me.dpTemplateManager.get('OptionBuilder/type-actions-set-ccs.html'); },
        getData() { return {}; },
        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};
              return {
                add_emails:       (options.add_emails || []).join(', '),
                remove_emails:    (options.remove_emails || []).join(', '),
                add_org_managers: options.add_org_managers || false
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
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
    }

    getSetFlag(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-set-flag.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              const agent_ids = {};
              if (value.options != null ? value.options.agent_ids : undefined) {
                for (const aid of Array.from((value.options != null ? value.options.agent_ids : undefined))) {
                  agent_ids[`${aid}`] = true;
                }
              }

              return {
                agent_ids,
                value: (value.options != null ? value.options.color : undefined) || ''
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'SetFlag';
              value.options = {};
              value.options.color = model.value || '';
              value.options.agent_ids = [];

              if (model.agent_ids) {
                for (const k of Object.keys(model.agent_ids || {})) {
                  const v = model.agent_ids[k];
                  if (v) {
                    if (Numbers.isNumeric(k)) {
                      value.options.agent_ids.push(parseInt(k));
                    } else {
                      value.options.agent_ids.push(k);
                    }
                  }
                }
              }

              return value;
            }
          };
        }
      };
    }

    getSetUserOwner(options) {
      if (options == null) { options = {}; }
      options.propName = 'email_address';
      options.placeholder = 'Enter an email address';
      const def = this.getStandardInput(options);
      return def;
    }

    getSetDeleted(options) {
      if (options == null) { options = {}; }
      options.icon = 'fa-unlink';
      const def = this.getStandardIs(options);
      return def;
    }

    getModStopTriggers(options) {
      if (options == null) { options = {}; }
      options.propName = 'stop_triggers';
      options.icon = 'fa-unlink';
      const def = this.getStandardIs(options);
      return def;
    }

    getModSetUserVar(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-var.html');
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
                name:  options.name || '',
                value: options.value || ''
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'ModSetUserVar';
              value.options = {
                name:  model.name || '',
                value: model.value || ''
              };
              return value;
            }
          };
        }
      };
    }

    getModMuteUserEmails(options) {
      if (options == null) { options = {}; }
      const def = this.getStandardIs(options);
      return def;
    }

    getModMuteAgentEmails(options) {
      if (options == null) { options = {}; }
      const def = this.getStandardIs(options);
      return def;
    }

    getModForceAgentEmails(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-force-agent-emails.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};

              const agent_ids = {};
              if (options.agent_ids) {
                for (const aid of Array.from(options.agent_ids)) {
                  agent_ids[`${aid}`] = true;
                }
              }

              return {
                agent_ids
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              options = {
                agent_ids: []
              };

              if (model.agent_ids) {
                for (const k of Object.keys(model.agent_ids || {})) {
                  const v = model.agent_ids[k];
                  if (v) {
                    if (Numbers.isNumeric(k)) {
                      options.agent_ids.push(parseInt(k));
                    } else {
                      options.agent_ids.push(k);
                    }
                  }
                }
              }

              const value = {};
              value.type = 'ModForceAgentEmails';
              value.options = options;
              return value;
            }
          };
        }
      };
    }

    getTicketLogText(options) {
      if (options == null) { options = {}; }
      options.propName = 'message';
      options.placeholder = 'Enter text here to add to the ticket log';
      const def = this.getStandardInput(options);
      return def;
    }

    getSendUserNewEmail(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-sendusernewemail.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        scopeInit: ['$scope', '$modal', '$timeout', function ($scope, $modal, $timeout) {
          $scope.handleTemplateChange = function () {
            if ($scope.model.template === 'CREATE') {
              $scope.model.template = null;
              $scope.is_creating = true;
              return $modal.open({
                templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-new-email-editor.html`,
                size:        'lg',
                controller:  'Admin_Templates_Ctrl_NewEmailTemplateEditor',
                resolve:     {
                  templateName() {
                    return null;
                  }
                }
              }).result.then((info) => {
                if (info.templateName) {
                  const title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                  const tpl = {
                    name: info.templateName,
                    title
                  };

                  if (((me.options_data != null ? me.options_data.new_custom_email_tpls : undefined) != null) && (me.options_data.new_custom_email_tpls.indexOf(tpl) === -1)) {
                    me.options_data.new_custom_email_tpls.push(tpl);
                  }

                  $scope.model.template = info.templateName;
                  return $timeout(() => {
                    $scope.model.template = info.templateName;
                    return $scope.is_creating = false;
                  }
                  , 100);
                }
                return $scope.is_creating = false;
              }
              , () => $scope.is_creating = false);
            }
          };

          return $scope.editTemplate = () =>
            $modal.open({
              templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-new-email-editor.html`,
              size:        'lg',
              controller:  'Admin_Templates_Ctrl_NewEmailTemplateEditor',
              resolve:     {
                templateName() {
                  return $scope.model.template;
                }
              }
            })
          ;
        }
        ],

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              let from_name,
                from_name_custom;
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};

              if (options.from_name_custom && !['performer', 'helpdesk_name', 'site_name'].includes(options.from_name_custom)) {
                from_name = 'custom';
                ({ from_name_custom } = options);
              } else {
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (!['performer', 'helpdesk_name', 'site_name'].includes(from_name)) {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
              }

              const view_model = {
                template:     options.template || '',
                do_cc_users:  options.do_cc_users ? 'all' : 'owner',
                from_name,
                from_name_custom,
                from_account: `${parseInt(options.from_account || 0) || 0}`,
                headers:      options.headers || [],
                simple_mode:  false
              };

              if ((view_model.do_cc_users === 'owner') && (view_model.from_name === 'helpdesk_name') && (view_model.from_account === '0') && !view_model.headers.length) {
                view_model.simple_mode = true;
              }

              return view_model;
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              options = {
                template:     model.template || '',
                do_cc_users:  model.do_cc_users && (model.do_cc_users === 'all'),
                from_name:    '',
                from_account: parseInt(model.from_account || 0),
                headers:      model.headers.filter(header => header.name)
              };

              if (model.from_name === 'custom') {
                options.from_name = model.from_name_custom || '';
              } else {
                options.from_name = model.from_name || '';
              }

              const value = {};
              value.type = 'SendUserNewEmail';
              value.options = options;
              return value;
            }
          };
        }
      };
    }

    getSendUserEmail(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-senduseremail.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        scopeInit: ['$scope', '$modal', '$timeout', function ($scope, $modal, $timeout) {
          $scope.handleTemplateChange = function () {
            if ($scope.model.template === 'CREATE') {
              $scope.model.template = null;
              $scope.is_creating = true;
              return $modal.open({
                templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-email-editor.html`,
                controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
                resolve:     {
                  templateName() {
                    return null;
                  }
                }
              }).result.then((info) => {
                if (info.templateName) {
                  const title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                  const tpl = {
                    name: info.templateName,
                    title
                  };

                  if (((me.options_data != null ? me.options_data.custom_email_tpls : undefined) != null) && (me.options_data.custom_email_tpls.indexOf(tpl) === -1)) {
                    me.options_data.custom_email_tpls.push(tpl);
                  }

                  $scope.model.template = info.templateName;
                  return $timeout(() => {
                    $scope.model.template = info.templateName;
                    return $scope.is_creating = false;
                  }
                  , 100);
                }
                return $scope.is_creating = false;
              }
              , () => $scope.is_creating = false);
            }
          };

          return $scope.editTemplate = () =>
            $modal.open({
              templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-email-editor.html`,
              controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
              resolve:     {
                templateName() {
                  return $scope.model.template;
                }
              }
            })
          ;
        }
        ],

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              let from_name,
                from_name_custom;
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};

              if (options.from_name_custom && !['performer', 'helpdesk_name', 'site_name'].includes(options.from_name_custom)) {
                from_name = 'custom';
                ({ from_name_custom } = options);
              } else {
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (!['performer', 'helpdesk_name', 'site_name'].includes(from_name)) {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
              }

              const view_model = {
                template:     options.template || '',
                do_cc_users:  options.do_cc_users ? 'all' : 'owner',
                from_name,
                from_name_custom,
                from_account: `${parseInt(options.from_account || 0) || 0}`,
                headers:      options.headers || [],
                simple_mode:  false
              };

              if ((view_model.do_cc_users === 'owner') && (view_model.from_name === 'helpdesk_name') && (view_model.from_account === '0') && !view_model.headers.length) {
                view_model.simple_mode = true;
              }

              return view_model;
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              options = {
                template:     model.template || '',
                do_cc_users:  model.do_cc_users && (model.do_cc_users === 'all'),
                from_name:    '',
                from_account: parseInt(model.from_account || 0),
                headers:      model.headers.filter(header => header.name)
              };

              if (model.from_name === 'custom') {
                options.from_name = model.from_name_custom || '';
              } else {
                options.from_name = model.from_name || '';
              }

              const value = {};
              value.type = 'SendUserEmail';
              value.options = options;
              return value;
            }
          };
        }
      };
    }

    getSendAgentNewEmail(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-sendagentnewemail.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        scopeInit: ['$scope', '$modal', '$timeout', function ($scope, $modal, $timeout) {
          $scope.$watch(
            () => $scope.model.agent_ids.all_agents,
            (newVal, oldVal) => {
              if (!newVal) { return; }
              return (() => {
                const result = [];
                for (const k of Object.keys($scope.model.agent_ids || {})) {
                  const v = $scope.model.agent_ids[k];
                  if (k === 'all_agents') { continue; }
                  result.push($scope.model.agent_ids[k] = false);
                }
                return result;
              })();
            });

          $scope.handleTemplateChange = function () {
            if ($scope.model.template === 'CREATE') {
              $scope.model.template = null;
              $scope.is_creating = true;
              return $modal.open({
                templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-new-email-editor.html`,
                size:        'lg',
                controller:  'Admin_Templates_Ctrl_NewEmailTemplateEditor',
                resolve:     {
                  templateName() {
                    return null;
                  }
                }
              }).result.then((info) => {
                if (info.templateName) {
                  const title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                  const tpl = {
                    name: info.templateName,
                    title
                  };

                  if (((me.options_data != null ? me.options_data.new_custom_email_tpls : undefined) != null) && (me.options_data.new_custom_email_tpls.indexOf(tpl) === -1)) {
                    me.options_data.new_custom_email_tpls.push(tpl);
                  }

                  $scope.model.template = info.templateName;
                  return $timeout(() => {
                    $scope.model.template = info.templateName;
                    return $scope.is_creating = false;
                  }
                  , 100);
                }
                return $scope.is_creating = false;
              }
              , () => $scope.is_creating = false);
            }
          };

          return $scope.editTemplate = () =>
            $modal.open({
              templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-new-email-editor.html`,
              size:        'lg',
              controller:  'Admin_Templates_Ctrl_NewEmailTemplateEditor',
              resolve:     {
                templateName() {
                  return $scope.model.template;
                }
              }
            })
          ;
        }
        ],

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              let from_name,
                from_name_custom;
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};

              if (options.from_name_custom && !['performer', 'helpdesk_name', 'site_name'].includes(options.from_name_custom)) {
                from_name = 'custom';
                ({ from_name_custom } = options);
              } else {
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (!['performer', 'helpdesk_name', 'site_name'].includes(from_name)) {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
              }

              const agent_ids = {};
              if (options.agent_ids) {
                for (const aid of Array.from(options.agent_ids)) {
                  agent_ids[`${aid}`] = true;
                }
              }

              return {
                template:     options.template || '',
                agent_ids,
                from_name,
                from_name_custom,
                from_account: `${parseInt(options.from_account || 0) || 0}`,
                headers:      options.headers || []
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              options = {
                template:     model.template || '',
                agent_ids:    [],
                from_name:    '',
                from_account: parseInt(model.from_account || 0),
                headers:      model.headers.filter(header => header.name)
              };

              if (model.from_name === 'custom') {
                options.from_name = model.from_name_custom || '';
              } else {
                options.from_name = model.from_name || '';
              }

              if (model.agent_ids) {
                for (const k of Object.keys(model.agent_ids || {})) {
                  const v = model.agent_ids[k];
                  if (v) {
                    if (Numbers.isNumeric(k)) {
                      options.agent_ids.push(parseInt(k));
                    } else {
                      options.agent_ids.push(k);
                    }
                  }
                }
              }

              const value = {};
              value.type = 'SendAgentNewEmail';
              value.options = options;
              return value;
            }
          };
        }
      };
    }

    getSendAgentEmail(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-sendagentemail.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        scopeInit: ['$scope', '$modal', '$timeout', function ($scope, $modal, $timeout) {
          $scope.$watch(
            () => $scope.model.agent_ids.all_agents,
            (newVal, oldVal) => {
              if (!newVal) { return; }
              return (() => {
                const result = [];
                for (const k of Object.keys($scope.model.agent_ids || {})) {
                  const v = $scope.model.agent_ids[k];
                  if (k === 'all_agents') { continue; }
                  result.push($scope.model.agent_ids[k] = false);
                }
                return result;
              })();
            });

          $scope.handleTemplateChange = function () {
            if ($scope.model.template === 'CREATE') {
              $scope.model.template = null;
              $scope.is_creating = true;
              return $modal.open({
                templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-email-editor.html`,
                controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
                resolve:     {
                  templateName() {
                    return null;
                  }
                }
              }).result.then((info) => {
                if (info.templateName) {
                  const title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                  const tpl = {
                    name: info.templateName,
                    title
                  };

                  if (((me.options_data != null ? me.options_data.custom_email_tpls : undefined) != null) && (me.options_data.custom_email_tpls.indexOf(tpl) === -1)) {
                    me.options_data.custom_email_tpls.push(tpl);
                  }

                  $scope.model.template = info.templateName;
                  return $timeout(() => {
                    $scope.model.template = info.templateName;
                    return $scope.is_creating = false;
                  }
                  , 100);
                }
                return $scope.is_creating = false;
              }
              , () => $scope.is_creating = false);
            }
          };

          return $scope.editTemplate = () =>
            $modal.open({
              templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-email-editor.html`,
              controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
              resolve:     {
                templateName() {
                  return $scope.model.template;
                }
              }
            })
          ;
        }
        ],

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              let from_name,
                from_name_custom;
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};

              if (options.from_name_custom && !['performer', 'helpdesk_name', 'site_name'].includes(options.from_name_custom)) {
                from_name = 'custom';
                ({ from_name_custom } = options);
              } else {
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (!['performer', 'helpdesk_name', 'site_name'].includes(from_name)) {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
              }

              const agent_ids = {};
              if (options.agent_ids) {
                for (const aid of Array.from(options.agent_ids)) {
                  agent_ids[`${aid}`] = true;
                }
              }

              return {
                template:     options.template || '',
                agent_ids,
                from_name,
                from_name_custom,
                from_account: `${parseInt(options.from_account || 0) || 0}`,
                headers:      options.headers || []
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              options = {
                template:     model.template || '',
                agent_ids:    [],
                from_name:    '',
                from_account: parseInt(model.from_account || 0),
                headers:      model.headers.filter(header => header.name)
              };

              if (model.from_name === 'custom') {
                options.from_name = model.from_name_custom || '';
              } else {
                options.from_name = model.from_name || '';
              }

              if (model.agent_ids) {
                for (const k of Object.keys(model.agent_ids || {})) {
                  const v = model.agent_ids[k];
                  if (v) {
                    if (Numbers.isNumeric(k)) {
                      options.agent_ids.push(parseInt(k));
                    } else {
                      options.agent_ids.push(k);
                    }
                  }
                }
              }

              const value = {};
              value.type = 'SendAgentEmail';
              value.options = options;
              return value;
            }
          };
        }
      };
    }

    getSendSpecificUserNewEmail(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-sendnewemail.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        scopeInit: ['$scope', '$modal', '$timeout', function ($scope, $modal, $timeout) {
          $scope.handleTemplateChange = function () {
            if ($scope.model.template === 'CREATE') {
              $scope.model.template = null;
              $scope.is_creating = true;
              return $modal.open({
                templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-new-email-editor.html`,
                size:        'lg',
                controller:  'Admin_Templates_Ctrl_NewEmailTemplateEditor',
                resolve:     {
                  templateName() {
                    return null;
                  }
                }
              }).result.then((info) => {
                if (info.templateName) {
                  const title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                  const tpl = {
                    name: info.templateName,
                    title
                  };

                  if (((me.options_data != null ? me.options_data.new_custom_email_tpls : undefined) != null) && (me.options_data.new_custom_email_tpls.indexOf(tpl) === -1)) {
                    me.options_data.new_custom_email_tpls.push(tpl);
                  }

                  $scope.model.template = info.templateName;
                  return $timeout(() => {
                    $scope.model.template = info.templateName;
                    return $scope.is_creating = false;
                  }
                  , 100);
                }
                return $scope.is_creating = false;
              }
              , () => $scope.is_creating = false);
            }
          };

          return $scope.editTemplate = () =>
            $modal.open({
              templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-new-email-editor.html`,
              size:        'lg',
              controller:  'Admin_Templates_Ctrl_NewEmailTemplateEditor',
              resolve:     {
                templateName() {
                  return $scope.model.template;
                }
              }
            })
          ;
        }
        ],

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              let from_name,
                from_name_custom;
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};

              const emails = options.emails || [];
              if (options.from_name_custom && !['performer', 'helpdesk_name', 'site_name'].includes(options.from_name_custom)) {
                from_name = 'custom';
                ({ from_name_custom } = options);
              } else {
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (!['performer', 'helpdesk_name', 'site_name'].includes(from_name)) {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
              }

              return {
                emails,
                template:     options.template || '',
                from_name,
                from_name_custom,
                from_account: `${parseInt(options.from_account || 0) || 0}`,
                headers:      options.headers || []
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              options = {
                emails:       model.emails,
                template:     model.template || '',
                from_name:    '',
                from_account: parseInt(model.from_account || 0),
                headers:      model.headers.filter(header => header.name)
              };

              if (model.from_name === 'custom') {
                options.from_name = model.from_name_custom || '';
              } else {
                options.from_name = model.from_name || '';
              }

              const value = {};
              value.type = 'SendSpecificUserNewEmail';
              value.options = options;
              return value;
            }
          };
        }
      };
    }

    getSendSpecificUserEmail(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-sendemail.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        scopeInit: ['$scope', '$modal', '$timeout', function ($scope, $modal, $timeout) {
          $scope.handleTemplateChange = function () {
            if ($scope.model.template === 'CREATE') {
              $scope.model.template = null;
              $scope.is_creating = true;
              return $modal.open({
                templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-email-editor.html`,
                controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
                resolve:     {
                  templateName() {
                    return null;
                  }
                }
              }).result.then((info) => {
                if (info.templateName) {
                  const title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
                  const tpl = {
                    name: info.templateName,
                    title
                  };

                  if (((me.options_data != null ? me.options_data.custom_email_tpls : undefined) != null) && (me.options_data.custom_email_tpls.indexOf(tpl) === -1)) {
                    me.options_data.custom_email_tpls.push(tpl);
                  }

                  $scope.model.template = info.templateName;
                  return $timeout(() => {
                    $scope.model.template = info.templateName;
                    return $scope.is_creating = false;
                  }
                  , 100);
                }
                return $scope.is_creating = false;
              }
              , () => $scope.is_creating = false);
            }
          };

          return $scope.editTemplate = () =>
            $modal.open({
              templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-email-editor.html`,
              controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
              resolve:     {
                templateName() {
                  return $scope.model.template;
                }
              }
            })
          ;
        }
        ],

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              let from_name,
                from_name_custom;
              if (value == null) { value = {}; }
              options = (value != null ? value.options : undefined) || {};

              const emails = options.emails || [];
              if (options.from_name_custom && !['performer', 'helpdesk_name', 'site_name'].includes(options.from_name_custom)) {
                from_name = 'custom';
                ({ from_name_custom } = options);
              } else {
                from_name = options.from_name || 'helpdesk_name';
                from_name_custom = null;
                if (!['performer', 'helpdesk_name', 'site_name'].includes(from_name)) {
                  from_name = 'custom';
                  from_name_custom = options.from_name;
                }
              }

              return {
                emails,
                template:     options.template || '',
                from_name,
                from_name_custom,
                from_account: `${parseInt(options.from_account || 0) || 0}`,
                headers:      options.headers || []
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              options = {
                emails:       model.emails,
                template:     model.template || '',
                from_name:    '',
                from_account: parseInt(model.from_account || 0),
                headers:      model.headers.filter(header => header.name)
              };

              if (model.from_name === 'custom') {
                options.from_name = model.from_name_custom || '';
              } else {
                options.from_name = model.from_name || '';
              }

              const value = {};
              value.type = 'SendSpecificUserEmail';
              value.options = options;
              return value;
            }
          };
        }
      };
    }

    getWebHook(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-webhook.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return value.options || {};
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'WebHook';
              value.options = model;
              return value;
            }
          };
        }
      };
    }

    getWebHook2(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-webhook2.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return value.options || {};
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'WebHook2';
              value.options = model;
              return value;
            }
          };
        }
      };
    }

    getSetSlas(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-slas.html');
        },

        getData() {
          const defer = me.$q.defer();
          me.loadDataOptions().then(() => {
            options = [];
            for (const sla of Array.from(me.options_data.ticket_slas)) {
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
                add_slas:    options.add_sla_ids || [],
                remove_slas: options.remove_sla_ids || []
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'SetSlas';
              value.options = {};
              value.options.add_sla_ids    = model.add_slas;
              value.options.remove_sla_ids = model.remove_slas;
              return value;
            }
          };
        }
      };
    }

    getAddAgentReply(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-addagentreply.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              const opt = value.options || {};

              let by_agent_id = opt.by_agent_id || null;
              if (!by_agent_id) {
                by_agent_id = data.agents[0].id;
              }

              by_agent_id += '';

              return {
                type:              'AddAgentReply',
                text:              opt.reply_text || '',
                by_assigned_agent: opt.by_assigned_agent || false,
                by_agent_id
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
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
    }

    getAddAgentNote(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-addagentreply.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              const opt = value.options || {};

              let by_agent_id = opt.by_agent_id || null;
              if (!by_agent_id) {
                by_agent_id = data.agents[0].id;
              }

              by_agent_id += '';

              return {
                type:              'AddAgentNote',
                text:              opt.note_text || '',
                by_assigned_agent: opt.by_assigned_agent || false,
                by_agent_id
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
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
    }

    getSetSlasComplete(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-setslascomplete.html');
        },

        getData() {
          const defer = me.$q.defer();
          me.loadDataOptions().then(() => {
            options = [];
            for (const sla of Array.from(me.options_data.ticket_slas)) {
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
                sla_ids:    options.sla_ids || [],
                sla_status: options.sla_status || 'ok'
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'SetSlasComplete';
              value.options = {
                sla_ids:    model.sla_ids,
                sla_status: model.sla_status || 'ok'
              };
              return value;
            }
          };
        }
      };
    }

    getCreateTask(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-create-task.html');
        },

        getData() {
          return me.loadDataOptions();
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = value.options || {};
              const agents = [{ id: -1, display_name: 'Current Agent' }];
              data.tasks.agents.map((agent) => { if (__guard__(agent.perms != null ? agent.perms.tasks : undefined, x => x.use)) { return agents.push(agent); } });
              let _public = options.public;
              if ((_public == null)) { _public = true; }
              const timezones = [];
              for (let i = -12, asc = -12 <= 12; asc ? i <= 12 : i >= 12; asc ? i++ : i--) {
                timezones.push({ id: i, title: `UTC ${i >= 0 ? '+' : ''}${i}:00` });
              }

              return {
                agents,
                teams:    data.agent_teams,
                title:    options.title,
                date_due: options.date_due,
                public:   _public,
                creator:  options.creator,
                assignee: options.assignee,
                timezones,
                offset:   options.offset,
                link:     options.link
              };
            },

            getValue(model, data) {
              let date;
              if (model == null) { model = {}; }
              if (model.date_due) { date = moment(model.date_due).format('YYYY-MM-DD HH:mm'); }
              return {
                type:    'CreateTask',
                options: {
                  title:    model.title,
                  date_due: (date != null) ? date : undefined,
                  public:   !!model.public,
                  creator:  model.creator,
                  assignee: model.assignee,
                  offset:   model.offset,
                  link:     !!model.link
                }
              };
            }
          };
        }
      };
    }

    getAddJIRAComment(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() { return me.dpTemplateManager.get('OptionBuilder/type-actions-addagentreply.html'); },
        getData() { return me.loadDataOptions(); },
        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              const opt = value.options || {};
              const by_agent_id = `${opt.by_agent_id || data.agents[0].id}`;

              return {
                type:              'AddJIRAComment',
                text:              opt.note_text || '',
                by_assigned_agent: opt.by_assigned_agent || false,
                by_agent_id
              };
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              return {
                type:    'AddJIRAComment',
                options: {
                  note_text:         model.text,
                  by_assigned_agent: model.by_assigned_agent || false,
                  by_agent_id:       parseInt(model.by_agent_id || 0) || 0
                }
              };
            }
          };
        }
      };
    }

    getDeleteAttachments(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-delete-attachments.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = value.options || {};

              if (options.must_match != null ? options.must_match.length : undefined) {
                options.must_match_value = options.must_match;
                options.must_match = true;
              } else {
                options.must_match = false;
              }

              if (options.must_not_match != null ? options.must_not_match.length : undefined) {
                options.must_not_match_value = options.must_not_match;
                options.must_not_match = true;
              } else {
                options.must_not_match = false;
              }

              if (parseInt(options.at_least)) {
                options.at_least_value = parseInt(options.at_least);
                options.at_least = true;
              } else {
                options.at_least = false;
              }

              return options;
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'DeleteAttachments';
              value.options = {
                must_match:     model.must_match ? model.must_match_value : '',
                must_not_match: model.must_not_match ? model.must_not_match_value : '',
                at_least:       model.at_least ? parseInt(model.at_least_value) || 0 : '',
                skip_inline:    model.skip_inline
              };
              return value;
            }
          };
        }
      };
    }

    getAddBrandToPerson(options) {
      if (options == null) { options = {}; }
      options.propName = 'add_brand_to_person';
      const def = this.getStandardIs(options);
      return def;
    }


    getDeleteVoicePhoneCallRecords(options) {
      if (options == null) { options = {}; }
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get('OptionBuilder/type-actions-delete-voice-phone-call-records.html');
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              options = value.options || {};

              if (parseInt(options.at_least)) {
                options.at_least = parseInt(options.at_least);
              } else {
                options.at_least = 0;
              }

              if (parseInt(options.shorter_than)) {
                options.shorter_than = parseInt(options.shorter_than);
              } else {
                options.shorter_than = 0;
              }

              if (parseInt(options.longer_than)) {
                options.longer_than = parseInt(options.longer_than);
              } else {
                options.longer_than = 0;
              }

              return options;
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = 'DeleteVoicePhoneCallRecords';
              value.options = {
                at_least:     model.at_least ? parseInt(model.at_least) || 0 : 0,
                longer_than:  model.longer_than ? parseInt(model.longer_than) || 0 : 0,
                shorter_than: model.shorter_than ? parseInt(model.shorter_than) || 0 : 0
              };
              return value;
            }
          };
        }
      };
    }
  }
  return Admin_OptionBuilder_TypesDef_TicketFilter;
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
