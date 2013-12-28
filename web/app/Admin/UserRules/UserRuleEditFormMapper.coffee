define [
	'DeskPRO/Util/Util'
], (
	Util
) ->
	class UserRuleEditFormMapper

		###
			#
 		#
		###

		getFormFromModel: (model) ->

			form = {}

			form.id = model.user_rule.id
			form.email_patterns = model.user_rule.email_patterns

			return form

		###
			#
			#
		###

		applyFormToModel: (model, formModel) ->

			model.email_patterns = formModel.email_patterns

		###
			#
			#
		###

		getPostDataFromForm: (formModel) ->

			postData = {}

			postData.id = formModel.id
			postData.email_patterns = formModel.email_patterns

			return postData