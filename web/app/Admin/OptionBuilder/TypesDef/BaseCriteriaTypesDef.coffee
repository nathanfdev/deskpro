define ->
	class Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef
		constructor: (@$q, @Api, @dpTemplateManager) ->
			@options_data   = null
			@inputTemplate  = 'OptionBuilder/type-criteria-input.html'
			@selectTemplate = 'OptionBuilder/type-criteria-select.html'
			@isTemplate     = 'OptionBuilder/type-criteria-is.html'
			@init()

		init: ->
			return

		###
    	# Gets a type definition by calling a getX method on this class
		###
		getDef: (type, options = {}) ->
			typeName = type
			options.type = type

			typeFunc = "get#{typeName}"
			if @[typeFunc]?
				return @[typeFunc](options)
			else
				console.error("Bad type with no definition getter: #{typeFunc}")
				me = @
				return {
					getTemplate: ->
						return me.dpTemplateManager.get(me.inputTemplate)
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

		###
    	# @param {Object} options
    	# @return {Array}
		###
		getOperators: (options) ->
			return options.operators || ['is', 'not']

		###
    	# Constructs standard input from a custom field def
		###
		getStandardForFieldDef: (field, options = {}) ->

			if not options.prop_name then options.prop_name = 'value'

			if field.type_name == 'choice'
				options.options = field.options
				return @getStandardSelect(options)
			else
				return @getStandardInput(options)

		###
    	# Constructs a standard select box type
		###
		getStandardSelect: (options) ->
			type      = options.type
			prop_name = options.propName
			data_name = options.dataName
			form_type = options.formType || 'select'
			operators = @getOperators(options)
			options_formatter = options.optionsFormatter || null
			extraOptions = options.extraOptions || null

			if not options_formatter
				options_formatter = (options) ->
					opts = []

					if extraOptions
						for opt in extraOptions
							opts.push(opt)

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
					if options.template
						return me.dpTemplateManager.get(options.template)
					switch form_type
						when 'input'
							return me.dpTemplateManager.get(me.inputTemplate)
						else
							return me.dpTemplateManager.get(me.selectTemplate)

				getData: ->
					if options.options
						return {
							operators: operators,
							options: if options_formatter then options_formatter(options.options) else options.options,
							multiselect: true
						}
					else if data_name
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
								value: value.options?[prop_name] || null,
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


		###
    	# Constructs a standard "is" template (no options, just a boolean is)
		###
		getStandardIs: (options) ->
			type      = options.type
			prop_name = options.propName

			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get(me.isTemplate)

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


		###
    	# Constructs a standard input box
		###
		getStandardInput: (options) ->
			type      = options.type
			prop_name = options.propName
			operators = @getOperators(options)

			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get(me.inputTemplate)

				getData: ->
					return {
						operators: operators
					}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							return {
								value: value.options?[prop_name] || '',
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