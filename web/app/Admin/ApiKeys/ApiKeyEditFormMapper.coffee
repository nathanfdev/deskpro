define [
	'angular'
], (
	angular
) ->
	class ApiKeyEditFormMapper

		###
		#
 		#
		###
		getFormFromModel: (model) ->
			form = angular.copy model.api_key
			if !form.flags? then form.flags = ['super']
			if !form.all_agents? then form.all_agents = model.all_agents
			form.isSuperUser = form.flags.length
			form



		###
		#
		#
		###
		applyFormToModel: (model, formModel) ->
			delete formModel.person
			angular.extend model, formModel



		###
		#
		#
		###
		getPostDataFromForm: (formModel) ->

			postData = {}

			postData.id = formModel.id
			postData.note = formModel.note
			postData.flags = []

			if formModel.person
				postData.person = formModel.person.id
			if formModel.isSuperUser
				postData.flags.push 'super'

			postData