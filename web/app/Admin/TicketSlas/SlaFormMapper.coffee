define ->
	class SlaFormMapper
		getFormFromModel: (model) ->
			form = {}
			form.title         = model.title || ''
			form.sla_type      = model.sla_type || 'first_response'
			form.active_time   = model.active_time || 'all'
			form.sla_warn_time = model.sla_warn_time || 1800
			form.sla_fail_time = model.sla_fail_time || 3600
			form.apply_type    = model.apply_type || 'all'

			form.sla_warn_actions = {}
			form.sla_fail_actions = {}
			form.criteria_sets = {}

			if model.warning_trigger?.actions?.length
				for action in model.warning_trigger.actions
					rowId = _.uniqueId('action')
					form.sla_warn_actions[rowId] = action

			if model.fail_trigger?.actions?.length
				for action in model.fail_trigger.actions
					rowId = _.uniqueId('action')
					form.sla_fail_actions[rowId] = action

			if model.apply_trigger?.terms?.length
				for termSet in model.apply_trigger.terms
					if not termSet.set_terms or not termSet.set_terms.length then continue
					setId = _.uniqueId('termset')
					form.criteria_sets[setId] = {}

					for term in termSet.set_terms
						rowId = _.uniqueId('term')
						form.criteria_sets[setId][rowId] = term

			return form