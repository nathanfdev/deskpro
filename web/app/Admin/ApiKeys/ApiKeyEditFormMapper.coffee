define [
	'DeskPRO/Util/Util'
], (
	Util
) ->
	class ApiKeyEditFormMapper

		###
			#
 		#
		###

		getFormFromModel: (model) ->

			form = {}

			form.id = model.api_key.id

			form.user = {}
			form.agents = model.all_agents

			form.selected_agents = {}

			ids = _.pluck(form.user.agents, 'id')

			for id in ids
				form.selected_agents[id] = true

			return form

		###
			#
			#
		###

		applyFormToModel: (model, formModel) ->

			model.id = formModel.id

		###
			#
			#
		###

		getPostDataFromForm: (formModel) ->

			postData = formModel

			return postData