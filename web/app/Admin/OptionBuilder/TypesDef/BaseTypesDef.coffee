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