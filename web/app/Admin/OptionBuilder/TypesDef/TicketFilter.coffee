define [
	'Admin/OptionBuilder/TypesDef/BaseCriteriaTypesDef',
], (
	BaseCriteriaTypesDef
) ->
	class Admin_OptionBuilder_TypesDef_TicketFilter extends BaseCriteriaTypesDef
		init: ->
			@options_data = null

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
				title: 'Department',
				value: 'FilterDepartment'
			})

			options.push({
				title: 'Agent',
				value: 'FilterAgent'
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
			}

			options.push({
				title: 'Labels',
				value: 'FilterLabels'
			}))

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
				value: 'FilterDateClosed'
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
				value: 'FilterUserUsergroups'
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
			if @options_data
				p = @$q.fcall( =>
					return @options_data
				)
			else
				@options_data = {}
				p = @Api.sendDataGet({
					'agents':          '/agents',
					'agent_teams':     '/agent_teams',
					'ticket_deps':     '/ticket_deps',
					'ticket_cats':     '/ticket_cats',
					'ticket_prods':    '/ticket_prods',
					'ticket_pris':     '/ticket_pris',
					'ticket_works':    '/ticket_works',
					'ticket_fields':   '/ticket_fields',
					'user_fields':     '/user_fields',
					'org_fields':      '/org_fields',
					'ticket_accounts': '/email_accounts',
					'usergroups':      '/user_groups',
				}).then( (result) =>
					data = result.data
					options_data = {}
					options_data['agents']           = data.agents.agents
					options_data['agent_teams']      = data.agent_teams.agent_teams
					options_data['ticket_deps']      = data.ticket_deps.departments
					options_data['ticket_cats']      = data.ticket_cats.categories
					options_data['ticket_pris']      = data.ticket_pris.priorities
					options_data['ticket_works']     = data.ticket_works.workflows
					options_data['ticket_fields']    = data.ticket_fields?.custom_fields
					options_data['org_fields']       = data.org_fields?.custom_fields
					options_data['user_fields']      = data.user_fields?.custom_fields
					options_data['ticket_prods']     = data.ticket_prods?.products
					options_data['ticket_accounts']  = data.ticket_accounts.email_accounts
					options_data['usergroups']       = data.usergroups.groups

					@options_data = options_data

					if @options_data?.ticket_fields
						for f in @options_data.ticket_fields
							@initFieldGetter('FilterTicketField', f)
					if @options_data?.user_fields
						for f in @options_data.user_fields
							@initFieldGetter('FilterUserField', f)
				)

			return p

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
			options.operators = ['contains', 'notcontains']
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
								value: if value.is_hold then '1' else '0'
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

		getFilterDateClosed: (options = {}) ->
			def = @getDateInput(options)
			return def

		getFilterDateLastAgentReply: (options = {}) ->
			def = @getDateInput(options)
			return def

		getFilterDateLastUserReply: (options = {}) ->
			def = @getDateInput(options)
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
						title: acc.address
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
			options.operators = ['contains', 'notcontains']
			def = @getStandardInput(options)
			return def

		getFilterUserUsergroups: (options = {}) ->
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
			options.propName = 'address'
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

		getFilterOrgName: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'notcontains']
			def = @getStandardInput(options)
			return def

		getFilterOrgLabels: (options = {}) ->
			options.propName = 'labels'
			options.operators = ['contains', 'notcontains']
			def = @getStandardInput(options)
			return def

		getFilterOrgContactPhone: (options = {}) ->
			options.propName = 'address'
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
			options.propName = 'name'
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