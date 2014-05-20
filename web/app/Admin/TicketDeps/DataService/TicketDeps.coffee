define [
	'Admin/Main/DataService/BaseListEdit',
	'Admin/TicketDeps/TicketDepFormMapper',
	'DeskPRO/Util/Arrays',
	'DeskPRO/Util/Util'
], (
	BaseListEdit,
	TicketDepFormMapper,
	Arrays,
	Util
)  ->
	class TicketDeps extends BaseListEdit
		@$inject = ['Api', '$q']

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/ticket_deps').success( (data, status, headers, config) =>
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
    	# @return {TicketDepFormMapper}
		###
		getFormMapper: ->
			if @formMapper then return @formMapper
			@formMapper = new TicketDepFormMapper()
			return @formMapper


		###
    	# Get all info needed for an 'edit department' form
    	#
    	# @return {promise}
		###
		getEditDepartmentData: (id) ->
			if id
				promise = @Api.sendDataGet({
					depInfo:            "/ticket_deps/#{id}",
					agentsInfo:         '/agents',
					agentgroupsInfo:    '/agent_groups',
					usergroupsInfo:     '/user_groups',
					ticketAccountsInfo: '/email_accounts',
					defaultLayoutInfo:  '/ticket_layouts/default',
					customLayoutInfo:   "/ticket_layouts/#{id}",
					layoutStats:        "/ticket_layouts/stats"
				})
			else
				promise = @Api.sendDataGet({
					agentsInfo:         '/agents',
					agentgroupsInfo:    '/agent_groups',
					usergroupsInfo:     '/user_groups',
					ticketAccountsInfo: '/email_accounts',
					defaultLayoutInfo:  '/ticket_layouts/default',
					layoutStats:        "/ticket_layouts/stats"
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

				data.layout_info = result.layoutStats.layout_info

				data.email_accounts = result.ticketAccountsInfo.email_accounts

				data.dep_parent_list = @listModels.slice(0)
				if data.dep.id
					for d, idx in data.dep_parent_list
						if d.id == data.dep.id
							data.dep_parent_list = Arrays.removeIndex(data.dep_parent_list, idx)
							break

				data.agents          = result.agentsInfo.agents
				data.agentgroups     = result.agentgroupsInfo.groups
				data.usergroups      = result.usergroupsInfo.groups

				layouts = {
					default_layout:    result.defaultLayoutInfo.layout,
					custom_layout:     if result.customLayoutInfo then result.customLayoutInfo.layout else null,
					use_custom_layout: if result.customLayoutInfo and not result.customLayoutInfo.is_default then true else false
				}

				data.form = @getFormMapper().getFormFromModel(
					data.dep,
					result.depInfo?.trigger || {},
					layouts,
					data.depPerms,
					data.agents,
					data.agentgroups,
					data.usergroups,
					data.email_accounts
				)

				deferred.resolve(data)
			)

			return deferred.promise


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
			promise = @Api.sendDelete('/ticket_deps/' + id, {
				move_to: move_to
			}).success(=>
				@removeListModelById(id)
			)

			return promise


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

			promise = @Api.sendPostJson('/ticket_deps/display_order', postData)
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
				promise = @Api.sendPostJson('/ticket_deps/' + dep.id, postData)
			else
				promise = @Api.sendPutJson('/ticket_deps', postData).success( (data) ->
					dep.id = data.id
				)

			promise.success(=>
				mapper.applyFormToModel(dep, formModel)
				@mergeDataModel(dep)
			)

			return promise