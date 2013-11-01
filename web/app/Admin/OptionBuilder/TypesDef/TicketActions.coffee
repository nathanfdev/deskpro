define ->
	class Admin_OptionBuilder_TypesDef_TicketActions
		constructor: (@$q, @Api, @dpTemplateManager) ->
			@options_data = null

		getOptionsForTypes: (types, typesData = null) ->
			set_options = []

			#------------------------------
			# Ticket Assignment
			#------------------------------

			options = []
			options.push({
				title: 'Change Assigned Agent',
				value: 'SetAgent'
			})

			options.push({
				title: 'Change Assigned Team',
				value: 'SetAgentTeam'
			})

			options.push({
				title: 'Change Agent Followers',
				value: 'SetAgentFollowers'
			})

			set_options.push({
				title: 'Ticket Assignment',
				subOptions: options
			})

			#------------------------------
			# Ticket Properties
			#------------------------------

			options = []

			options.push({
				title: 'Change Department',
				value: 'SetDepartment'
			})

			options.push({
				title: 'Change Product',
				value: 'SetProduct'
			})

			options.push({
				title: 'Change Category',
				value: 'SetCategory'
			})

			options.push({
				title: 'Change Priority',
				value: 'SetPriority'
			})

			options.push({
				title: 'Change Workflow',
				value: 'SetWorkflow'
			})

			options.push({
				title: 'Change Urgency',
				value: 'SetUrgency'
			})

			options.push({
				title: 'Change Subject',
				value: 'SetSubject'
			})

			options.push({
				title: 'Change Labels',
				value: 'SetLabels'
			})

			options.push({
				title: 'Change Flag',
				value: 'SetFlag'
			})

			options.push({
				title: 'Change Email Account',
				value: 'SetEmailAccount'
			})

			options.push({
				title: 'Change CC\'d Users',
				value: 'SetCcUsers'
			})

			set_options.push({
				title: 'Ticket Properties',
				subOptions: options
			})

			#------------------------------
			# Ticket SLAs
			#------------------------------

			options = []

			options.push({
				title: 'Change SLAs',
				value: 'SetSlas'
			})

			options.push({
				title: 'Change SLA Condition Status (Passing/Failing)',
				value: 'SetSlaStatus'
			})

			options.push({
				title: 'Change SLA State (Waiting/Finished)',
				value: 'SetSlaRequirements'
			})

			set_options.push({
				title: 'Ticket SLAs',
				subOptions: options
			})

			#------------------------------
			# Ticket Actions
			#------------------------------

			options = []

			options.push({
				title: 'Change Ticket User',
				value: 'ChangeUser'
			})

			options.push({
				title: 'Delete Ticket',
				value: 'DeleteTicket'
			})

			options.push({
				title: 'Add Agent Reply',
				value: 'AddAgentReply'
			})

			options.push({
				title: 'Force User Email Validation',
					value: 'ModForceEmailValidation'
			})

			set_options.push({
				title: 'Ticket Actions',
				subOptions: options
			})

			#------------------------------
			# Ticket Actions
			#------------------------------

			options = []

			options.push({
				title: 'Send Email To User',
				value: 'SendUserEmail'
			})

			options.push({
				title: 'Send Email To Agents',
				value: 'SendAgentEmail'
			})

			#------------------------------
			# Trigger Control
			#------------------------------

			options = []

			options.push({
				title: 'Stop Processing Triggers',
				value: 'ModStopTriggers'
			})

			options.push({
				title: 'Prevent Emails To User',
				value: 'ModQuietUserEmails'
			})

			options.push({
				title: 'Prevent Emails To Agents',
				value: 'ModQuietAgentEmails'
			})

			set_options.push({
				title: 'Trigger Control',
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
					'agents':          '/agents'
					'agent_teams':     '/agent_teams',
					'ticket_deps':     '/ticket_deps',
					'ticket_cats':     '/ticket_cats',
					'ticket_prods':    '/ticket_prods',
					'ticket_pris':     '/ticket_pris',
					'ticket_works':    '/ticket_works',
					'ticket_slas':     '/ticket_slas',
					'ticket_accounts': '/ticket_accounts',
					'usergroups':      '/usergroups',
				}).then( (result) =>
					data = result.data
					@options_data['agents']           = data.agents.agents
					@options_data['agent_teams']      = data.agent_teams.agent_teams
					@options_data['ticket_deps']      = data.ticket_deps.departments
					@options_data['ticket_cats']      = data.ticket_cats.categories
					@options_data['ticket_pris']      = data.ticket_pris.priorities
					@options_data['ticket_works']     = data.ticket_works.workflows
					@options_data['ticket_prods']     = data.ticket_prods?.products
					@options_data['ticket_slas']      = data.ticket_slas?.slas
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
					return me.dpTemplateManager.get('OptionBuilder/type-actions-input.html')
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
			options_formatter = options.optionsFormatter || null
			is_multi  = options.isMulti

			if not options_formatter
				options_formatter = (options) ->
					opts = []

					for opt in options
						if opt.title
							title = opt.title
						else if opt.display_name
							title = opt.display_name
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
					return me.dpTemplateManager.get('OptionBuilder/type-actions-select.html')

				getData: ->
					if data_name
						defer = me.$q.defer()
						me.loadDataOptions().then(=>
							defer.resolve({
								options: if options_formatter then options_formatter(me.options_data[data_name]) else me.options_data[data_name],
								multiselect: is_multi
							})
						)

						return defer.promise
					else
						return {}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							return {
								value: value[prop_name]
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = type
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
					return me.dpTemplateManager.get('OptionBuilder/type-actions-is.html')

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
						value.options = {}
						value.options[prop_name] = true
						return value
					}
			}

		getStandardInput: (options) ->
			type      = options.type
			prop_name = options.propName

			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-input.html')

				getData: ->
					return {

					}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							return {
								value: value[prop_name]
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = type
							value.options = {}
							value.options[prop_name] = model.value
							return value
					}
			}

		getAgent: (options = {}) ->
			options.propName = 'agent_id'
			options.dataName = 'agents'
			def = @getStandardSelect(options)
			return def

		getAgentFollowers: (options = {}) ->
			options.propName = 'agent_ids'
			options.dataName = 'agents'
			options.isMulti = true
			def = @getStandardSelect(options)
			return def

		getAgentTeam: (options = {}) ->
			options.propName = 'agent_team_id'
			options.dataName = 'agent_teams'
			def = @getStandardSelect(options)
			return def

		getWorkflow: (options = {}) ->
			options.propName = 'workflow_ids'
			options.dataName = 'ticket_works'
			def = @getStandardSelect(options)
			return def

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
			def = @getStandardInput(options)
			return def

		getUrgency: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-urgency.html')

				getData: ->
					return {

					}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							return {
								value: value.urgency,
								op: value.op || 'add',
								only_if_lower: !!value.only_if_lower
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'urgency'
							value.options = {}
							value.options.urgency = model.value
							value.options.op = model.op
							value.options.only_if_lower = !!model.only_if_lower
							return value
						}
			}

		getFlag: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-select.html')

				getData: ->
					return {
						options: [
							{title: 'Red', 'red'},
							{title: 'Blue', 'blue'},
							{title: 'Green', 'green'},
							{title: 'Orange', 'orange'},
							{title: 'Purple', 'purple'},
							{title: 'Pink', 'Pink'}
						]
					}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							return {
								value: value.color
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'flag'
							value.options = {}
							value.options.color = model.value
							return value
					}
			}

		getDeleteTicket: (options = {}) ->
			options.propName = 'delete_ticket'
			def = @getStandardIs(options)
			return def

		getForceEmailValidation: (options = {}) ->
			options.propName = 'force_email_validation'
			def = @getStandardIs(options)
			return def

		getStopTriggers: (options = {}) ->
			options.propName = 'stop_triggers'
			def = @getStandardIs(options)
			return def

		getQuietUserEmails: (options = {}) ->
			def = @getStandardIs(options)
			return def

		getQuietAgentEmails: (options = {}) ->
			def = @getStandardIs(options)
			return def

		getSlas: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-slas.html')

				getData: ->
					defer = me.$q.defer()
					me.loadDataOptions().then(=>
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
							return {
								add_slas: value.add_slas || [],
								remove_slas: value.remove_slas || []
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'slas'
							value.options = {}
							value.options.add_slas    = model.add_slas
							value.options.remove_slas = model.remove_slas
							return value
					}
			}