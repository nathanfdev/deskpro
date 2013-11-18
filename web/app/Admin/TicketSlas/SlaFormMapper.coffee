define ->
	class SlaFormMapper
		###
    	# Converts a model we get from the API into a form model that we can use in our page
    	#
    	# @param {Object} model
    	# @return {Object}
		###
		getFormFromModel: (model) ->
			form = {}
			form.title         = model.title || ''
			form.sla_type      = model.sla_type || 'first_response'
			form.active_time   = model.active_time || 'all'
			form.apply_type    = model.apply_type || 'all'
			form.warn_time     = [30, 'minutes']
			form.fail_time     = [60, 'minutes']
			form.warn_actions  = {}
			form.fail_actions  = {}
			form.apply_terms   = {}

			if model.warn_time and model.warn_time_unit
				form.warn_time = [model.warn_time, model.warn_time_unit]

			if model.fail_time and model.fail_time_unit
				form.fail_time = [model.fail_time, model.fail_time_unit]

			if model.warn_actions?.actions?.length
				for action in model.warn_actions.actions
					rowId = _.uniqueId('action')
					form.warn_actions[rowId] = action

			if model.fail_actions?.actions?.length
				for action in model.fail_actions.actions
					rowId = _.uniqueId('action')
					form.fail_actions[rowId] = action

			if model.apply_terms?.terms?.length
				for termSet in model.apply_terms.terms
					if not termSet.set_terms or not termSet.set_terms.length then continue
					setId = _.uniqueId('termset')
					form.apply_terms[setId] = {}

					for term in termSet.set_terms
						rowId = _.uniqueId('term')
						form.apply_terms[setId][rowId] = term

			return form


		###
    	# Converts the form model into a model we can post back to the API
    	# Essentially the reverse of getFormFromModel
    	#
    	# @param {Object} form
    	# @return {Object}
		###
		getPostDataFromFormModel: (form) ->
			postData = {
				title:          form.title,
				sla_type:       form.sla_type,
				active_time:    form.active_time,
				apply_type:     form.apply_type,
				warn_time:      form.warn_time[0],
				warn_time_unit: form.warn_time[1],
				fail_time:      form.fail_time[0],
				fail_time_unit: form.fail_time[1],
				warn_actions:   [],
				fail_actions:   [],
				apply_terms:    []
			}

			if form.apply_type == 'terms'
				for own _, crit_set of form.apply_terms
					set = []
					for own _, crit of crit_set
						if crit.type
							set.push(crit)
					if set.length
						postData.apply_terms.push(set)

			if form.warn_actions
				for own _, act of form.warn_actions
					if act.type
						postData.warn_actions.push(act)

			if form.fail_actions
				for own _, act of form.fail_actions
					if act.type
						postData.fail_actions.push(act)

			return postData