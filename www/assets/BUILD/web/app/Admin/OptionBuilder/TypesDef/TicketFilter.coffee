define [
  'Admin/OptionBuilder/TypesDef/BaseCriteriaTypesDef',
], (
  BaseCriteriaTypesDef
) ->
  class Admin_OptionBuilder_TypesDef_TicketFilter extends BaseCriteriaTypesDef
    init: ->
      @options_data = null

    getOperators: (options) ->
      return options.operators || ['is', 'not']

    getOptionsForTypes: (types, typesData = null) ->
      set_options = []
      #------------------------------
      # Ticket Criteria
      #------------------------------

      options = []

      options.push({
        title: 'Status',
        value: 'FilterStatus'
      })

      options.push({
        title: 'Brand',
        value: 'FilterBrand'
      })

      options.push({
        title: 'Department',
        value: 'FilterDepartment'
      })

      options.push({
        title: 'Agent',
        value: 'FilterAgent'
      })

      options.push({
        title: 'Followers',
        value: 'FilterAgentParticipant'
      })

      options.push({
        title: 'Agent Team',
        value: 'FilterAgentTeam'
      })

      options.push({
        title: 'Product',
        value: 'FilterProduct'
      })

      options.push({
        title: 'Category',
        value: 'FilterCategory'
      })

      options.push({
        title: 'Priority',
        value: 'FilterPriority'
      })

      options.push({
        title: 'Urgency',
        value: 'FilterUrgency'
      })

      options.push({
        title: 'Workflow',
        value: 'FilterWorkflow'
      })

      options.push({
        title: 'Labels',
        value: 'FilterLabels'
      })

      options.push({
        title: 'Linked feedback items'
        value: 'FilterFeedbackLinks'
      })

      options.push({
        title: 'Email Account',
        value: 'FilterEmailAccount'
      })

      options.push({
        title: 'Subject',
        value: 'FilterSubject'
      })

      options.push({
        title: 'Hold',
        value: 'FilterHoldStatus'
      })

      options.push({
        title: 'Date Created',
        value: 'FilterDateCreated'
      })

      options.push({
        title: 'Date Resolved',
        value: 'FilterDateResolved'
      })

      options.push({
        title: 'Date Archived',
        value: 'FilterDateArchived'
      })

      options.push({
        title: 'Date Of Last Agent Reply',
        value: 'FilterDateLastAgentReply'
      })

      options.push({
        title: 'Date Of Last User Reply',
        value: 'FilterDateLastUserReply'
      })

      options.push({
        title: 'User Waiting Time',
        value: 'FilterUserWaiting'
      })

      options.push({
        title: 'Total User Waiting Time',
        value: 'FilterTotalUserWaiting'
      })

      options.push
        title: 'Ticket SLA'
        value: 'FilterSla'

      options.push
        title: 'Ticket SLA Status'
        value: 'FilterSlaStatus'

      set_options.push({
        title: 'Ticket Criteria',
        subOptions: options
      })

      #------------------------------
      # Ticket Fields
      #------------------------------

      if @options_data?.ticket_fields
        options = []

        for f in @options_data.ticket_fields
          options.push({
            title: f.title,
            value: @initFieldGetter('FilterTicketField', f)
          })

        if options.length
          set_options.push({
            title: 'Ticket Fields',
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
            value: @initFieldGetter('FilterUserField', f)
          })

        if options.length
          set_options.push({
            title: 'Person Fields',
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
            value: @initFieldGetter('FilterOrgField', f)
          })

        if options.length
          set_options.push({
            title: 'Organization Fields',
            subOptions: options
          })

      #------------------------------
      # Person
      #------------------------------

      options = []

      options.push({
        title: 'Name',
        value: 'FilterUserName'
      })

      options.push({
        title: 'Email Address',
        value: 'FilterUserEmailAddress'
      })

      options.push({
        title: 'Label',
        value: 'FilterUserLabels'
      })

      options.push({
        title: 'Usergroup',
        value: 'FilterUserGroups'
      })

      options.push({
        title: 'Language',
        value: 'FilterUserLanguage'
      })

      options.push({
        title: 'Is manager of organization',
        value: 'FilterUserIsManager'
      })

      options.push({
        title: 'Is disabled',
        value: 'FilterUserIsDisabled'
      })

      options.push({
        title: 'User Contact Phone',
        value: 'FilterUserContactPhone'
      })

      options.push({
        title: 'User Contact Address',
        value: 'FilterUserContactAddress'
      })

      options.push({
        title: 'User Contact Instant Messaging',
        value: 'FilterUserContactIm'
      })

      options.push({
        title: 'Date User Created',
        value: 'FilterUserDateCreated'
      })

      set_options.push({
        title: 'User Criteria',
        subOptions: options
      })

      #------------------------------
      # Org
      #------------------------------

      options = []

      options.push({
        title: 'Organization',
        value: 'FilterOrgId'
      })

      options.push({
        title: 'Organization Name',
        value: 'FilterOrgName'
      })

      options.push({
        title: 'Organization Label',
        value: 'FilterOrgLabels'
      })

      options.push({
        title: 'Organization Contact Phone',
        value: 'FilterOrgContactPhone'
      })

      options.push({
        title: 'Organization Contact Address',
        value: 'FilterOrgContactAddress'
      })

      options.push({
        title: 'Organization Contact Instant Messaging',
        value: 'FilterOrgContactIm'
      })

      options.push({
        title: 'Organization Email Domain',
        value: 'FilterOrgEmailDomain'
      })

      options.push({
        title: 'Organization Linked Usergroup',
        value: 'FilterOrgGroups'
      })

      options.push({
        title: 'Date Organization Created',
        value: 'FilterOrgDateCreated'
      })

      set_options.push({
        title: 'Organization Criteria',
        subOptions: options
      })

      return set_options

    resetData: ->
      @options_data = null
      @loadDataPromise = null

    loadDataOptions: ->
      if !@loadDataPromise
        @loadDataPromise = @Api.sendDataGet({
          agents:          '/agents'
          agent_teams:      '/agent_teams'
          ticket_brands:      '/ticket_brands'
          ticket_deps:      '/ticket_deps'
          ticket_cats:      '/ticket_cats'
          ticket_prods:     '/ticket_prods'
          ticket_pris:      '/ticket_pris'
          ticket_works:     '/ticket_works'
          ticket_fields:    '/ticket_fields'
          ticket_slas:      '/ticket_slas'
          ticket_labels:    '/labels/definitions/tickets'
          user_fields:      '/user_fields'
          user_labels:      '/labels/definitions/people'
          org_fields:       '/org_fields'
          org_labels:       '/labels/definitions/organizations'
          ticket_accounts:  '/email_accounts'
          usergroups:       '/user_groups'
          organizations:    '/organizations?per_page=250'
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
          options_data['ticket_fields']     = data.ticket_fields?.custom_fields
          options_data['ticket_slas']       = data.ticket_slas
          options_data['ticket_labels']     = data.ticket_labels
          options_data['org_fields']        = data.org_fields?.custom_fields
          options_data['org_labels']        = data.org_labels
          options_data['user_fields']       = data.user_fields?.custom_fields
          options_data['user_labels']       = data.user_labels
          options_data['ticket_prods']      = data.ticket_prods?.products
          options_data['ticket_accounts']   = data.ticket_accounts.email_accounts
          options_data['usergroups']        = data.usergroups.groups
          options_data['organizations']     = data.organizations.organizations

          @options_data = options_data

          if @options_data?.ticket_fields
            for f in @options_data.ticket_fields
              @initFieldGetter 'FilterTicketField', f, true
          if @options_data?.user_fields
            for f in @options_data.user_fields
              @initFieldGetter 'FilterUserField', f, true
          if @options_data?.org_fields
            for f in @options_data.org_fields
              @initFieldGetter 'FilterOrgField', f, true
        )

      @loadDataPromise

    getFilterWorkflow: (options = {}) ->
      options.propName = 'workflow_ids'
      options.dataName = 'ticket_works'
      options.extraOptions = [
        {title: 'None', value: 0}
      ]
      def = @getStandardSelect(options)
      return def

    getFilterLabels: (options = {}) ->
      options.propName = 'labels'
      options.type_title = 'Labels'
      options.tags = true
      options.options = []
      @options_data.ticket_labels.map (def) ->
        options.options.push {title: def.label, value: def.label}
      options.operators = ['contains', 'notcontains']
      def = @getStandardSelect(options)
      return def

    getFilterUrgency: (options = {}) ->
      options.propName = 'urgency'
      options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte']
      def = @getStandardInput(options)
      return def

    getFilterPriority: (options = {}) ->
      options.propName = 'priority_ids'
      options.dataName = 'ticket_pris'
      options.extraOptions = [
        {title: 'None', value: 0}
      ]
      def = @getStandardSelect(options)
      return def

    getCheckUrgency: (options = {}) ->
      options.propName = 'urgency'
      options.operators = ['is', 'not', 'gt', 'gte', 'lt', 'lte']
      def = @getStandardInput(options)
      return def

    getFilterCategory: (options = {}) ->
      options.propName = 'category_ids'
      options.dataName = 'ticket_cats'
      def = @getStandardSelect(options)
      return def

    getFilterStatus: (options = {}) ->
      options.propName = 'status'
      options.template = 'OptionBuilder/type-filter-status.html'
      options.noArchive = true
      def = @getStandardSelect(options)
      return def

    getFilterHoldStatus: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-filter-hold.html')

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              return {
                op: 'is',
                value: if value.options?.is_hold then '1' else '0'
              }
            getValue: (model = {}, data) ->
              value = {}
              value.type = 'FilterHoldStatus'
              value.op = model.op
              value.options = {
                is_hold: if parseInt(model.value) == 1 then true else false
              }
              return value
          }
      }

    getFilterSla: (options = {}) ->
      options.propName = 'sla_id'
      options.dataName = 'ticket_slas'
      options.optionsFormatter = (res) ->
        (res.slas || []).map (item) -> {title: item.title, value: item.id}
      @getStandardSelect(options)

    getFilterSlaStatus: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get 'OptionBuilder/type-filter-sla.html'

        getData: ->
          defer = me.$q.defer()
          me.loadDataOptions().then ->
            options = []
            for sla in me.options_data.ticket_slas?.slas
              options.push
                title: sla.title
                value: sla.id
            defer.resolve {options: options}
          defer.promise

        getDataFormatter: ->
          getViewValue: (value = {}, data) ->
            options = value.options || {}
            return {
              op:           value.op || 'is'
              sla_status:   options.sla_status || 'fail'
              sla_id:       options.sla_id || 0
            }
          getValue: (model = {}, data) ->
            return {
              type:         'FilterSlaStatus'
              op:           model.op
              options:
                sla_status: model.sla_status || 'fail'
                sla_id:     model.sla_id || 0
            }
      }

    getFilterUserWaiting: (options = {}) ->
      options.propName = 'time'
      def = @getTimeElapsedInput(options)
      return def

    getFilterTotalUserWaiting: (options = {}) ->
      options.propName = 'time'
      def = @getTimeElapsedInput(options)
      return def

    getFilterDateCreated: (options = {}) ->
      def = @getDateInput(options)
      return def

    getFilterDateResolved: (options = {}) ->
      def = @getDateInput(options)
      return def

    getFilterDateArchived: (options = {}) ->
      def = @getDateInput(options)
      return def

    getFilterDateLastAgentReply: (options = {}) ->
      def = @getDateInput(options)
      return def

    getFilterDateLastUserReply: (options = {}) ->
      def = @getDateInput(options)
      return def

    getFilterBrand: (options = {}) ->
      options.propName = 'brand_ids'
      options.dataName = 'ticket_brands'
      def = @getStandardSelect(options)
      return def

    getFilterDepartment: (options = {}) ->
      options.propName = 'department_ids'
      options.dataName = 'ticket_deps'
      def = @getStandardSelect(options)
      return def

    getFilterAgent: (options = {}) ->
      options.propName = 'agent_ids'
      options.dataName = 'agents'
      options.extraOptions = [
        {title: 'Unassigned', value: 0},
        {title: 'Current Agent', value: -1}
      ]
      def = @getStandardSelect(options)
      return def

    getFilterAgentParticipant: (options = {}) ->
      options.propName = 'agent_ids'
      options.dataName = 'agents'
      options.extraOptions = [
        {title: 'Current Agent', value: -1}
      ]
      def = @getStandardSelect(options)
      return def

    getFilterAgentTeam: (options = {}) ->
      options.propName = 'team_ids'
      options.dataName = 'agent_teams'
      options.extraOptions = [
        {title: 'No Team', value: 0},
        {title: 'Current Agent\'s Team', value: -1}
      ]
      def = @getStandardSelect(options)
      return def

    getFilterProduct: (options = {}) ->
      options.propName = 'product_ids'
      options.dataName = 'ticket_prods'
      options.extraOptions = [
        {title: 'None', value: 0}
      ]
      def = @getStandardSelect(options)
      return def

    getFilterEmailAccount: (options = {}) ->
      options.propName = 'email_account_ids'
      options.dataName = 'ticket_accounts'
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

    getFilterCcAddress: (options = {}) ->
      options.propName = 'cc_address'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterCcName: (options = {}) ->
      options.propName = 'cc_name'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterEmailHeader: (options = {}) ->
      options.propName = 'email_header_match'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterSubject: (options = {}) ->
      options.propName = 'subject'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterFeedbackLinks: (options = {}) ->
      options.propName = 'feedback_links'
      options.operators = ['isset', 'not_isset', 'is']
      def = @getStandardInput(options)
      return def

    getFilterMessage: (options = {}) ->
      options.propName = 'message'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterHasAttach: (options = {}) ->
      options.propName = 'with_attach'
      def = @getStandardIs(options)
      return def

    getFilterHasAttachType: (options = {}) ->
      options.propName = 'attach_type'
      options.operators = ['is', 'not']
      def = @getStandardInput(options)
      return def

    getFilterHasAttachName: (options = {}) ->
      options.propName = 'attach_name'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterUserName: (options = {}) ->
      options.propName = 'name'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterUserEmailAddress: (options = {}) ->
      options.propName = 'email'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterUserLabels: (options = {}) ->
      options.propName = 'labels'
      options.options = []
      @options_data.user_labels.map (def) ->
        options.options.push {title: def.label, value: def.label}
      options.operators = ['contains', 'notcontains']
      def = @getStandardSelect(options)
      return def

    getFilterUserGroups: (options = {}) ->
      options.propName = 'group_ids'
      options.dataName = 'usergroups'
      def = @getStandardSelect(options)
      return def

    getFilterUserLanguage: (options = {}) ->
      options.propName = 'language_ids'
      options.dataName = 'languages'
      def = @getStandardSelect(options)
      return def

    getFilterUserIsManager: (options = {}) ->
      options.propName = 'is_manager'
      def = @getStandardIs(options)
      return def

    getFilterUserIsDisabled: (options = {}) ->
      options.propName = 'is_disabled'
      def = @getStandardIs(options)
      return def

    getFilterUserContactPhone: (options = {}) ->
      options.propName = 'phone'
      options.operators = ['contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterUserContactAddress: (options = {}) ->
      options.propName = 'address'
      options.operators = ['contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterUserContactIm: (options = {}) ->
      options.propName = 'im'
      options.operators = ['contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterUserDateCreated: (options = {}) ->
      def = @getDateInput(options)
      return def

    getFilterOrgId: (options = {}) ->
      options.propName = 'id'
      options.operators = ['is', 'not']
      options.url = '/organizations'
      options.isMulti = true
      options.map = (data) -> {id: data.organization.id, name: data.organization.name}
      format = (item) -> item['name']
      options.inputOptions =
        formatResult: format
        formatSelection: format
        ajax:
          data: (term, page) -> { name: term, limit: 10 }
          results: (data, page) ->
            results = []
            for own k, v of data.data?.organizations
              results.push {id: v.id, name: v.name}
            return {results: results}
      @getRemoteInput options

    getFilterOrgName: (options = {}) ->
      options.propName = 'name'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterOrgLabels: (options = {}) ->
      options.propName = 'labels'
      options.options = []
      @options_data.org_labels.map (def) ->
        options.options.push {title: def.label, value: def.label}
      options.operators = ['contains', 'notcontains']
      def = @getStandardSelect(options)
      return def

    getFilterOrgContactPhone: (options = {}) ->
      options.propName = 'phone'
      options.operators = ['contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterOrgContactAddress: (options = {}) ->
      options.propName = 'address'
      options.operators = ['contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterOrgContactIm: (options = {}) ->
      options.propName = 'im'
      options.operators = ['contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterOrgEmailDomain: (options = {}) ->
      options.propName = 'domain'
      options.operators = ['is', 'not', 'contains', 'notcontains']
      def = @getStandardInput(options)
      return def

    getFilterOrgGroups: (options = {}) ->
      options.propName  = 'group_ids'
      options.dataName  = 'usergroups'
      options.operators = ['is', 'not']
      def = @getStandardSelect(options)
      return def

    getFilterOrgDateCreated: (options = {}) ->
      def = @getDateInput(options)
      return def

    getFilterDayOfWeek: (options = {}) ->
      me = @
      return {
      getTemplate: ->
        return me.dpTemplateManager.get('OptionBuilder/type-criteria-dayofweek.html')

      getData: ->
        return {

        }

      getDataFormatter: ->
        return {
        getViewValue: (value = {}, data) ->
          return {
            op: value.op || 'is'
          }

        getValue: (model = {}, data) ->
          value = {}
          return value
        }
      }

    getFilterTimeOfDay: (options = {}) ->
      me = @
      return {
      getTemplate: ->
        return me.dpTemplateManager.get('OptionBuilder/type-criteria-timeofday.html')

      getData: ->
        return {

        }

      getDataFormatter: ->
        return {
        getViewValue: (value = {}, data) ->
          return {
          op: value.op || 'is'
          }

        getValue: (model = {}, data) ->
          value = {}
          return value
        }
      }

    getFilterWorkingHours: (options = {}) ->
      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get('OptionBuilder/type-criteria-workinghours.html')

        getData: ->
          return {

          }

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              return {
                op: value.op || 'is'
              }

          getValue: (model = {}, data) ->
            value = {}
            return value
          }
      }
