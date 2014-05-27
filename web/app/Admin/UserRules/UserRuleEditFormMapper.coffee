define [
	'DeskPRO/Util/Util'
], (
	Util
) ->
	class UserRuleEditFormMapper
		getFormFromModel: (model) ->
			form = {}

			form.id = model.user_rule.id
			form.email_patterns = model.user_rule.email_patterns
			form.usergroups = []
			for own id, title of model.all_usergroups
				form.usergroups.push({id: id, title: title})

			if model.user_rule.usergroup
				form.usergroup = {}
				form.usergroup.id = model.user_rule.usergroup.id
				form.usergroup.title = model.user_rule.usergroup.title

			return form

		applyFormToModel: (model, formModel) ->
			model.email_patterns = formModel.email_patterns

		getPostDataFromForm: (formModel) ->
			postData = {}
			postData.id = formModel.id
			postData.email_patterns = formModel.email_patterns
			postData.add_usergroup = formModel.usergroup.id
			return postData