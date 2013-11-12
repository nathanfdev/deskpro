define [
	'Admin/Main/DataService/BaseListEdit'
], (
	BaseListEdit
)  ->
	class Admin_TwitterAccounts_DataService_TwitterAccounts extends BaseListEdit
		@$inject = ['Api', '$q']

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/twitter_accounts').success( (data) =>
				models = data.twitter_accounts
				deferred.resolve(models)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise

		###
    	# Remove a model
    	#
    	# @param {Integer} id twitter_account id
    	# @return {promise}
		###
		deleteTwitterAccountById: (id) ->
			promise = @Api.sendDelete('/twitter_accounts/' + id).then(=>
				@removeListModelById(id)
			)
			return promise

		###
    	# Get all data needed for the edit page
    	#
    	# @param {Integer} id twitter_account id
    	# @return {promise}
		###
		loadEditTwitterAccountData: (id) ->

			deferred = @$q.defer()

			if id
				@Api.sendGet('/twitter_accounts/' + id).then( (result) =>
					data = {}
					data.twitter_account = result.data.twitter_account
					data.all_agents = result.data.twitter_account.all_agents
					deferred.resolve(data)
				, ->
					deferred.reject()
				)
			else
				data = {}
				data.twitter_account = {
					id: null,
					user: {
						agents: {}
					}
				}
				deferred.resolve(data)

			return deferred.promise


		###
    	# Saves a form model and merges model with list data
    	#
    	# @param {Object} model twitter_account model
    	# @return {promise}
		###
		saveFormModel: (model) ->

			if model.id
				promise = @Api.sendPostJson('/twitter_accounts/' + model.id, {twitter_account: model})
			else
				promise = @Api.sendPutJson('/twitter_accounts', {twitter_account: model}).success( (data) ->
					model.id = data.id
				)

			promise.success(=>
				@mergeDataModel(model)
			)

			return promise