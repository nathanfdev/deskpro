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
				value: 'FilterDepartment'
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
				title: 'Workflow',
				value: 'FilterWorkflow'
			})

			options.push({
				title: 'Subject',
				value: 'FilterSubject'
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
				value: 'FilterPersonIsDisabled'
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
				value: 'FilterOrgName'
			})

			options.push({
				title: 'Label',
				value: 'FilterOrgLabels'
			})

			options.push({
				title: 'Email Domain',
				value: 'FilterOrgEmailDomain'
			})

			options.push({
				title: 'Linked Usergroup',
				value: 'FilterOrgUsergroups'
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

		getFilterWorkflow: (options = {}) ->
			options.propName = 'workflow_ids'
			options.dataName = 'ticket_works'
			def = @getStandardSelect(options)
			return def

		getFilterPriority: (options = {}) ->
			options.propName = 'priority_ids'
			options.dataName = 'ticket_pris'
			def = @getStandardSelect(options)
			return def

		getFilterCategory: (options = {}) ->
			options.propName = 'category_ids'
			options.dataName = 'ticket_cats'
			def = @getStandardSelect(options)
			return def

		getFilterDepartment: (options = {}) ->
			options.propName = 'department_ids'
			options.dataName = 'ticket_deps'
			def = @getStandardSelect(options)
			return def

		getFilterProduct: (options = {}) ->
			options.propName = 'product_ids'
			options.dataName = 'ticket_prods'
			def = @getStandardSelect(options)
			return def

		getFilterEmailAccount: (options = {}) ->
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

		getFilterEmailSubject: (options = {}) ->
			options.propName = 'subject'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterEmailBody: (options = {}) ->
			options.propName = 'body'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterEmailToName: (options = {}) ->
			options.propName = 'to_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterEmailToAddress: (options = {}) ->
			options.propName = 'to_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterEmailFromName: (options = {}) ->
			options.propName = 'from_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterEmailFromAddress: (options = {}) ->
			options.propName = 'from_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterCcAddress: (options = {}) ->
			options.propName = 'cc_address'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterCcName: (options = {}) ->
			options.propName = 'cc_name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterEmailHeader: (options = {}) ->
			options.propName = 'email_header_match'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterSubject: (options = {}) ->
			options.propName = 'subject'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterMessage: (options = {}) ->
			options.propName = 'message'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
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
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterUserName: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterUserEmailAddress: (options = {}) ->
			options.propName = 'email'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterUserLabels: (options = {}) ->
			options.propName = 'labels'
			options.operators = ['contains', 'not_contains']
			def = @getStandardInput(options)
			return def

		getFilterUserUsergroups: (options = {}) ->
			options.propName = 'usergroup_ids'
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

		getFilterPersonIsDisabled: (options = {}) ->
			options.propName = 'is_disabled'
			def = @getStandardIs(options)
			return def

		getFilterOrgName: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterOrgLabels: (options = {}) ->
			options.propName = 'labels'
			options.operators = ['contains', 'not_contains']
			def = @getStandardInput(options)
			return def

		getFilterOrgEmailDomain: (options = {}) ->
			options.propName = 'name'
			options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex']
			def = @getStandardInput(options)
			return def

		getFilterOrgUsergroups: (options = {}) ->
			options.propName  = 'usergroup_ids'
			options.dataName  = 'usergroups'
			options.operators = ['is', 'not']
			def = @getStandardSelect(options)
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