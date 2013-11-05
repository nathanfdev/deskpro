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
				title: 'Department',
				value: 'CheckDepartment'
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
				title: 'Workflow',
				value: 'CheckWorkflow'
			})

			options.push({
				title: 'Subject',
				value: 'CheckSubject'
			})

			set_options.push({
				title: 'Ticket Criteria',
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
				title: 'Is disabled',
				value: 'CheckPersonIsDisabled'
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

		getCheckWorkflow: (options = {}) ->
			options.propName = 'workflow_ids'
			options.dataName = 'ticket_works'
			def = @getStandardSelect(options)
			return def

		getCheckPriority: (options = {}) ->
			options.propName = 'priority_ids'
			options.dataName = 'ticket_pris'
			def = @getStandardSelect(options)
			return def

		getCheckCategory: (options = {}) ->
			options.propName = 'category_ids'
			options.dataName = 'ticket_cats'
			def = @getStandardSelect(options)
			return def

		getCheckDepartment: (options = {}) ->
			options.propName = 'department_ids'
			options.dataName = 'ticket_deps'
			def = @getStandardSelect(options)
			return def

		getCheckProduct: (options = {}) ->
			options.propName = 'product_ids'
			options.dataName = 'ticket_prods'
			def = @getStandardSelect(options)
			return def

		getCheckEmailAccount: (options = {}) ->
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

		getCheckMessage: (options = {}) ->
			options.propName = 'message'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
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
			def = @getStandardSelect(options)
			return def

		getCheckUserLanguage: (options = {}) ->
			options.propName = 'language_ids'
			options.dataName = 'languages'
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