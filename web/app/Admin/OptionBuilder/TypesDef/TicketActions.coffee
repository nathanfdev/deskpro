define ->
	class Admin_OptionBuilder_TypesDef_TicketCriteria
		constructor: (@$q, @Api, @dpTemplateManager) ->
			@options_data = null

		getOptionsForTypes: (types, typesData = null) ->
			set_options = []

			#------------------------------
			# Email Criteria
			#------------------------------

			if types.indexOf('email') != -1
				options = []

				options.push({
					title: 'Email Account',
					value: 'email_account'
				})

				options.push({
					title: 'Email Subject',
					value: 'email_subject'
				})

				options.push({
					title: 'Email Body',
					value: 'email_body'
				})

				options.push({
					title: 'To Name',
					value: 'email_to_name'
				})

				options.push({
					title: 'To Address',
					value: 'email_to_address'
				})

				options.push({
					title: 'From Name',
					value: 'email_from_name'
				})

				options.push({
					title: 'From Address',
					value: 'email_from_address'
				})

				options.push({
					title: 'CCd Name',
					value: 'email_cc_name'
				})

				options.push({
					title: 'CCd Address',
					value: 'email_cc_address'
				})

				options.push({
					title: 'Email Header',
					value: 'email_header_match'
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
				value: 'department'
			})

			options.push({
				title: 'Product',
				value: 'product'
			})

			options.push({
				title: 'Category',
				value: 'category'
			})

			options.push({
				title: 'Priority',
				value: 'priority'
			})

			if types.indexOf('web.agent') != -1
				options.push({
					title: 'Workflow',
					value: 'workflow'
				})

			options.push({
				title: 'Title',
				value: 'title'
			})

			options.push({
				title: 'Message',
				value: 'message'
			})

			set_options.push({
				title: 'Ticket Criteria',
				subOptions: options
			})

			#------------------------------
			# Attachment Criteria
			#------------------------------

			options = []

			options.push({
				title: 'Has attachment',
				value: 'with_attach'
			})
			options.push({
				title: 'Has attachment type',
				value: 'with_attach_type'
			})
			options.push({
				title: 'Has attachment named',
				value: 'with_attach_name'
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
				value: 'person_name'
			})

			options.push({
				title: 'Email Address',
				value: 'person_email'
			})

			options.push({
				title: 'Label',
				value: 'person_label'
			})

			options.push({
				title: 'Usergroup',
				value: 'person_usergroup'
			})

			options.push({
				title: 'Language',
				value: 'person_language'
			})

			options.push({
				title: 'Is manager of organization',
				value: 'person_is_manager'
			})

			options.push({
				title: 'Is disabled',
				value: 'person_is_disabled'
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
				title: 'Name',
				value: 'org_name'
			})

			options.push({
				title: 'Label',
				value: 'org_label'
			})

			options.push({
				title: 'Email Domain',
				value: 'org_email_domain'
			})

			options.push({
				title: 'Linked Usergroup',
				value: 'org_usergroup'
			})

			set_options.push({
				title: 'Organization Criteria',
				subOptions: options
			})

			#------------------------------
			# Dates
			#------------------------------

			options = []

			options.push({
				title: 'Day of week',
				value: 'day_of_week'
			})

			options.push({
				title: 'Time of day',
				value: 'time_of_day'
			})

			options.push({
				title: 'Within working hours',
				value: 'within_working_hours'
			})

			set_options.push({
				title: 'Dates',
				subOptions: options
			})

			return set_options

		loadDataOptions: ->
			if @options_data
				p = @$q.fcall( =>
					return @options_data
				)
			else
				@options_data = {}
				p = @Api.sendDataGet({
					'ticket_deps':     '/ticket_deps',
					'ticket_cats':     '/ticket_cats',
					'ticket_prods':    '/ticket_prods',
					'ticket_pris':     '/ticket_pris',
					'ticket_works':    '/ticket_works',
					'ticket_accounts': '/ticket_accounts',
					'usergroups':      '/usergroups',
				}).then( (result) =>
					data = result.data
					@options_data['ticket_deps']      = data.ticket_deps.departments
					@options_data['ticket_cats']      = data.ticket_cats.categories
					@options_data['ticket_pris']      = data.ticket_pris.priorities
					@options_data['ticket_works']     = data.ticket_works.workflows
					@options_data['ticket_prods']     = data.ticket_prods?.products
					@options_data['ticket_accounts']  = data.ticket_accounts.ticket_accounts
					@options_data['usergroups']       = data.usergroups.usergroups
				)

			return p

		getDef: (type, options = {}) ->
			typeName = type.toLowerCase().replace(/_(.)/g, (match, group1) ->
				return group1.toUpperCase()
			)
			typeName = typeName.charAt(0).toUpperCase() + typeName.slice(1)

			options.type = type

			typeFunc = "get#{typeName}"
			if @[typeFunc]?
				return @[typeFunc](options)
			else
				console.error("Bad type with no definition getter: #{typeFunc}")
				me = @
				return {
					getTemplate: ->
						return me.dpTemplateManager.get('OptionBuilder/type-criteria-input.html')
					getData: ->
						return {}
					getDataFormatter: ->
						return {
							getViewValue: (value = {}, data) ->
								return {}
							getValue: (model = {}, data) ->
								return null
						}
				}

		getStandardSelect: (options) ->
			type      = options.type
			prop_name = options.propName
			data_name = options.dataName
			form_type = options.formType || 'select'
			operators = options.operators || ['is', 'not']
			options_formatter = options.optionsFormatter || null

			if not options_formatter
				options_formatter = (options) ->
					opts = []

					for opt in options
						if opt.title
							title = opt.title
						else if opt.name
							title = opt.name
						else
							title = null

						if opt.id
							val = opt.id
						else if opt.value
							val = opt.value
						else
							val = null

						if title != null and val != null
							opts.push({
								title: title,
								value: val
							})

					return opts

			me = @

			return {
				getTemplate: ->
					switch form_type
						when 'input'
							return me.dpTemplateManager.get('OptionBuilder/type-criteria-input.html')
						else
							return me.dpTemplateManager.get('OptionBuilder/type-criteria-select.html')

				getData: ->
					if data_name
						defer = me.$q.defer()
						me.loadDataOptions().then(=>
							defer.resolve({
								operators: operators,
								options: if options_formatter then options_formatter(me.options_data[data_name]) else me.options_data[data_name],
								multiselect: true
							})
						)

						return defer.promise
					else
						return {
							operators: operators
						}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							return {
								value: value[prop_name],
								op: value.op || _.first(data.operators)
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = type
							value.op = model.op
							value.options = {}
							value.options[prop_name] = model.value
							return value
					}
			}

		getStandardIs: (options) ->
			type      = options.type
			prop_name = options.propName

			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-criteria-is.html')

				getData: ->
					return {}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							return {
								value: true,
								op: 'is'
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = type
							value.op = 'is'
							value.options = {}
							value.options[prop_name] = true
							return value
					}
			}

		getStandardInput: (options) ->
			type      = options.type
			prop_name = options.propName
			operators = options.operators || ['is', 'not']

			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-criteria-input.html')

				getData: ->
					return {
						operators: operators
					}

				getDataFormatter: ->
					return {
					getViewValue: (value = {}, data) ->
						return {
							value: value[prop_name],
							op: value.op || _.first(data.operators)
						}
					getValue: (model = {}, data) ->
						value = {}
						value.type = type
						value.op = model.op
						value.options = {}
						value.options[prop_name] = model.value
						return value
					}
			}

		getWorkflow: (options = {}) ->
			options.propName = 'workflow_ids'
			options.dataName = 'ticket_works'
			def = @getStandardSelect(options)
			return def

		getPriority: (options = {}) ->
			options.propName = 'priority_ids'
			options.dataName = 'ticket_pris'
			def = @getStandardSelect(options)
			return def

		getCategory: (options = {}) ->
			options.propName = 'category_ids'
			options.dataName = 'ticket_cats'
			def = @getStandardSelect(options)
			return def

		getDepartment: (options = {}) ->
			options.propName = 'department_ids'
			options.dataName = 'ticket_deps'
			def = @getStandardSelect(options)
			return def

		getProduct: (options = {}) ->
			options.propName = 'product_ids'
			options.dataName = 'ticket_prods'
			def = @getStandardSelect(options)
			return def

		getEmailAccount: (options = {}) ->
			options.propName = 'gateway_ids'
			options.dataName = 'ticket_accounts'
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

		getEmailSubject: (options = {}) ->
			options.propName = 'subject'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getEmailBody: (options = {}) ->
			options.propName = 'body'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getEmailToName: (options = {}) ->
			options.propName = 'to_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getEmailToAddress: (options = {}) ->
			options.propName = 'to_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getEmailFromName: (options = {}) ->
			options.propName = 'from_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getEmailFromAddress: (options = {}) ->
			options.propName = 'from_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCcAddress: (options = {}) ->
			options.propName = 'cc_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getCcName: (options = {}) ->
			options.propName = 'cc_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getEmailHeaderMatch: (options = {}) ->
			options.propName = 'email_header_match'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getTitle: (options = {}) ->
			options.propName = 'title'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getMessage: (options = {}) ->
			options.propName = 'message'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getWithAttach: (options = {}) ->
			options.propName = 'with_attach'
			def = @getStandardIs(options)
			return def

		getWithAttachType: (options = {}) ->
			options.propName = 'attach_type'
			options.operators = ['is', 'not']
			def = @getStandardInput(options)
			return def

		getWithAttachName: (options = {}) ->
			options.propName = 'attach_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getPersonName: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getPersonEmail: (options = {}) ->
			options.propName = 'email'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getPersonLabel: (options = {}) ->
			options.propName = 'labels'
			options.operators = ['contains', 'not_contains']
			def = @getStandardInput(options)
			return def

		getPersonUsergroup: (options = {}) ->
			options.propName = 'usergroup_ids'
			options.dataName = 'usergroups'
			def = @getStandardSelect(options)
			return def

		getPersonLanguage: (options = {}) ->
			options.propName = 'language_ids'
			options.dataName = 'languages'
			def = @getStandardSelect(options)
			return def

		getPersonIsManager: (options = {}) ->
			options.propName = 'is_manager'
			def = @getStandardIs(options)
			return def

		getPersonIsDisabled: (options = {}) ->
			options.propName = 'is_disabled'
			def = @getStandardIs(options)
			return def

		getOrgName: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getOrgLabel: (options = {}) ->
			options.propName = 'labels'
			options.operators = ['contains', 'not_contains']
			def = @getStandardInput(options)
			return def

		getOrgEmailDomain: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getOrgUsergroup: (options = {}) ->
			options.propName  = 'usergroup_ids'
			options.dataName  = 'usergroups'
			options.operators = ['is', 'not']
			def = @getStandardSelect(options)
			return def

		getDayOfWeek: (options = {}) ->
			options.propName = 'day_of_week'
			options.operators = ['is', 'not']
			def = @getStandardInput(options)
			return def

		getTimeOfDay: (options = {}) ->
			options.propName = 'time_of_week'
			options.operators = ['is', 'not']
			def = @getStandardInput(options)
			return def

		getWithinWorkingHours: (options = {}) ->
			options.propName = 'working_hours'
			options.operators = ['is', 'not']
			def = @getStandardInput(options)
			return def