define ->
	class Admin_TicketTriggers_TriggerEditFormMapper
		getFormFromModel: (model) ->
			form = {}
			form.title = model.title || ''

			form.typeForm = {
				by_user: true,
				by_agent: false,
				by_agent_mode: {
					web: true,
					email: true,
					api: true
				},
				by_user_mode: {
					portal: true,
					widget: true,
					form: true,
					email: true,
					api: true
				}
			}

			if model.id
				if model.by_agent_mode.length
					form.typeForm.by_agent = true
					for x in model.by_agent_mode
						form.typeForm.by_agent_mode[x] = true
				if model.by_user_mode.length
					form.typeForm.by_user = true
					for x in model.by_user_mode
						form.typeForm.by_user_mode[x] = true

			form.terms_set = {}
			form.actions = {}

			if model.terms?.terms?.length
				for termSet in model.terms.terms
					if not termSet.set_terms or not termSet.set_terms.length then continue
					setId = _.uniqueId('termset')
					form.terms_set[setId] = {}

					for term in termSet.set_terms
						rowId = _.uniqueId('term')
						form.terms_set[setId][rowId] = term

			if model.actions?.actions?.length
				for action in model.actions.actions
					rowId = _.uniqueId('action')
					form.actions[rowId] = action

			return form