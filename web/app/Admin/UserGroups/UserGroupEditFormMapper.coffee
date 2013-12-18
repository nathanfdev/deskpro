define [
	'DeskPRO/Util/Util'
], (
	Util
) ->
	class UserGroupEditFormMapper

		###
			#
 		#
		###

		getFormFromModel: (model) ->

			form = {}

			form.id = model.user_group.id
			form.title = model.user_group.title
			form.note = model.user_group.note
			form.is_enabled = model.user_group.is_enabled

			form.permissions = []

			return form

		###
			#
			#
		###

		applyFormToModel: (model, formModel) ->

			model.title = formModel.title

		###
			#
			#
		###

		getPostDataFromForm: (formModel) ->

			postData = {}

			postData.id = formModel.id
			postData.title = formModel.title
			postData.note = formModel.note

			postData.permissions = []

			return postData