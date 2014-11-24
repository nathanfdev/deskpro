define [
	'DeskPRO/Util/Util',
	'DeskPRO/Util/Arrays',
	'Admin/OptionBuilder/TypesDef/BaseTypesDef'
], (
	Util,
	Arrays,
	BaseTypesDef
) ->
	class Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef extends BaseTypesDef
		constructor: (@$q, @Api, @dpTemplateManager) ->
			@options_data        = null
			@inputTemplate       = 'OptionBuilder/type-criteria-input.html'
			@dateTemplate        = 'OptionBuilder/type-criteria-date.html'
			@timeElapsedTemplate = 'OptionBuilder/type-criteria-time-elapsed.html'
			@selectTemplate      = 'OptionBuilder/type-criteria-select.html'
			@isTemplate          = 'OptionBuilder/type-criteria-is.html'
			@remoteTemplate      = 'OptionBuilder/type-criteria-remote.html'
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
			return options.operators || ['is', 'not', 'isset', 'not_isset']

		###
    	# Constructs standard input from a custom field def
		###
		getStandardForFieldDef: (field, options = {}) ->
			if not options.propName then options.propName = 'value'

			if field.type_name == 'choice'
				options.options = field.choices.map( (o) -> {title: o.title, value: o.id})
				return @getStandardSelect(options)
			else if field.type_name == 'toggle'
				options.options = [{title: 'On', value: "1"}, {title: "Off", value: "0"}]
				options.single = true
				return @getStandardSelect(options)
			else
				if not options.operators then options.operators = ['is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex', 'isset', 'not_isset']
				return @getStandardInput(options)

		###
    	# Sets the getter for a custom field
    	#
    	# @param {String} base_name The base name of the field type (e.g., TicketField, UserField etc)
    	# @param {Object} f         The field
    	# @retrn {String} The name of the field that was set
    	###
		initFieldGetter: (base_name, f) ->
			fname = base_name + f.id

			if not this['get'+fname]
				this['get'+fname] = (options = {}) =>
					options.type = base_name + f.id
					return @getStandardForFieldDef(f, options)

			return fname

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
				options_formatter = (options) =>
					return @standardOptionsFormatter(options, extraOptions)

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
							fieldOptions: options,
							operators: operators,
							options: if options_formatter then options_formatter(options.options) else options.options,
							multiselect: !options.single
						}
					else if data_name
						defer = me.$q.defer()
						me.loadDataOptions().then(=>
							defer.resolve({
								operators: operators,
								fieldOptions: options,
								options: if options_formatter then options_formatter(me.options_data[data_name]) else me.options_data[data_name],
								multiselect: !options.single
							})
						)

						return defer.promise
					else
						return {
							operators: operators,
							fieldOptions: options
						}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							val = value.options?[prop_name] || null
							if val == null and data.options and prop_name
								val = data.options[0]?.value || null

							if !options.single and not Util.isArray(val)
								val = [val]

							return {
								value: val,
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
						operators: operators,
						options: options
					}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							val = value.options?[prop_name] || ''
							if Util.isArray(val) then val = val.join(',')

							if value.op
								if value.op == 'is' and operators.indexOf('is') == -1
									value.op = 'contains'
								else if value.op == 'not' and operators.indexOf('not') == -1
									value.op = 'notcontains'

							return {
								value: val,
								op: value.op || _.first(data.operators)
							}
						getValue: (model = {}, data) ->

							val = model.value || ''
							if options.tags
								val = val.split(',')

							value = {}
							value.type = type
							value.op = model.op
							value.options = {}
							value.options[prop_name] = val
							return value
						}
			}

		getTimeElapsedInput: (options) ->
			type      = options.type
			operators = options.operators || ['lte', 'gte']
			prop_name = options.propName
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get(me.timeElapsedTemplate)

				getData: ->
					return {
						operators: operators
					}

				getDataFormatter: ->
					return {
					getViewValue: (value = {}, data) ->
						val = value.options?[prop_name] || [1, 'days']
						return {
							op: value.op || _.first(operators),
							value: val
						}
					getValue: (model = {}, data) ->

						val = model.value || [1, 'days']

						value = {}
						value.type = type
						value.op = model.op
						value.options = {}
						value.options[prop_name] = val
						return value
					}
			}

		getDateInput: (options) ->
			type      = options.type
			operators = options.operators || ['lte', 'gte', 'between']
			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get(me.dateTemplate)

				getData: ->
					return {
						operators: operators,
						options: options
					}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							value.options = value.options || {}

							date1 = null
							date2 = null
							date1_relative = null
							date2_relative = null
							use_relative = false

							if value.options.date1 or value.options.date2 or (not value.options.date1_relative and not value.options.date2_relative)
								use_relative = false

								if value.options.date1
									date1 = new Date(value.options.date1 * 1000)
								if value.options.date2
									date2 = new Date(value.options.date2 * 1000)
							else
								use_relative = true
								if value.options.date1_relative
									date1_relative = [value.options.date1_relative]
									date1_relative[1] = value.options.date1_relative_type || 'days'
								if value.options.date2_relative
									date2_relative = [value.options.date2_relative]
									date2_relative[1] = value.options.date2_relative_type || 'days'

							return {
								op: value.op || _.first(operators),
								use_relative: use_relative,
								date1: date1 || null,
								date2: date2 || null,
								date1_relative: date1_relative || [1, 'days'],
								date2_relative: date2_relative || [1, 'days']
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = type
							value.op = model.op
							value.options = {}

							if not model.use_relative
								if (model.op == 'lte' || model.op == 'between')
									if not model.date1 then model.date1 = new Date()
									value.options.date1 = parseInt(model.date1.getTime() / 1000)
								if (model.op == 'gte' || model.op == 'between') and model.date2
									if not model.date2 then model.date2 = new Date()
									value.options.date2 = parseInt(model.date2.getTime() / 1000)
							else
								if (model.op == 'lte' || model.op == 'between') and model.date1_relative
									d1 = model.date1_relative || [1, 'days']
									value.options.date1_relative = d1[0]
									value.options.date1_relative_type = d1[1]
								if (model.op == 'gte' || model.op == 'between') and model.date2_relative
									d2 = model.date2_relative || [1, 'days']
									value.options.date2_relative = d2[0]
									value.options.date2_relative_type = d2[1]

							return value
					}
			}