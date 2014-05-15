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


		getOptionsForTypes: (types, typesData = null) ->
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
					title: 'Email Header',
					value: 'CheckEmailHeader'
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
				title: 'Department',
				value: 'CheckDepartment'
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

			if types.indexOf('web.agent') != -1
				options.push({
					title: 'Workflow',
					value: 'CheckWorkflow'
				})

			options.push({
				title: 'Subject',
				value: 'CheckSubject'
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
						value: @initFieldGetter('CheckTicketField', f)
					})

				if options.length
					set_options.push({
						title: 'Ticket Fields',
						subOptions: options
					})

			#------------------------------
			# Attachment Criteria
			#------------------------------

			options = []

			options.push({
				title: 'Has attachment',
				value: 'CheckHasAttach'
			})
			options.push({
				title: 'Has attachment type',
				value: 'CheckHasAttachType'
			})
			options.push({
				title: 'Has attachment named',
				value: 'CheckHasAttachName'
			})

			set_options.push({
				title: 'Attachment Criteria',
				subOptions: options
			})

			#------------------------------
			# Person
			#------------------------------

			options = []

			options.push({
				title: 'Name',
				value: 'CheckUserName'
			})

			options.push({
				title: 'Email Address',
				value: 'CheckUserEmailAddress'
			})

			options.push({
				title: 'Label',
				value: 'CheckUserLabels'
			})

			options.push({
				title: 'Usergroup',
				value: 'CheckUserUsergroups'
			})

			options.push({
				title: 'Language',
				value: 'CheckUserLanguage'
			})

			options.push({
				title: 'Is manager of organization',
				value: 'CheckUserIsManager'
			})

			options.push({
				title: 'User is new',
				value: 'CheckUserIsNew'
			})

			options.push({
				title: 'User is awaiting agent validation',
				value: 'CheckUserValidAgent'
			})

			options.push({
				title: 'User is awaiting email validation',
				value: 'CheckUserValidEmail'
			})

			options.push({
				title: 'Is disabled',
				value: 'CheckPersonIsDisabled'
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
				title: 'Name',
				value: 'CheckOrgName'
			})

			options.push({
				title: 'Label',
				value: 'CheckOrgLabels'
			})

			options.push({
				title: 'Email Domain',
				value: 'CheckOrgEmailDomain'
			})

			options.push({
				title: 'Linked Usergroup',
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
						value: @initFieldGetter('OrgField', f)
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
				title: 'Day of week',
				value: 'CheckDayOfWeek'
			})

			options.push({
				title: 'Time of day',
				value: 'CheckTimeOfDay'
			})

			options.push({
				title: 'Within working hours',
				value: 'CheckWorkingHours'
			})

			set_options.push({
				title: 'Dates',
				subOptions: options
			})

			return set_options

		loadDataOptions: ->
			if not @loadDataPromise
				@loadDataPromise = @Api.sendDataGet({
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
					options_data['ticket_prods']     = data.ticket_prods?.products
					options_data['ticket_fields']    = data.ticket_fields?.custom_fields
					options_data['org_fields']       = data.org_fields?.custom_fields
					options_data['user_fields']      = data.user_fields?.custom_fields
					options_data['email_accounts']   = data.ticket_accounts.email_accounts
					options_data['usergroups']       = data.usergroups.groups
					@options_data = options_data

					if @options_data?.ticket_fields
						for f in @options_data.ticket_fields
							@initFieldGetter('CheckTicketField', f)
					if @options_data?.user_fields
						for f in @options_data.user_fields
							@initFieldGetter('CheckUserField', f)
					if @options_data?.org_fields
						for f in @options_data.org_fields
							@initFieldGetter('CheckOrgField', f)
				)

			return @loadDataPromise

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

		getCheckCategory: (options = {}) ->
			options.propName = 'category_ids'
			options.dataName = 'ticket_cats'
			options.operators = ['is', 'not', 'touched', 'nottouched', 'changed', 'changed_to', 'changed_from']
			options.extraOptions = [
				{title: 'None', value: 0}
			]
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
			options.propName = 'gateway_ids'
			options.dataName = 'email_accounts'
			options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from']
			options.optionsFormatter = (options) ->
				opts = []

				for acc in options
					opts.push({
						value: acc.id,
						title: acc.email_address
					})

				return opts

			def = @getStandardSelect(options)
			return def

		getCheckEmailSubject: (options = {}) ->
			options.propName = 'subject'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckEmailBody: (options = {}) ->
			options.propName = 'body'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckEmailToName: (options = {}) ->
			options.propName = 'to_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckEmailToAddress: (options = {}) ->
			options.propName = 'to_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckEmailFromName: (options = {}) ->
			options.propName = 'from_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckEmailFromAddress: (options = {}) ->
			options.propName = 'from_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckCcAddress: (options = {}) ->
			options.propName = 'cc_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckCcName: (options = {}) ->
			options.propName = 'cc_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckEmailHeader: (options = {}) ->
			options.propName = 'email_header_match'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckSubject: (options = {}) ->
			options.propName = 'subject'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckAgentMessage: (options = {}) ->
			options.propName = 'message'
			options.operators = ['isset', 'not_isset', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckAgentNote: (options = {}) ->
			options.propName = 'message'
			options.operators = ['isset', 'not_isset', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckUserMessage: (options = {}) ->
			options.propName = 'message'
			options.operators = ['isset', 'not_isset', 'contains', 'not_contains', 'is_regex', 'not_regex']
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
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckUserName: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckUserEmailAddress: (options = {}) ->
			options.propName = 'email'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckUserLabels: (options = {}) ->
			options.propName = 'labels'
			options.operators = ['contains', 'not_contains']
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
			options.dataName = 'languages'
			options.operators = ['is', 'not', 'changed', 'changed_to', 'changed_from']
			def = @getStandardSelect(options)
			return def

		getCheckUserIsManager: (options = {}) ->
			options.propName = 'is_manager'
			def = @getStandardIs(options)
			return def

		getCheckPersonIsDisabled: (options = {}) ->
			options.propName = 'is_disabled'
			def = @getStandardIs(options)
			return def

		getCheckUserIsNew: (options = {}) ->
			options.propName = 'is_new'
			def = @getStandardIs(options)
			return def

		getCheckUserValidAgent: (options = {}) ->
			options.propName = 'is_valid_agent'
			def = @getStandardIs(options)
			return def

		getCheckUserValidEmail: (options = {}) ->
			options.propName = 'is_valid_email'
			def = @getStandardIs(options)
			return def

		getCheckOrgName: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCheckOrgLabels: (options = {}) ->
			options.propName = 'labels'
			options.operators = ['contains', 'not_contains']
			def = @getStandardInput(options)
			return def

		getCheckOrgEmailDomain: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
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

		getCheckTimeOfDay: (options = {}) ->
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

		getCheckWorkingHours: (options = {}) ->
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