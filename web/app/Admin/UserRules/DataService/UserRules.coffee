define [
	'Admin/Main/DataService/BaseListEdit',
	'Admin/UserRules/UserRuleEditFormMapper'
], (
	BaseListEdit,
	UserRuleEditFormMapper
)  ->
	class UserRules extends BaseListEdit
		@$inject = ['Api', '$q']

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/user_rules').success( (data) =>
				models = data.user_rules
				deferred.resolve(models)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise

		###
		# Remove a model
		#
		# @param {Integer} id
		# @return {promise}
		###
		deleteUserRuleById: (id) ->
			promise = @Api.sendDelete('/user_rules/' + id).success( =>
				@removeListModelById(id)
			)

			return promise

		###
		# Get the form mapper
		#
		# @return {UserRuleEditFormMapper}
		###
		getFormMapper: ->

			if @formMapper then return @formMapper
			@formMapper = new UserRuleEditFormMapper()
			return @formMapper

		###
		# Get all data needed for the edit page
		#
		# @param {Integer} id
		# @return {promise}
		###
		loadEditUserRuleData: (id) ->
			deferred = @$q.defer()

			if id
				@Api.sendDataGet({
				 	user_rule: "/user_rules/#{id}"
					usergroups: '/non_sys_usergroups'
				}).then( (result) =>
					data = {}
					data.user_rule = result.data.user_rule.user_rule
					data.all_usergroups = result.data.usergroups.groups
					data.form = @getFormMapper().getFormFromModel(data)
					deferred.resolve(data)
				, ->
					deferred.reject()
				)
			else
				@Api.sendGet('/non_sys_usergroups').then( (result) =>
					data = {}
					data.user_rule = {}
					data.all_usergroups = result.data.groups
					data.form = @getFormMapper().getFormFromModel(data)
					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			return deferred.promise


		###
		# Saves a form model and merges model with list data
		#
		# @param {Object} model user_rule model
		# @param {Object} formModel  The model representing the form
		# @return {promise}
		###
		saveFormModel: (model, formModel) ->
			mapper = @getFormMapper()
			postData = mapper.getPostDataFromForm(formModel)

			if model.id
				promise = @Api.sendPostJson('/user_rules/' + model.id, {user_rule: postData})
			else
				promise = @Api.sendPutJson('/user_rules', {user_rule: postData}).success( (data) ->
					model.id = data.id
				)

			promise.success(=>
				mapper.applyFormToModel(model, formModel)
				@mergeDataModel(model)
			)

			return promise