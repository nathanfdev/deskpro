define [
	'Admin/Main/DataService/BaseListEdit',
	'Admin/UserGroups/UserGroupEditFormMapper'
], (
	BaseListEdit,
	UserGroupEditFormMapper
)  ->
	class UserGroups extends BaseListEdit
		@$inject = ['Api', '$q']

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/user_groups').success( (data) =>

				models = data.user_groups
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

		deleteUserGroupById: (id) ->

			promise = @Api.sendDelete('/user_groups/' + id).success( =>
				@removeListModelById(id)
			)

			return promise

		###
		# Get the form mapper
		#
		# @return {UserGroupEditFormMapper}
		###

		getFormMapper: ->

			if @formMapper then return @formMapper
			@formMapper = new UserGroupEditFormMapper()
			return @formMapper

		###
  # Get all data needed for the edit page
  #
  # @param {Integer} id
  # @return {promise}
		###

		loadEditUserGroupData: (id) ->

			deferred = @$q.defer()

			if id

				@Api.sendGet('/user_groups/' + id).then( (result) =>

					data = {}
					data.user_group = result.data.user_group

					data.form = @getFormMapper().getFormFromModel(data)

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			else

				data = {}

				data.user_group = {}

				data.form = @getFormMapper().getFormFromModel(data)

				deferred.resolve(data)

			return deferred.promise


		###
  # Saves a form model and merges model with list data
  #
  # @param {Object} model api_key model
 	# @param {Object} formModel  The model representing the form
  # @return {promise}
		###

		saveFormModel: (model, formModel) ->

			mapper = @getFormMapper()

			postData = mapper.getPostDataFromForm(formModel)

			if model.id
				promise = @Api.sendPostJson('/user_groups/' + model.id, {user_group: postData})
			else
				promise = @Api.sendPutJson('/user_groups', {user_group: postData}).success( (data) ->
					model.id = data.id
				)

			promise.success( =>
				mapper.applyFormToModel(model, formModel)
				@mergeDataModel(model)
			)

			return promise