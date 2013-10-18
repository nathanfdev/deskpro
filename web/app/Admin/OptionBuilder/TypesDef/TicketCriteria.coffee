define ->
	class Admin_OptionBuilder_TypesDef_TicketCriteria
		constructor: (@$q, @Api, @dpTemplateManager) ->
			@options_data = null
			@type_to_data = {
				'department_ids': 'departments'
			}

		loadOptions: ->
			if @options_data
				p = @$q.fcall( =>
					return @options_data
				)
			else
				@options_data = {}
				p = @Api.sendDataGet([
					'/ticket_deps'
				]).then( (result) =>
					data = result.data
					@options_data['departments'] = data.api_ticket_deps.departments
				)

			return p

		getDef: (type, options) ->
			typeName = type.toLowerCase().replace(/_(.)/g, (match, group1) ->
				return group1.toUpperCase()
			)
			typeName = typeName.charAt(0).toUpperCase() + typeName.slice(1)

			typeFunc = "get#{typeName}"
			return @[typeFunc](options)

		getStandardSelect: (options) ->
			prop_name = options.propName
			form_type = options.formType || 'select'
			operators = options.operators || ['is', 'not']

			me = @

			return {
				getTemplate: ->
					switch form_type
						when 'input'
							return me.dpTemplateManager.get('OptionBuilder/type-criteria-input.html')
						else
							return me.dpTemplateManager.get('OptionBuilder/type-criteria-select.html')

				getData: ->
					if me.type_to_data[prop_name]
						defer = me.$q.defer()
						me.loadOptions().then(=>
							opt_name = me.type_to_data[prop_name]
							defer.resolve({
								operators: operators,
								options: me.options_data[opt_name]
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
							value[prop_name] = model.value
							value.op = model.op
							return value
					}
			}

		getWorkflow: (options = {}) ->
			options.propName = 'workflow_ids'
			def = @getStandardSelect(options)
			return def

		getPriority: (options = {}) ->
			options.propName = 'priority_ids'
			def = @getStandardSelect(options)
			return def

		getCategory: (options = {}) ->
			options.propName = 'category_ids'
			def = @getStandardSelect(options)
			return def

		getDepartment: (options = {}) ->
			options.propName = 'department_ids'
			def = @getStandardSelect(options)
			return def

		getProduct: (options = {}) ->
			options.propName = 'product_ids'
			def = @getStandardSelect(options)
			return def