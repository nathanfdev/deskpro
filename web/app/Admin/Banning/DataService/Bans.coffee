define [
	'Admin/Main/DataService/BaseListEdit'
], (
	BaseListEdit,
)  ->
	class Bans extends BaseListEdit
		@$inject = ['Api', '$q']
		@type = 'ip'

		###
 	#
 	###

		init: ->
			@setSubLists ['ip_bans', 'email_bans']

		###
 	#
 	###

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/banning').success( (data) =>

				models = data.bans
				deferred.resolve(models)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise

		###
 	# Sets type of ban that is used for create / update / delete operations
		#
 	# @param {string} type
 	###

		setType: (type) ->

			@type = type

		###
  # Remove a model
  #
  # @param {Integer} id
  # @return {promise}
		###

		deleteBanById: (id) ->

			promise = @Api.sendDelete('/banning_' + @type + '/' + id).success( =>
				@removeListModelById(id)
			)

			return promise

		###
  # Get all data needed for the edit page
  #
  # @param {Integer} id
  # @return {promise}
		###

		loadEditBanData: (id) ->

			deferred = @$q.defer()

			if id

				@Api.sendGet('/banning_' + @type + '/' + id).then( (result) =>

					data = {}
					data[@type + '_ban'] = result.data[@type + '_ban']
					data.form = data

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			else

				@Api.sendGet('/banning_' + @type).then( (result) =>

					data = {}
					data[@type + '_ban'] = {}
					data.form = data

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			return deferred.promise

		###
  # Saves a form model and merges model with list data
  #
  # @param {Object} model
 	# @param {Object} formModel  The model representing the form
  # @return {promise}
		###

		saveFormModel: (model, formModel) ->

			postData = formModel

			sendData = {}
			sendData[@type + '_ban'] = postData

			if model.id
				promise = @Api.sendPostJson('/banning_' + @type + '/' + model.id, sendData)
			else
				promise = @Api.sendPutJson('/banning_' + @type, sendData).success( (data) ->
					model.id = data.id
				)

			promise.success( =>
				@mergeDataModel(model)
			)

			return promise