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

			form.isSuperUser = true

			form.id = model.api_key.id
			form.note = model.api_key.note
			form.code = model.api_key.code
			form.keyString = model.api_key.keyString

			if model.api_key.user
				form.isSuperUser = false
				form.user = {}
				form.user.id = model.api_key.user.id
				form.user.name = model.api_key.user.name

			form.agents = model.all_agents

			return form

		###
			#
			#
		###

		applyFormToModel: (model, formModel) ->

			model.note = formModel.note

		###
			#
			#
		###

		getPostDataFromForm: (formModel) ->

			postData = {}

			postData.id = formModel.id
			postData.note = formModel.note

			if !formModel.isSuperUser
				postData.person = formModel.user.id


			return postData