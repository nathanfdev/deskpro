define [
	'Admin/OptionBuilder/TypesDef/BaseActionTypesDef',
], (
	BaseActionTypesDef
) ->
	class Admin_OptionBuilder_TypesDef_TicketFilter extends BaseActionTypesDef
		init: ->
			@options_data = null

		getOptionsForTypes: (types = [], typesData = null) ->
			set_options = []

			#------------------------------
			# Ticket Assignment
			#------------------------------

			options = []
			options.push({
				title: 'Set Assigned Agent',
				value: 'SetAgent'
			})

			options.push({
				title: 'Set Assigned Team',
				value: 'SetAgentTeam'
			})

			options.push({
				title: 'Set Agent Followers',
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
				title: 'Set Status',
				value: 'SetStatus'
			})

			options.push({
				title: 'Set Department',
				value: 'SetDepartment'
			})

			options.push({
				title: 'Set Product',
				value: 'SetProduct'
			})

			options.push({
				title: 'Set Category',
				value: 'SetCategory'
			})

			options.push({
				title: 'Set Priority',
				value: 'SetPriority'
			})

			options.push({
				title: 'Set Workflow',
				value: 'SetWorkflow'
			})

			options.push({
				title: 'Set Language',
				value: 'SetLanguage'
			})

			options.push({
				title: 'Set Urgency',
				value: 'SetUrgency'
			})

			options.push({
				title: 'Set Subject',
				value: 'SetSubject'
			})

			options.push({
				title: 'Set Labels',
				value: 'SetLabels'
			})

			options.push({
				title: 'Set Flag',
				value: 'SetFlag'
			})

			options.push({
				title: 'Set Email Account',
				value: 'SetEmailAccount'
			})

			options.push({
				title: 'Set CC\'d Users',
				value: 'SetCcs'
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
				title: 'Set SLAs',
				value: 'SetSlas'
			})

			options.push({
				title: 'Complete SLAs',
				value: 'SetSlasComplete'
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
				title: 'Set Ticket User',
				value: 'ChangeUser'
			})

			options.push({
				title: 'Delete Ticket',
				value: 'SetDeleted'
			})

			options.push({
				title: 'Add Agent Reply',
				value: 'AddAgentReply'
			})

			options.push({
				title: 'Require User Email Validation',
				value: 'SetRequireValidation'
			})

			options.push({
				title: 'Set Hold',
				value: 'SetHold'
			})

			options.push({
				title: 'Call Web Hook',
				value: 'WebHook'
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

			set_options.push({
				title: 'Send Email',
				subOptions: options
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

			options.push({
				title: 'Set Trigger Variable',
				value: 'ModSetUserVar'
			})

			set_options.push({
				title: 'Trigger Control',
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
						value: @initFieldGetter('SetTicketField', f)
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
						value: @initFieldGetter('SetUserField', f)
					})

				if options.length
					set_options.push({
						title: 'Person Fields',
						subOptions: options
					})

			#------------------------------
			# Dynamic Options
			#------------------------------

			if typesData?.dynamicOptions?

				options = []

				for opt in typesData.dynamicOptions
					options.push({
						title: opt.action_title,
						value: opt.action_name
					})

					typeFunc = "get#{opt.action_name}"
					@[typeFunc] = (options = {}) ->
						me = @
						return {
							getTemplate: ->
								return me.dpTemplateManager.get(opt.builder_template)
							getData: ->
								return {}
							getDataFormatter: ->
								return {
									getViewValue: (value = {}, data) ->
										return value.options || {}
									getValue: (model = {}, data) ->
										value = {}
										value.type = opt.action_name
										value.options = model || {}
										return value
								}
						}

				if options.length
					set_options.push({
						title: 'Other Actions',
						subOptions: options
					})

			return set_options

		loadDataOptions: ->
			if @options_data
				defer = @.$q.defer()
				defer.resolve(@options_data)
				return defer.promise
			else
				if not @loadDataPromise
					@loadDataPromise = @Api.sendDataGet({
						'agents':          '/agents'
						'agent_teams':     '/agent_teams',
						'ticket_deps':     '/ticket_deps',
						'ticket_cats':     '/ticket_cats',
						'ticket_prods':    '/ticket_prods',
						'ticket_pris':     '/ticket_pris',
						'ticket_works':    '/ticket_works',
						'ticket_fields':   '/ticket_fields',
						'user_fields':     '/user_fields',
						'org_fields':      '/org_fields',
						'ticket_slas':     '/ticket_slas',
						'email_accounts':  '/email_accounts',
						'usergroups':      '/user_groups',
						'langs':           '/langs',
						'email_tpls':      '/email-templates-info'
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
						options_data['ticket_slas']      = data.ticket_slas?.slas
						options_data['email_accounts']   = data.email_accounts.email_accounts
						options_data['usergroups']       = data.usergroups.groups
						options_data['langs']            = data.langs?.languages
						options_data['custom_email_tpls']= data.email_tpls.list['custom'].groups['custom'].templates

						@options_data = options_data

						if @options_data?.ticket_fields
							for f in @options_data.ticket_fields
								@initFieldGetter('SetTicketField', f)
						if @options_data?.user_fields
							for f in @options_data.user_fields
								@initFieldGetter('SetUserField', f)
					)

				return @loadDataPromise

		getSetAgent: (options = {}) ->
			options.propName = 'agent_id'
			options.dataName = 'agents'
			options.extraOptions = [
				{title: 'Unassign', value: 0},
				{title: 'Current Agent', value: -1}
			]
			def = @getStandardSelect(options)
			return def

		getSetAgentFollowers: (options = {}) ->
			options.propName = 'add_agent_ids'
			options.dataName = 'agents'
			options.isMulti = true
			def = @getStandardSelect(options)
			return def

		getSetAgentTeam: (options = {}) ->
			options.propName = 'agent_team_id'
			options.dataName = 'agent_teams'
			options.extraOptions = [
				{title: 'No Team', value: 0},
				{title: 'Current Agent\'s Team', value: -1}
			]
			def = @getStandardSelect(options)
			return def

		getSetWorkflow: (options = {}) ->
			options.propName = 'workflow_id'
			options.dataName = 'ticket_works'
			options.extraOptions = [
				{title: 'None', value: 0}
			]
			def = @getStandardSelect(options)
			return def

		getSetLanguage: (options = {}) ->
			options.propName = 'language_id'
			options.dataName = 'langs'
			def = @getStandardSelect(options)
			return def

		getSetHold: (options = {}) ->
			options.propName = 'is_hold'
			options.options = [
				{title: "Put ticket on hold", value: "1"},
				{title: "Take ticket off hold", value: "0"}
			]
			def = @getStandardSelect(options)
			return def

		getSetPriority: (options = {}) ->
			options.propName = 'priority_id'
			options.dataName = 'ticket_pris'
			options.extraOptions = [
				{title: 'None', value: 0}
			]
			def = @getStandardSelect(options)
			return def

		getSetCategory: (options = {}) ->
			options.propName = 'category_id'
			options.dataName = 'ticket_cats'
			options.extraOptions = [
				{title: 'None', value: 0}
			]
			def = @getStandardSelect(options)
			return def

		getSetLabels: (options = {}) ->
			me = @
			return {
			getTemplate: -> return me.dpTemplateManager.get('OptionBuilder/type-actions-set-labels.html')
			getData: -> return {}
			getDataFormatter: ->
				return {
					getViewValue: (value = {}, data) ->
						options = value?.options || {}
						return {
							add_labels:       (options.add_labels || []).join(', '),
							remove_labels:    (options.remove_labels || []).join(', '),
						}
					getValue: (model = {}, data) ->
						value = {}
						value.type = 'SetLabels'
						value.options = {}
						value.options.add_labels = (model.add_labels || '').split(',')
						value.options.remove_labels = (model.remove_labels || '').split(',')
						return value
				}
			}

		getSetSubject: (options = {}) ->
			me = @
			return {
				getTemplate: -> return me.dpTemplateManager.get('OptionBuilder/type-actions-set-subject.html')
				getData: -> return {}
				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							options = value?.options || {}
							return {
								subject: options.subject || '',
								with_formatter: options.with_formatter || false
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'SetSubject'
							value.options = {}
							value.options.subject = model.subject || ''
							value.options.with_formatter = !!model.with_formatter
							return value
					}
			}

		getSetStatus: (options = {}) ->
			options.propName = 'status'
			options.template = 'OptionBuilder/type-actions-status.html'
			def = @getStandardSelect(options)
			return def

		getSetDepartment: (options = {}) ->
			options.propName = 'department_id'
			options.dataName = 'ticket_deps'
			def = @getStandardSelect(options)
			return def

		getSetProduct: (options = {}) ->
			options.propName = 'product_id'
			options.dataName = 'ticket_prods'
			options.extraOptions = [
				{title: 'None', value: 0}
			]
			def = @getStandardSelect(options)
			return def

		getSetEmailAccount: (options = {}) ->
			options.propName = 'email_account_id'
			options.dataName = 'email_accounts'
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

		getSetEmailSubject: (options = {}) ->
			options.propName = 'subject'
			def = @getStandardInput(options)
			return def

		getSetUrgency: (options = {}) ->
			me = @
			return {
				getTemplate: -> return me.dpTemplateManager.get('OptionBuilder/type-actions-urgency.html')
				getData: -> return {}
				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							options = value?.options || {}

							mode = options.mode || 'add'
							only_if_lower = false
							if mode == 'raise'
								mode = 'set'
								only_if_lower = true

							return {
								value: options.urgency,
								op: mode,
								only_if_lower: only_if_lower
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'SetUrgency'
							value.options = {}
							value.options.urgency = model.value

							if model.op == 'set' and model.only_if_lower
								value.options.mode = 'raise'
							else
								value.options.mode = model.op
							return value
						}
			}

		getSetCcs: (options = {}) ->
			me = @
			return {
				getTemplate: -> return me.dpTemplateManager.get('OptionBuilder/type-actions-set-ccs.html')
				getData: -> return {}
				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							options = value?.options || {}
							return {
								add_emails:       (options.add_emails || []).join(', '),
								remove_emails:    (options.remove_emails || []).join(', '),
								add_org_managers: options.add_org_managers || false
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'SetCcs'
							value.options = {}
							value.options.add_emails = (model.add_emails || '').split(',')
							value.options.remove_emails = (model.remove_emails || '').split(',')
							value.options.add_org_managers = model.add_org_managers || false
							return value
					}
			}

		getSetFlag: (options = {}) ->
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
							value.type = 'SetFlag'
							value.options = {}
							value.options.color = model.value || 'red'
							return value
					}
			}

		getSetDeleted: (options = {}) ->
			def = @getStandardIs(options)
			return def

		getSetRequireValidation: (options = {}) ->
			options.propName = 'require_validation'
			def = @getStandardIs(options)
			return def

		getModStopTriggers: (options = {}) ->
			options.propName = 'stop_triggers'
			def = @getStandardIs(options)
			return def

		getModSetUserVar: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-var.html')

				getData: ->
					return {}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							options = value?.options || {}
							return {
								name: options.name || '',
								value: options.value || ''
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'ModSetUserVar'
							value.options = {
								name: model.name || '',
								value: model.value || ''
							}
							return value
					}
			}

		getModQuietUserEmails: (options = {}) ->
			def = @getStandardIs(options)
			return def

		getModQuietAgentEmails: (options = {}) ->
			def = @getStandardIs(options)
			return def

		getSendUserEmail: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-senduseremail.html')

				getData: ->
					return me.loadDataOptions()

				scopeInit: [ '$scope', '$modal', '$timeout', ($scope, $modal, $timeout) ->
					$scope.handleTemplateChange = ->
						if $scope.model.template == 'CREATE'
							$scope.model.template = null
							$scope.is_creating = true
							$modal.open({
								templateUrl: DP_BASE_ADMIN_URL+'/load-view/Templates/modal-email-editor.html',
								controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
								resolve: {
									templateName: ->
										return null
								}
							}).result.then( (info) =>
								if info.templateName
									title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html')
									tpl = {
										name: info.templateName,
										title: title
									}

									if me.options_data?.custom_email_tpls? and me.options_data.custom_email_tpls.indexOf(tpl) == -1
										me.options_data.custom_email_tpls.push(tpl)

									$scope.model.template = info.templateName
									$timeout(->
										$scope.model.template = info.templateName
										$scope.is_creating = false
									, 100)
								else
									$scope.is_creating = false
							, ->
								$scope.is_creating = false
							)
				]

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							options = value?.options || {}

							from_name = options.from_name || 'helpdesk_name'
							from_name_custom = null
							if from_name not in ['performer', 'helpdesk_name', 'site_name']
								from_name = 'custom'
								from_name_custom = options.from_name

							return {
								template: options.template || '',
								do_cc_users: !!options.do_cc_users,
								from_name: from_name,
								from_name_custom: from_name_custom,
								from_account: (parseInt(options.from_account || 0) || 0)+''
							}
						getValue: (model = {}, data) ->
							options = {
								template: model.template || '',
								do_cc_users: !!model.do_cc_users,
								from_name: '',
								from_account: parseInt(model.from_account || 0)
							}

							if model.from_name == 'custom'
								options.from_name = model.from_name_custom || ''
							else
								options.from_name = model.from_name || ''

							value = {}
							value.type = 'SendUserEmail'
							value.options = options
							return value
					}
			}

		getSendAgentEmail: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-sendagentemail.html')

				getData: ->
					return me.loadDataOptions()

				scopeInit: [ '$scope', '$modal', '$timeout', ($scope, $modal, $timeout) ->
					$scope.handleTemplateChange = ->
						if $scope.model.template == 'CREATE'
							$scope.model.template = null
							$scope.is_creating = true
							$modal.open({
								templateUrl: DP_BASE_ADMIN_URL+'/load-view/Templates/modal-email-editor.html',
								controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
								resolve: {
									templateName: ->
										return null
								}
							}).result.then( (info) =>
								if info.templateName
									title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html')
									tpl = {
										name: info.templateName,
										title: title
									}

									if me.options_data?.custom_email_tpls? and me.options_data.custom_email_tpls.indexOf(tpl) == -1
										me.options_data.custom_email_tpls.push(tpl)

									$scope.model.template = info.templateName
									$timeout(->
										$scope.model.template = info.templateName
										$scope.is_creating = false
									, 100)
								else
									$scope.is_creating = false
							, ->
								$scope.is_creating = false
							)
				]

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							options = value?.options || {}

							from_name = options.from_name || 'helpdesk_name'
							from_name_custom = null
							if from_name not in ['performer', 'helpdesk_name', 'site_name']
								from_name = 'custom'
								from_name_custom = options.from_name

							agent_ids = {}
							if options.agent_ids
								for aid in options.agent_ids
									if aid != 'notify_list' then aid = parseInt(aid)
									agent_ids[aid] = true
							else
								agent_ids['notify_list'] = true

							return {
								template: options.template || '',
								agent_ids: agent_ids,
								from_name: from_name,
								from_name_custom: from_name_custom,
								from_account: (parseInt(options.from_account || 0) || 0)+''
							}
						getValue: (model = {}, data) ->
							options = {
								template: model.template || '',
								agent_ids: [],
								from_name: '',
								from_account: parseInt(model.from_account || 0)
							}

							if model.from_name == 'custom'
								options.from_name = model.from_name_custom || ''
							else
								options.from_name = model.from_name || ''

							if model.agent_ids
								for own k, v of model.agent_ids
									if v
										if k == 'notify_list'
											options.agent_ids.push('notify_list')
										else
											options.agent_ids.push(parseInt(k))

							value = {}
							value.type = 'SendAgentEmail'
							value.options = options
							return value
					}
			}

		getWebHook: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-webhook.html')

				getData: ->
					return {}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							return value.options || {}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'WebHook'
							value.options = model
							return value
						}
			}

		getSetSlas: (options = {}) ->
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
							options = value.options || {}
							return {
								add_slas: options.add_sla_ids || [],
								remove_slas: options.remove_sla_ids || []
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = 'SetSlas'
							value.options = {}
							value.options.add_sla_ids    = model.add_slas
							value.options.remove_sla_ids = model.remove_slas
							return value
					}
			}

		getAddAgentReply: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-addagentreply.html')

				getData: ->
					return me.loadDataOptions()

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							opt = value.options || {}

							by_agent_id = opt.by_agent_id || null
							if not by_agent_id
								by_agent_id = data.agents[0].id

							by_agent_id = by_agent_id + ""

							return {
								reply_text: opt.reply_text || '',
								by_assigned_agent: opt.by_assigned_agent || false,
								by_agent_id: by_agent_id
							}

						getValue: (model = {}, data) ->
							value = {}
							value.type = 'AddAgentReply'
							value.options = {}
							value.options.reply_text = model.reply_text
							value.options.by_assigned_agent = model.by_assigned_agent || false
							value.options.by_agent_id = parseInt(model.by_agent_id || 0) || 0
							return value
					}
			}

		getSetSlasComplete: (options = {}) ->
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get('OptionBuilder/type-actions-setslascomplete.html')

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
							options = value.options || {}

							return {
								sla_ids: options.sla_ids || [],
								sla_status: options.sla_status || 'ok'
							}

						getValue: (model = {}, data) ->
							value = {}
							value.type = 'SetSlasComplete'
							value.options = {
								sla_ids: model.sla_ids,
								sla_status: model.sla_status || 'ok'
							}
							return value
					}
			}