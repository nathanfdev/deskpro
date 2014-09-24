define [
	'Admin/Main/DataService/BaseListEdit',
	'Admin/ChatDeps/ChatDepFormMapper',
	'DeskPRO/Util/Arrays',
	'DeskPRO/Util/Util'
], (
	BaseListEdit,
	ChatDepFormMapper,
	Arrays,
	Util
)  ->
	class ChatDeps extends BaseListEdit
		@$inject = ['Api', '$q']

		url: -> '/chat_deps'

		resolveResponse: (response) -> response.departments

		all: (reload) ->
			super reload, {with_perms: 1}

		_doLoadList:  (params) ->
			deferred = @$q.defer()
			params = params || {}

			# maybe should init query params as method argument
			@Api.sendGet(@url(), params).success( (data) =>
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
					agentgroupsInfo:    '/agent_groups',
					usergroupsInfo:     '/user_groups',
				})
			else
				promise = @Api.sendDataGet({
					agentsInfo:         '/agents',
					agentgroupsInfo:    '/agent_groups',
					usergroupsInfo:     '/user_groups',
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
				data.agentgroups     = result.agentgroupsInfo.groups
				data.usergroups      = result.usergroupsInfo.groups

				data.form = @getFormMapper().getFormFromModel(
					data.dep,
					data.depPerms,
					data.agents,
					data.agentgroups,
					data.usergroups
				)

				data.dep.original_parent_id = data.dep.parent_id

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

		###
  # Gets an option array of full-title departments.
  #
  # @param {Integer} exclude_id  Dont include this dep in the list
  # @return {Array}
  ###

		getLeafOptionsArray: (exclude_id) ->

			list = []

			proc = (coll, title_seg) ->

				for d in coll
					continue if exclude_id and d.id == exclude_id

					if not title_seg then title_seg = []

					title_seg.push(d.title)

					if d.children and !Util.isEmpty(d.children)
						proc(d.children, title_seg)
					else
						list.push({
							id: d.id,
							title: title_seg.join(" > ")
						})

					title_seg.pop()

			proc(@listModels)

			return list

		###
  # Remove a model from the list by ID.
  #
  # @return {Object/null} The removed object or null if object could not be found
		###

		removeListModelById: (id) ->

			if not @isListLoaded then return
			super(id)

			if not @isListLoaded then return

			removeIdx = null

			for model, idx in @deps
				if model[@idProp] == id
					removeIdx = idx
					break

			result = null

			if removeIdx != null
				result = @deps.splice(removeIdx, 1)
				result = result[0]

			# Remove from children arrays

			for model in @listModels
				if not model.children?.length then continue

				removeIdx = null

				for subModel, idx in model.children
					if subModel.id == id
						removeIdx = idx
						break

				if Util.isNumber(removeIdx)
					model.children.splice(removeIdx, 1)

			return result


		###
  # Remove a department
 	#
 	# @param {Integer} id Department id
 	# @param {Integer} move_to - id to which we want to move department data
  # @return {promise}
		###

		deleteDepartmentById: (id, move_to) ->

			promise = @Api.sendDelete('/chat_deps/' + id, {
				move_to: move_to
			}).success( =>
				@removeListModelById(id)
			)

			return promise