define [
	'DeskPRO/Util/Util'
], (
	Util
) ->
	class Admin_TicketEscalations_EscalationEditFormMapper
		getFormFromModel: (escModel) ->
			form = {}
			form.title              = escModel.title || ''
			form.event_trigger      = escModel.event_trigger || 'time.open'
			form.event_trigger_time = escModel.event_trigger_time || 3600
			form.actions            = escModel.actions?.actions || {}

			form.terms = {}
			form.actions = {}

			if escModel.terms?.terms?.length
				for term in escModel.terms.terms
					rowId = _.uniqueId('term')
					form.terms[rowId] = term

			if escModel.actions?.actions?.length
				for action in escModel.actions.actions
					rowId = _.uniqueId('action')
					form.actions[rowId] = action

			return form

		applyFormToModel: (escModel, formModel) ->
			escModel.title = formModel.title

		getPostDataFromForm: (formModel) ->
			postData = {}
			postData.title = formModel.title
			postData.event_trigger = formModel.event_trigger
			postData.event_trigger_time = formModel.event_trigger_time

			postData.actions = []
			for own id, row of formModel.actions
				postData.actions.push(row)

			postData.terms = []
			for own id, row of formModel.terms
				postData.terms.push(row)

			return postData