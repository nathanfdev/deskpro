define [
  'Admin/OptionBuilder/TypesDef/BaseCriteriaTypesDef',
], (
  BaseCriteriaTypesDef
) ->
  class Admin_OptionBuilder_TypesDef_TicketCriteria extends BaseCriteriaTypesDef
    init: ->
      @options_data = null

    setWithChangedOps: (@with_changed_ops) ->
      return

    getOperators: (options) ->

      if not options.operators
        return ['is', 'not']

      if @with_changed_ops
        return options.operators

      ops = []
      for o in options.operators
        if o != 'changed' && o != 'changed_to' && o != 'changed_from'
          ops.push(o)

      return ops


    getOptionsForTypes: (types, typesData = null, mode) ->
      set_options = []

      #------------------------------
      # Email Criteria
      #------------------------------

      if types.indexOf('email') != -1
        options = []

        options.push({
          title: 'Email Account',
          value: 'CheckEmailAccount'
        })

        options.push({
          title: 'Email Subject',
          value: 'CheckEmailSubject'
        })

        options.push({
          title: 'Email Body',
          value: 'CheckEmailBody'
        })

        options.push({
          title: 'To Name',
          value: 'CheckEmailToName'
        })

        options.push({
          title: 'To Address',
          value: 'CheckEmailToAddress'
        })

        options.push({
          title: 'From Name',
          value: 'CheckEmailFromName'
        })

        options.push({
          title: 'From Address',
          value: 'CheckEmailFromAddress'
        })

        options.push({
          title: 'CCd Name',
          value: 'CheckEmailCcName'
        })

        options.push({
          title: 'CCd Address',
          value: 'CheckEmailCcAddress'
        })

        options.push({
          title: 'CC(s) were added',
          value: 'CheckEmailCcAdded'
        })

        options.push({
          title: 'Email Header',
          value: 'CheckEmailHeader'
        })

        options.push({
          title: 'Email bounced',
          value: 'CheckEmailIsBounce'
        })

        options.push({
          title: 'Automated email',
          value: 'CheckEmailIsRobot'
        })

        set_options.push({
          title: 'Email Criteria',
          subOptions: options
        })

      #------------------------------
      # Ticket Criteria
      #------------------------------

      options = []

      options.push({
        title: 'Brand',
        value: 'CheckBrand'
      })

      options.push({
        title: 'Department',
        value: 'CheckDepartment'
      })

      options.push({
        title: 'Status',
        value: 'CheckStatus'
      })

      options.push({
        title: 'Agent',
        value: 'CheckAgent'
      })

      options.push({
        title: 'Agent Team',
        value: 'CheckAgentTeam'
      })

      options.push({
        title: 'Product',
        value: 'CheckProduct'
      })

      options.push({
        title: 'Category',
        value: 'CheckCategory'
      })

      options.push({
        title: 'Priority',
        value: 'CheckPriority'
      })

      options.push({
        title: 'Urgency',
        value: 'CheckUrgency'
      })

      options.push({
        title: 'Workflow',
        value: 'CheckWorkflow'
      })

      options.push({
        title: 'Subject',
        value: 'CheckSubject'
      })

      options.push({
        title: 'Labels',
        value: 'CheckLabel'
      })

      options.push({
        title: 'SLAs',
        value: 'CheckSlaStatus'
      })

      options.push({
        title: 'Creation System',
        value: 'CheckCreationSystem'
      })

      options.push({
        title: 'Created via URL',
        value: 'CheckCreationSystemOption'
      })

      options.push({
        title: 'Agent Message',
        value: 'CheckAgentMessage'
      })

      options.push({
        title: 'Agent Note',
        value: 'CheckAgentNote'
      })

      options.push({
        title: 'User Message',
        value: 'CheckUserMessage'
      })
      options.push({
        title: 'New Ticket Charge',
        value: 'CheckTicketCharge'
      })

      options.push({
        title: 'Check Expression [Expert]',
        value: 'CheckExpression'
      })

      if @options_data?.ticket_settings?.satisfaction_enabled
        options.push({
          title: 'Ticket Satisfaction',
          value: 'CheckTicketSatisfaction'
        })

      set_options.push({
        title: 'Ticket Criteria',
        subOptions: options
      })

      #------------------------------
      # Ticket Fields
      #------------------------------

      options = []

      if @options_data?.ticket_fields
        for f in @options_data.ticket_fields
          options.push({
            title: f.title,
            value: @initFieldGetter('CheckTicketField', f)
          })

      if @options_data?.contextual_fields
        for f in @options_data.contextual_fields
          options.push({
            title: f.title,
            value: @initFieldGetter('CheckTicketContextualField', f)
          })

      if options.length
        set_options.push({
          title: 'Ticket Fields',
          subOptions: options
        })

      #------------------------------
      # JIRA
      #------------------------------

      if @options_data?.jira_settings?.enabled
        options = []

        options.push({
          title: 'New JIRA Comment'
          value: 'CheckJIRANewComment'
        }) if 'TriggersUpdate' == mode

        options.push({
          title: 'Issue Status'
          value: 'CheckJIRAIssueStatus'
        })

        options.push({
          title: 'New Linked Issue'
          value: 'CheckJIRANewLinkedIssue'
        }) if 'TriggersUpdate' == mode

        set_options.push({
          title: 'JIRA',
          subOptions: options
        })

      #------------------------------
      # Person
      #------------------------------

      options = []

      options.push({
        title: 'User',
        value: 'CheckUserId'
      })

      options.push({
        title: 'User Name',
        value: 'CheckUserName'
      })

      options.push({
        title: 'User Email Address',
        value: 'CheckUserEmail'
      })

      options.push({
        title: 'User Label',
        value: 'CheckUserLabel'
      })

      options.push({
        title: 'Usergroup',
        value: 'CheckUserUsergroups'
      })

      options.push({
        title: 'User Language',
        value: 'CheckUserLanguage'
      })

      options.push({
        title: 'User is manager of organization',
        value: 'CheckUserOrgManager'
      })

      options.push({
        title: 'User is new',
        value: 'CheckUserIsNew'
      })

      options.push({
        title: 'Check agent validation status',
        value: 'CheckUserValidAgent'
      })

      options.push({
        title: 'Check email validation status',
        value: 'CheckUserValidEmail'
      })

      options.push({
        title: 'User is disabled',
        value: 'CheckUserIsDisabled'
      })

      set_options.push({
        title: 'User Criteria',
        subOptions: options
      })

      #------------------------------
      # User Fields
      #------------------------------

      if @options_data?.user_fields
        options = []

        for f in @options_data.user_fields
          options.push({
            title: f.title,
            value: @initFieldGetter('CheckUserField', f)
          })

        if options.length
          set_options.push({
            title: 'Person Fields',
            subOptions: options
          })

      #------------------------------
      # Org
      #------------------------------

      options = []

      options.push({
        title: 'Organization',
        value: 'CheckOrgId'
      })

      options.push({
        title: 'Organization Name',
        value: 'CheckOrgName'
      })

      options.push({
        title: 'Organization Label',
        value: 'CheckOrgLabel'
      })

      options.push({
        title: 'Organization Email Domain',
        value: 'CheckOrgEmailDomain'
      })

      options.push({
        title: 'Organization Usergroup',
        value: 'CheckOrgUsergroups'
      })

      set_options.push({
        title: 'Organization Criteria',
        subOptions: options
      })

      #------------------------------
      # Org Fields
      #------------------------------

      if @options_data?.org_fields
        options = []

        for f in @options_data.org_fields
          options.push({
            title: f.title,
            value: @initFieldGetter('CheckOrgField', f)
          })

        if options.length
          set_options.push({
            title: 'Organization Fields',
            subOptions: options
          })

      #------------------------------
      # Dates
      #------------------------------

      options = []

      options.push({
        title: 'Current day of week',
        value: 'CheckDayOfWeek'
      })

      options.push({
        title: 'Current time of day',
        value: 'CheckTimeOfDay'
      })

      options.push({
        title: 'Ticket Created Date',
        value: 'CheckDateCreated'
      })

      options.push({
        title: 'During Working Hours',
        value: 'CheckWorkingHours'
      })

      #options.push({
      # title: 'Within working hours',
      # value: 'CheckWorkingHours'
      #})

      set_options.push({
        title: 'Dates',
        subOptions: options
      })

      #------------------------------
      # Trigger Control
      #------------------------------

      options = []

      options.push({
        title: 'Check if user was emailed',
        value: 'CheckUserIsEmailed'
      })

      options.push({
        title: 'Check if agents were emailed',
        value: 'CheckAgentIsEmailed'
      })

      options.push({
        title: 'Check Trigger Variable',
        value: 'CheckUserVar'
      })

      options.push({
        title: 'Check Webhook Variable',
        value: 'CheckWebhookVar'
      })

      options.push({
        title: 'Check Current Agent',
        value: 'CheckPerformer'
      })

      options.push({
        title: 'Check Performer Email',
        value: 'CheckPerformerEmail'
      })

      set_options.push({
        title: 'Trigger Control',
        subOptions: options
      })

      #------------------------------
      # API Criteria
      #------------------------------

      options = []

      options.push({
        title: 'Check API key',
        value: 'CheckApiKey'
      })

      set_options.push({
        title: 'API Criteria',
        subOptions: options
      })

      return set_options

    resetData: ->
      @options_data = null
      @loadDataPromise = null

    loadDataOptions: ->
      if !@loadDataPromise
        @loadDataPromise = @Api.sendDataGet({
          agents:             '/agents'
          agent_teams:        '/agent_teams'
          ticket_brands:      '/ticket_brands'
          ticket_deps:        '/ticket_deps'
          ticket_cats:        '/ticket_cats'
          ticket_prods:       '/ticket_prods'
          ticket_pris:        '/ticket_pris'
          ticket_works:       '/ticket_works'
          ticket_fields:      '/ticket_fields'
          ticket_labels:      '/labels/definitions/tickets'
          user_fields:        '/user_fields'
          org_fields:         '/org_fields'
          ticket_slas:        '/ticket_slas'
          ticket_accounts:    '/email_accounts'
          usergroups:         '/user_groups'
          langs:              '/langs'
          email_tpls:         '/email-templates-info'
          api_keys:           '/api_keys'
          ticket_settings:    '/ticket_settings'
          contextual_fields:  '/custom_fields'
          jira_settings    :  '/apps/jira'
        }).then( (result) =>
          data = result.data
          options_data = {}
          options_data['agents']            = data.agents.agents
          options_data['agent_teams']       = data.agent_teams.agent_teams
          options_data['ticket_brands']     = data.ticket_brands.brands
          options_data['ticket_deps']       = data.ticket_deps.departments
          options_data['ticket_cats']       = data.ticket_cats.categories
          options_data['ticket_pris']       = data.ticket_pris.priorities
          options_data['ticket_works']      = data.ticket_works.workflows
          options_data['ticket_prods']      = data.ticket_prods?.products
          options_data['ticket_fields']     = data.ticket_fields?.custom_fields
          options_data['org_fields']        = data.org_fields?.custom_fields
          options_data['user_fields']       = data.user_fields?.custom_fields
          options_data['ticket_slas']       = data.ticket_slas?.slas
          options_data['email_accounts']    = data.ticket_accounts.email_accounts
          options_data['usergroups']        = data.usergroups.groups
          options_data['langs']             = data.langs?.languages
          options_data['custom_email_tpls'] = data.email_tpls.list['custom'].groups['custom'].templates
          options_data['api_keys']          = data.api_keys.api_keys
          options_data['ticket_settings']   = data.ticket_settings?.ticket_settings
          options_data['contextual_fields'] = data.contextual_fields
          options_data['jira_settings']     = data.jira_settings
          options_data['ticket_labels']     = data.ticket_labels
          @options_data = options_data

          if @options_data?.ticket_fields
            for f in @options_data.ticket_fields
              @initFieldGetter 'CheckTicketField', f, true
          if @options_data?.contextual_fields
            for f in @options_data.contextual_fields
              @initFieldGetter 'CheckTicketContextualField', f, true
          if @options_data?.user_fields
            for f in @options_data.user_fields
              @initFieldGetter 'CheckUserField', f, true
          if @options_data?.org_fields
            for f in @options_data.org_fields
              @initFieldGetter 'CheckOrgField', f, true
        )

      @loadDataPromise

    getCheckWorkflow: (options = {}) ->
      options.propName = 'workflow_ids'
      options.dataName = 'ticket_works'
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
      options.extraOptions = [
        {title: 'None', value: 0}
      ]
      def = @getStandardSelect(options)
      return def

    getCheckPriority: (options = {}) ->
      options.propName = 'priority_ids'
      options.dataName = 'ticket_pris'
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
      options.extraOptions = [
        {title: 'None', value: 0}
      ]
      def = @getStandardSelect(options)
      return def

    getCheckUrgency: (options = {}) ->
      options.propName = 'urgency1'
      options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte']
      def = @getStandardInput(options)
      return def

    getCheckCategory: (options = {}) ->
      options.propName = 'category_ids'
      options.dataName = 'ticket_cats'
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
      options.extraOptions = [
        {title: 'None', value: 0}
      ]
      def = @getStandardSelect(options)
      return def

    getCheckBrand: (options = {}) ->
      options.propName = 'brand_ids'
      options.dataName = 'ticket_brands'
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
      def = @getStandardSelect(options)
      return def

    getCheckDepartment: (options = {}) ->
      options.propName = 'department_ids'
      options.dataName = 'ticket_deps'
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
      def = @getStandardSelect(options)
      return def

    getCheckAgent: (options = {}) ->
      options.propName = 'agent_ids'
      options.dataName = 'agents'
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
      options.extraOptions = [
        {title: 'Unassigned', value: 0},
        {title: 'Current Agent', value: -1}
      ]
      def = @getStandardSelect(options)
      return def

    getCheckPerformer: (options = {}) ->
      options.propName = 'person_ids'
      options.dataName = 'agents'
      options.operators = ['contains', 'notcontains']
      options.template = 'OptionBuilder/type-criteria-performer.html'
      options.extraOptions = [
        {title: 'Assigned Agent', value: -1}
        {title: 'Member of assigned team', value: -2}
        {title: 'Follower of the ticket', value: -3}
      ]
      def = @getStandardSelect(options)
      return def

    getCheckPerformerEmail: (options = {}) ->
      options.propName = 'email'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckAgentTeam: (options = {}) ->
      options.propName = 'team_ids'
      options.dataName = 'agent_teams'
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
      options.extraOptions = [
        {title: 'No Team', value: 0},
        {title: 'Current Agent\'s Team', value: -1}
      ]
      def = @getStandardSelect(options)
      return def

    getCheckProduct: (options = {}) ->
      options.propName = 'product_ids'
      options.dataName = 'ticket_prods'
      options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
      options.extraOptions = [
        {title: 'None', value: 0}
      ]
      def = @getStandardSelect(options)
      return def

    getCheckEmailAccount: (options = {}) ->
      options.propName = 'email_account_ids'
      options.dataName = 'email_accounts'
      options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from']
      options.optionsFormatter = (options) ->
        opts = []

        for acc in options
          opts.push({
            value: acc.id,
            title: acc.use_email_address || acc.address
          })

        return opts

      def = @getStandardSelect(options)
      return def

    getCheckEmailSubject: (options = {}) ->
      options.propName = 'subject'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckEmailBody: (options = {}) ->
      options.propName = 'body'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckEmailToName: (options = {}) ->
      options.propName = 'name'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckEmailToAddress: (options = {}) ->
      options.propName = 'email'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckEmailFromName: (options = {}) ->
      options.propName = 'name'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckEmailFromAddress: (options = {}) ->
      options.propName = 'email'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckEmailCcAddress: (options = {}) ->
      options.propName = 'email'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckEmailCcAdded: (options = {}) ->
      options.propName = 'ccs_added'
      def = @getStandardIs(options)
      return def

    getCheckEmailCcName: (options = {}) ->
      options.propName = 'name'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckEmailHeader: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-emailheader.html')

        getData: ->
          return {

          }

        getDataFormatter: ->
          return {
          getViewValue: (value = {}, data) ->
            return {
              op: value.op || 'is',
              name: value.options?.name || '',
              value: value.options?.value || '',
            }

          getValue: (model = {}, data) ->
            value = {
              type: 'CheckEmailHeader',
              op: model.op || 'is',
              options: {
                name: model.name || ''
                value: model.value || ''
              }
            }
            return value
          }
        }

    getCheckEmailIsBounce: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html')

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              return {
              op: value.op || 'is',
              options: [
                { value: 'is', title: 'Email message IS a bounced message' },
                { value: 'not', title: 'Email message IS NOT a bounced message' }
              ]
              }

            getValue: (model = {}, data) ->
              return {
              type: 'CheckEmailIsBounce',
              op: model.op || 'is'
              options: { run:true }
              }
          }
      }

    getCheckEmailIsRobot: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html')

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              return {
              op: value.op || 'is',
              options: [
                { value: 'is', title: 'Email message IS an automated message' },
                { value: 'not', title: 'Email message IS NOT an automated message' }
              ]
              }

            getValue: (model = {}, data) ->
              return {
                type: 'CheckEmailIsRobot',
                op: model.op || 'is'
                options: { run:true }
              }
          }
      }

    getCheckLabel: (options = {}) ->
      options.propName = 'labels'
      options.type_title = 'Labels'
      options.tags = true
      options.options = []
      @options_data.ticket_labels.map (def) ->
        options.options.push {title: def.label, value: def.label}
      options.operators = ['contains', 'notcontains']
      def = @getStandardSelect(options)
      return def

    getCheckSlaStatus: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-slas.html')

        getData: ->
          defer = me.$q.defer()
          me.loadDataOptions().then(->
            options = []
            for sla in me.options_data['ticket_slas']
              options.push({
                title: sla.title,
                value: sla.id
              })

            defer.resolve({
              options: options
            })
          )

          return defer.promise

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              options = value.options || {}
              return {
                op:          value.op || 'contains',
                sla_ids:     options.sla_ids || [],
                is_complete: !!options.is_complete,
                sla_status:  options.sla_status || 'passing',
                show_status: !!options.sla_status
              }

            getValue: (model = {}, data) ->
              value = {
                type: 'CheckSlaStatus',
                op: model.op || 'contains',
                options: {
                  sla_ids: model.sla_ids || []
                  is_complete: if model.op == 'contains' then !!model.is_complete else null,
                  sla_status: if model.show_status and model.sla_status and model.op == 'contains' then model.sla_status else null
                }
              }
              return value
            }
      }

    getCheckStatus: (options = {}) ->
      options.propName = 'status'
      options.template = 'OptionBuilder/type-criteria-status.html'
      def = @getStandardSelect(options)
      return def

    getCheckSubject: (options = {}) ->
      options.propName = 'subject'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckAgentMessage: (options = {}) ->
      options.propName = 'message'
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckTicketCharge: (options = {}) ->
      options.propName = 'amount'
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex', 'is', 'not', 'gt', 'gte', 'lt', 'lte']
      def = @getStandardInput(options)
      return def

    getCheckExpression: (options = {}) ->
      options.propName = 'expr'
      options.operators = ['is', 'not']
      def = @getStandardInput(options)
      return def

    getCheckCreationSystem: (options = {}) ->
      options.propName = 'creation_system'
      options.operators = ['is', 'not']
      options.options = [
        {title: "Created by a user via the portal", value: "web.person.portal"},
        {title: "Created by a user via the widget", value: "web.person.widget"},
        {title: "Created by a user via an embedded form", value: "web.person.embed"},
        {title: "Created by a user via email", value: "gateway.person"},
        {title: "Created by an agent via the agent interface", value: "web.agent.portal"},
        {title: "Created by an agent via email", value: "gateway.agent"},
        {title: "Create by the API in a user context", value: "web.api.person"},
        {title: "Create by the API in an agent context", value: "web.api.agent"},
      ]
      def = @getStandardSelect(options)
      return def

    getCheckCreationSystemOption: (options = {}) ->
      options.propName = 'creation_system_option'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckAgentNote: (options = {}) ->
      options.propName = 'message'
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckUserMessage: (options = {}) ->
      options.propName = 'message'
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckHasAttach: (options = {}) ->
      options.propName = 'with_attach'
      def = @getStandardIs(options)
      return def

    getCheckHasAttachType: (options = {}) ->
      options.propName = 'attach_type'
      options.operators = ['is', 'not']
      def = @getStandardInput(options)
      return def

    getCheckHasAttachName: (options = {}) ->
      options.propName = 'attach_name'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckUserName: (options = {}) ->
      options.propName = 'name'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckUserId: (options = {}) ->
      options.propName = 'id'
      options.operators = ['is', 'not']
      options.url = '/people/quick_search'
      options.map = (data) ->
        id: data.person.id
        email: data.person.primary_email?.email
        first_name: data.person.first_name
        last_name: data.person.last_name
        name: data.person.name
      format = (item) ->
        if item.name and item.email
          "#{item['name'] || ''} (#{item.email || ''})"
        else if item.name
          "#{item['name'] || ''}"
        else if item.email
          "#{item.email || ''}"
        else
          ""
      options.inputOptions =
        formatResult: format
        formatSelection: format
        ajax:
          data: (term, page) -> { query: term, limit: 10, with_agents: false, start_with: true }
      @getRemoteInput options

    getCheckUserEmail: (options = {}) ->
      options.propName = 'email'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      options.url = '/people/quick_search_email'
      options.hardcodedSkipLoadById = true
      options.map = (email) -> {id: email, email: email}
      format = (item) -> item?.email || ''
      options.inputOptions =
        formatResult: format
        formatSelection: format
        tags: true
        createSearchChoice: (term) -> {id: term, email: term}
        ajax:
          data: (term, page) -> { query: term, limit: 10 }
      @getRemoteInput options

    getCheckUserLabel: (options = {}) ->
      options.propName = 'labels'
      options.operators = ['contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getCheckUserUsergroups: (options = {}) ->
      options.propName = 'usergroup_ids'
      options.dataName = 'usergroups'
      options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from']
      def = @getStandardSelect(options)
      return def

    getCheckUserLanguage: (options = {}) ->
      options.propName = 'language_ids'
      options.dataName = 'langs'
      options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from']
      def = @getStandardSelect(options)
      return def

    getCheckUserOrgManager: (options = {}) ->
      options.propName = 'is_manager'
      def = @getStandardIs(options)
      return def

    getCheckUserIsDisabled: (options = {}) ->
      options.propName = 'is_disabled'
      def = @getStandardIs(options)
      return def

    getCheckUserIsNew: (options = {}) ->
      options.propName = 'is_new'
      def = @getStandardIs(options)
      return def

    getCheckUserValidAgent: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html')

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              return {
                op: value.op || 'is',
                options: [
                  { value: 'is', title: 'User has been validated by an agent' },
                  { value: 'not', title: 'User is waiting to be validated by an agent' }
                ]
              }

            getValue: (model = {}, data) ->
              return {
                type: 'CheckUserValidAgent',
                op: model.op || 'is'
                options: { run:true }
              }
          }
      }

    getCheckUserValidEmail: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-opselect.html')

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              return {
              op: value.op || 'is',
              options: [
                { value: 'is', title: 'User has validated their email address' },
                { value: 'not', title: 'User has not yet validated their email address' }
              ]
              }

            getValue: (model = {}, data) ->
              return {
              type: 'CheckUserValidEmail',
              op: model.op || 'is'
              options: { run:true }
              }
          }
      }

    getCheckOrgName: (options = {}) ->
      options.propName = 'name'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckOrgId: (options = {}) ->
      options.propName = 'id'
      options.operators = ['is', 'not', 'isset', 'not_isset']
      options.url = '/organizations'
      options.map = (data) ->
        id: data.organization?.id
        name: data.organization?.name
      format = (item) -> item['name']
      options.inputOptions =
        formatResult: format
        formatSelection: format
        ajax:
          data: (term, page) -> { name: term, limit: 10 }
          results: (data, page) ->
            results = []
            obj = data.data?.organizations
            for own k, v of obj
              results.push {id: v.id, name: v.name}
            return {results: results}
      @getRemoteInput options

    getCheckOrgLabel: (options = {}) ->
      options.propName = 'labels'
      options.operators = ['contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getCheckOrgEmailDomain: (options = {}) ->
      options.propName = 'email_domain'
      options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex']
      def = @getStandardInput(options)
      return def

    getCheckOrgUsergroups: (options = {}) ->
      options.propName  = 'usergroup_ids'
      options.dataName  = 'usergroups'
      options.operators = ['is', 'not']
      def = @getStandardSelect(options)
      return def

    getCheckDayOfWeek: (options = {}) ->
      me = @
      return {
      getTemplate: ->
        return me.dpTemplateManager.get('OptionBuilder/type-criteria-dayofweek.html')

      getData: ->
        return {}

      getDataFormatter: ->
        return {
          getViewValue: (value = {}, data) ->
            options = value.options || {}
            days = [null, false, false, false, false, false, false, false]
            if options.days
              for d in options.days
                days[d] = true

            return {
              op: value.op || 'is',
              tz: options.tz || 'UTC',
              days: days
            }

          getValue: (model = {}, data) ->
            days = []
            for v, k in model.days
              if v
                days.push(k)

            value = {
              type: 'CheckDayOfWeek',
              op: 'is',
              options:{
                tz: model.tz || 'UTC',
                days: days,
                var: 'now'
              }
            }
            return value
          }
      }

    getCheckDateCreated: (options = {}) ->
      def = @getDateInput(options)
      return def

    getCheckTimeOfDay: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-timeofday.html')

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              options = value.options || {}

              time1 = (options.time1 || '8:0').split(':')
              time2 = (options.time2 || '18:0').split(':')

              return {
                tz:          options.tz || 'UTC',
                start_hour:  time1[0],
                start_min:   time1[1],
                end_hour:    time2[0],
                end_min:     time2[1]
              }

            getValue: (model = {}, data) ->
              time1 = (model.start_hour || '8') + ':' + (model.start_min || '0')
              time2 = (model.end_hour || '18') + ':' + (model.end_min || '0')

              value = {
                type: 'CheckTimeOfDay',
                op: 'between',
                options: {
                  var:   'now',
                  tz:    model.tz || 'UTC',
                  time1: time1,
                  time2: time2
                }
              }
              return value
          }
      }

    getCheckWorkingHours: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-workinghours.html')

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              return {
                op: value.op || 'is'
                set_name: value.options?.set_name || 'default'
                working_hours: value.options?.working_hours || {}
              }

            getValue: (model = {}, data) ->
              return {
                type: 'CheckWorkingHours'
                op: model.op
                options:
                  set_name: model.set_name || 'default'
                  working_hours: model.working_hours
              }
          }
      }

    getCheckWebhookVar: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-var.html')

        getData: ->
          return {
            label : 'Check Webhook Var'
          }

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              options = value?.options || {}
              return {
                op: value.op || 'isset',
                name: options.name || '',
                value: options.value || ''
              }
            getValue: (model = {}, data) ->
              value = {}
              value.type = 'CheckWebhookVar'
              value.op = model.op || 'isset'
              value.options = {
                name: model.name || '',
                value: model.value || ''
              }
              return value
          }
      }

    getCheckUserVar: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-var.html')

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              options = value?.options || {}
              return {
                op: value.op || 'isset',
                name: options.name || '',
                value: options.value || ''
              }
            getValue: (model = {}, data) ->
              value = {}
              value.type = 'CheckUserVar'
              value.op = model.op || 'isset'
              value.options = {
                name: model.name || '',
                value: model.value || ''
              }
              return value
          }
      }

    getIsEmailed: (name, tpl) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/' + tpl)

        getData: ->
          return me.loadDataOptions()

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              options = value?.options || {}
              return {
                op: value.op || 'is',
                template: options.template || null,
                custom_email_tpls: me.options_data?.custom_email_tpls
                with_template: if options.template then true else false
              }
            getValue: (model = {}, data) ->
              value = {}
              value.type = name
              value.op = model.op || 'is'
              value.options = {
                template: if model.with_template and model.template then model.template else null
              }
              return value
          }
      }

    getCheckUserIsEmailed: ->
      return @getIsEmailed('CheckUserIsEmailed', 'type-criteria-userisemailed.html')

    getCheckAgentIsEmailed: ->
      return @getIsEmailed('CheckAgentIsEmailed', 'type-criteria-agentisemailed.html')

    getCheckApiKey: (options = {}) ->
      options.propName = 'api_key_id'
      options.dataName = 'api_keys'
      options.operators = ['is', 'not']
      options.single = true
      options.optionsFormatter = (options) ->
        opts = []

        for key in options
          name = if key.person then key.person.display_name else 'Super User'
          opts.push({
            value: key.id,
            title: [name, key.note].join ' | '
          })

        opts

      @getStandardSelect(options)

    getCheckTicketSatisfaction: (options = {}) ->
      options.propName = 'feedback_rating'
      options.dataName = 'feedback_rating'
      options.operators = ['is', 'isset', 'not_isset', 'changed', 'changed_to']
      options.single = true
      options.optionsFormatter = (options) ->
        return [{value: -1, title: 'Negative'}, {value: 0, title: 'Neutral'}, {value: 1, title: 'Positive'}]
      @getStandardSelect options

    getCheckJIRANewComment: (options = {}) ->
      options.propName = 'message'
      options.operators = ['isset', 'not_isset', 'contains', 'notcontains', 'is_regex', 'not_regex']
      @getStandardInput options

    getCheckJIRAIssueStatus: (options = {}) ->
      me = @
      getTemplate: -> me.dpTemplateManager.get 'OptionBuilder/type-criteria-jira-issue-status.html'
      getData: -> {}
      getDataFormatter: ->
        getViewValue: (value = {}, data) ->
          op: value.op || 'changed'
          all: value.options?.all
          status: value.options?.status
          statuses: me.options_data.jira_settings.meta.statuses
        getValue: (model = {}, data) ->
          type: 'CheckJIRAIssueStatus',
          op: model.op
          options:
            all: model.all || ''
            status: model.status

    getCheckJIRANewLinkedIssue: (options = {}) ->
      me = @
      getTemplate: -> me.dpTemplateManager.get 'OptionBuilder/type-criteria-jira-linked-issue.html'
      getData: -> {}
      getDataFormatter: ->
        getViewValue: (value = {}, data) ->
          strict_project: value.options?.project?
          project: value.options?.project
          projects: me.options_data.jira_settings.meta.projects
        getValue: (model = {}, data) ->
          project = model.project
          project = null if !model.strict_project
          type: 'CheckJIRANewLinkedIssue',
          op: 'is'
          options:
            project: project
