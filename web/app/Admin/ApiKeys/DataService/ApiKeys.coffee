define [
	'Admin/Main/DataService/BaseListEdit',
	'Admin/ApiKeys/ApiKeyEditFormMapper'
], (
	BaseListEdit,
	ApiKeyEditFormMapper
)  ->
	class ApiKeys extends BaseListEdit
		@$inject = ['Api', '$q']

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/api_keys').success( (data) =>

				models = data.api_keys
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

		deleteApiKeyById: (id) ->

			promise = @Api.sendDelete('/api_keys/' + id).success( =>
				@removeListModelById(id)
			)

			return promise

		###
		# Get the form mapper
		#
		# @return {ApiKeyEditFormMapper}
		###

		getFormMapper: ->

			if @formMapper then return @formMapper
			@formMapper = new ApiKeyEditFormMapper()
			return @formMapper

		###
  # Get all data needed for the edit page
  #
  # @param {Integer} id
  # @return {promise}
		###

		loadEditApiKeyData: (id) ->

			deferred = @$q.defer()

			if id

				@Api.sendGet('/api_keys/' + id).then( (result) =>

					data = {}
					data.api_key = result.data.api_key
					data.all_agents = result.data.api_key.all_agents

					data.form = @getFormMapper().getFormFromModel(data)

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			else

				@Api.sendGet('/agents').then( (result) =>

					data = {}

					data.api_key = {person: {}}
					data.all_agents = result.data.agents

					data.form = @getFormMapper().getFormFromModel(data)

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

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
				promise = @Api.sendPostJson('/api_keys/' + model.id, {api_key: postData})
			else
				promise = @Api.sendPutJson('/api_keys', {api_key: postData}).success( (data) ->
					model.id = data.id
				)

			promise.success(=>
				mapper.applyFormToModel(model, formModel)
				@mergeDataModel(model)
			)

			return promise

		###
		# Generate new API key code
 	#
 	# @param {Object} model api_key model
 	# @param {Object} formModel  The model representing the form
 	# @return {promise}
 	###

		regenerateApiKey: (model, formModel) ->

			promise = @Api.sendPostJson('/api_keys/regenerate/' + model.id)

			promise.success( (data) =>

				formModel.code = data.code
				formModel.keyString = data.keyString
			)

			return promise