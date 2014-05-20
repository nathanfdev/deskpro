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
				models = data.groups
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
		# Get all data needed for the edit page. Also returns the "reg_group" usergroup info as well.
		#
		# @param {Integer} id
		# @return {promise}
		###
		loadEditUserGroupData: (id) ->
			deferred = @$q.defer()

			sendTypes = {
				everyone_group: '/user_groups/everyone',
				reg_group:      '/user_groups/registered'
			}
			if (id and id != 1)
				sendTypes.group = "/user_groups/#{id}"

			@Api.sendDataGet(sendTypes).then( (result) =>
				data = {}

				data.everyone_group = result.data.everyone_group.group
				data.reg_group      = result.data.reg_group.group

				if result.data.group
					data.group = result.data.group.group
					data.form = @getFormMapper().getFormFromModel(data)
				else
					data.group = { id: null, title: '', is_enabled: true}
					data.group.perms = {}
					data.form = @getFormMapper().getFormFromModel(data)

				deferred.resolve(data)
			, -> deferred.reject())

			return deferred.promise


		###
		# Saves a form model and merges model with list data
		#
		# @param {Object} model api_key model
		# @param {Object} formModel  The model representing the form
		# @return {promise}
		###
		saveFormModel: (model, formModel, formPermsModel) ->
			mapper = @getFormMapper()
			postData = mapper.getPostDataFromForm(formModel, formPermsModel)

			if model.id
				promise = @Api.sendPostJson('/user_groups/' + model.id, {group: postData})
			else
				promise = @Api.sendPutJson('/user_groups', {group: postData}).success( (data) ->
					model.id = data.id
				)

			promise.success( =>
				mapper.applyFormToModel(model, formModel)
				@mergeDataModel(model)
			)

			return promise


		###
    	# Removes group by id
    	#
    	# @param {Integer} groupId
    	# @return promise
		###
		removeGroupById: (groupId) ->
			p = @Api.sendDelete("/user_groups/#{groupId}")
			p.then(=>
				@removeListModelById(groupId)
			)

			return p