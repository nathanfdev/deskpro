define [
	'Admin/Main/DataService/BaseListEdit',
	'Admin/ChatDeps/ChatDepFormMapper',
	'DeskPRO/Util/Arrays',
], (
	BaseListEdit,
	ChatDepFormMapper,
	Arrays
)  ->
	class ChatDeps extends BaseListEdit
		@$inject = ['Api', '$q']

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/chat_deps').success( (data) =>
				@deps = data.departments

				proc = (parent) ->
					list = []

					parent_id = if parent then parent.id else null
					for d in data.departments
						if d.parent_id == parent_id
							d.parent = parent
							d.children = proc(d)
							list.push(d)

					return list

				models = proc(null)
				deferred.resolve(models)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise


		###
   # Get the form mapper
   #
   # @return {ChatDepFormMapper}
		###

		getFormMapper: ->
			if @formMapper then return @formMapper
			@formMapper = new ChatDepFormMapper()
			return @formMapper


		###
   # Get all info needed for an 'edit department' form
   #
   # @return {promise}
		###

		getEditDepartmentData: (id) ->
			if id
				promise = @Api.sendDataGet({
					depInfo:            "/chat_deps/#{id}",
					agentsInfo:         '/agents',
					agentgroupsInfo:    '/agentgroups',
					usergroupsInfo:     '/usergroups',
				})
			else
				promise = @Api.sendDataGet({
					agentsInfo:         '/agents',
					agentgroupsInfo:    '/agentgroups',
					usergroupsInfo:     '/usergroups',
				})

			deferred = @$q.defer()

			allPromise = @$q.all([promise, @loadList()]).then( (result) =>
				result = result[0].data

				data = {}

				if result.depInfo
					data.dep      = result.depInfo.department
					data.depPerms = result.depInfo.permissions
				else
					data.dep = {}
					data.depPerms = {
						usergroup_ids: [],
						agentgroup_ids: [],
						agent_ids: []
					}

				data.dep_parent_list = @listModels.slice(0)
				if data.dep.id
					for d, idx in data.dep_parent_list
						if d.id == data.dep.id
							data.dep_parent_list = Arrays.removeIndex(data.dep_parent_list, idx)
							break

				data.agents          = result.agentsInfo.agents
				data.agentgroups     = result.agentgroupsInfo.agentgroups
				data.usergroups      = result.usergroupsInfo.usergroups

				data.form = @getFormMapper().getFormFromModel(
					data.dep,
					data.depPerms,
					data.agents,
					data.agentgroups,
					data.usergroups
				)

				deferred.resolve(data)
			)

			return deferred.promise

		###
		# Save display orders
   #
   # @param {Array} orders An array of ids in order
   # @return {promise}
		###

		saveDisplayOrders: (orders) ->
			postData = {display_orders: []}

			for id,order in orders
				d = @findListModelById(id)
				if d
					d.display_order = order
					postData.display_orders.push(id)

			promise = @Api.sendPostJson('/chat_deps/display_order', postData)
			return promise


		###
  # Saves a form model and applies the form model to the dep model
  # once finished.
  #
  # @param {Object} dep The dep model
  # @param {Object} formModel  The model representing the form
  # @return {promise}
		###

		saveFormModel: (dep, formModel) ->

			mapper = @getFormMapper()
			postData = mapper.getPostDataFromForm(formModel)

			if dep.id
				promise = @Api.sendPostJson('/chat_deps/' + dep.id, postData)
			else
				promise = @Api.sendPutJson('/chat_deps', postData).success( (data) ->
					dep.id = data.id
				)

			promise.success( =>
				mapper.applyFormToModel(dep, formModel)
				@mergeDataModel(dep)
			)

			return promise