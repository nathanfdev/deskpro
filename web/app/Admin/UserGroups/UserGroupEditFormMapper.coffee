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

			form.permissions = {}

			for permission in model.user_group.permissions
				key = permission.name.replace('.', '_')
				if Util.isBlank(permission.value) then form.permissions[key] = false else form.permissions[key] = true

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
			postData.is_enabled = formModel.is_enabled

			postData.permissions = []

			console.log formModel.permissions

			for own permission, value of formModel.permissions
				postData.permissions.push({
					usergroup_id: formModel.id,
					name: permission.replace('_', '.'),
					value: (if value then 1 else 0),
					person_id: null,
				})

			return postData