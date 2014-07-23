define ['DeskPRO/Util/Util'], (Util) ->
	class Admin_OptionBuilder_TypesDef_BaseActionTypesDef
		constructor: (@$q, @Api, @dpTemplateManager) ->
			@options_data   = null
			@inputTemplate  = 'OptionBuilder/type-actions-input.html'
			@selectTemplate = 'OptionBuilder/type-actions-select.html'
			@isTemplate     = 'OptionBuilder/type-actions-is.html'
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
    	# Constructs a standard select box type
		###
		getStandardSelect: (options) ->
			type      = options.type
			prop_name = options.propName
			data_name = options.dataName
			options_formatter = options.optionsFormatter || null
			is_multi  = options.isMulti
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
					return me.dpTemplateManager.get(options.template || me.selectTemplate)

				getData: ->
					if options.options
						return {
							options: if options_formatter then options_formatter(options.options) else options.options,
							multiselect: is_multi
						}
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
							val = value.options?[prop_name] || null
							if val == null and data.options and prop_name
								val = data.options[0]?.value || null

							return {
								value: val,
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = type
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
			icon      = options.icon

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
								op: 'is',
								icon: icon || false
							}
						getValue: (model = {}, data) ->
							value = {}
							value.type = type
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

			me = @
			return {
				getTemplate: ->
					return me.dpTemplateManager.get(me.inputTemplate)

				getData: ->
					return {
						options: options
					}

				getDataFormatter: ->
					return {
						getViewValue: (value = {}, data) ->
							val = value.options?[prop_name] || ''
							if Util.isArray(val) then val = val.join(',')
							return {
								value: val
								op: value.op || _.first(data.operators)
							}
						getValue: (model = {}, data) ->

							val = model.value || ''
							if options.tags
								val = val.split(',')

							value = {}
							value.type = type
							value.options = {}
							value.options[prop_name] = val
							return value
					}
			}


		###
    	# Constructs standard input from a custom field def
		###
		getStandardForFieldDef: (field, options = {}) ->
			if not options.propName then options.propName = 'value'

			if field.type_name == 'choice'
				options.options = field.choices.map( (o) -> {title: o.title, value: o.id + ""})
				return @getStandardSelect(options)
			else if field.type_name == 'toggle'
				options.options = [{title: 'On', value: "1"}, {title: "Off", value: "0"}]
				return @getStandardSelect(options)
			else
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