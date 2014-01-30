define [
	'DeskPRO/Util/Util'
], (
	Util
) ->
	class ReportEditFormMapper

		###
			#
 		#
		###
		getFormFromModel: (model) ->

			form = {}
			form.id = model.id

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

			return postData