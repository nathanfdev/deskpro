define ['DeskPRO/Util/Util', 'DeskPRO/Util/Arrays'], (Util, Arrays) ->
	class Admin_OptionBuilder_TypesDef_BaseTypesDef
		standardOptionsFormatter: (options, extraOptions) ->
			getRenderOpt = (opt, parentTitleSegs = []) ->
				pTitle = parentTitleSegs.join(" > ")

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

				if pTitle.length
					title = pTitle + " > " + title

				if title != null and val != null
					return {
					title: title,
					value: val
					}
				else
					return null

			addTree = (options, parent_id, toOpts, parentTitleSegs = []) ->
				parent_id = parseInt(parent_id)
				if options
					for opt in options
						if (parent_id != 0 and parseInt(opt.parent_id) == parent_id) or (parent_id == 0 and (not opt.parent_id or not parseInt(opt.parent_id)))
							o = getRenderOpt(opt, parentTitleSegs)

							if o then parentTitleSegs.push(o.title)

							childOps = []
							addTree(options, opt.id, childOps, parentTitleSegs)

							if o then parentTitleSegs.pop()

							if childOps.length
								Arrays.append(toOpts, childOps)
							else
								if o then toOpts.push(o)

			opts = []

			if extraOptions
				for opt in extraOptions
					opts.push(opt)

			addTree(options, 0, opts, [])

			return opts

		getVars: -> @vars || {}
		setVar: (k, v) ->
			if not @vars then @vars = {}
			@vars[k] = v

		###
		# Constructs autocomplete with remote data
		###
		getRemoteInput: (options) ->
			type      = options.type
			prop_name = options.propName
			operators = @getOperators(options)

			me = @
			{
				getTemplate: -> return me.dpTemplateManager.get(me.remoteTemplate)

				getData: -> { operators: operators, options: options }

				getDataFormatter: ->
					{
						getViewValue: (value = {}, data) ->

							if value.op
								if value.op == 'is' and operators.indexOf('is') == -1
									value.op = 'contains'
								else if value.op == 'not' and operators.indexOf('not') == -1
									value.op = 'notcontains'

							inputOptions =
								dropdownAutoWidth: true
								minimumInputLength: 1
								initSelection: (item) -> item[prop_name]
								ajax:
									data: (term, page) -> { query: term }
									quietMillis: 200
									transport: (query) -> me.Api.sendGet(options.url, query.data).then query.success
									results: (data, page) -> { results: data.data }

							$.extend true, inputOptions, options.inputOptions || {}

							if !value.options?[prop_name]
								info = null
							else
								info = value.options?.info || {}
								if !info[prop_name] then info[prop_name] = value.options[prop_name]

							return {
								value: info
								op: value.op || _.first(data.operators)
								inputOptions: inputOptions
							}

						getValue: (model = {}, data) ->
							value = {}
							value.type = type
							value.op = model.op
							value.options = {}
							value.options[prop_name] = model.value?[prop_name] || ''
							value.options.info = model.value
							return value
					}
			}