define [
	'DeskPRO/Util/Angular',
	'DeskPRO/Util/Arrays',
	'DeskPRO/Util/Util',
	'angular'
], (
	Util_Angular,
	Arrays,
	Util,
	angular
) ->
	###
	# This is a simple base data service that implements some default functionality for
	# loading the "list" collection, and some methods for keeping the list up to date.
	###
	class Admin_Main_DataService_BaseListEdit
		constructor: ->
			Util_Angular.setInjectedProperties(this, arguments)
			@map = {}
			@loadListPromise   = null
			@isListLoaded      = false
			@listModels        = []
			@idProp            = 'id'
			@orderField        = 'display_order'
			@subLists          = []
			@pagination        = {}
			@isReloadWaiting   = false
			@init()


		###
		# An empty hook method for sub-classes
		###
		init: ->
			return


		###
    	# If data has changed, then the next time this list
    	# is loaded should be new
    	###
		setReloadNext: ->
			@isReloadWaiting = true


		###
		# Loads list of accounts
		#
		# @return {Promise}
		###
		loadList: (reload) ->
			if reload or @isReloadWaiting
				@loadListPromise = null
				@isListLoaded = false

			if @loadListPromise
				return @loadListPromise

			if @isListLoaded
				deferred = @$q.defer()
				deferred.resolve(@listModels)
				return deferred.promise

			deferred = @$q.defer()
			@loadListPromise = deferred.promise

			@_doLoadList().then( (models) =>
				@isListLoaded = true
				@_setListData(models)
				@_setPaginationData(models)
				deferred.resolve(@listModels)
			, =>
				deferred.reject()
			)

			return @loadListPromise

		refreshList: ->
			deferred = @$q.defer()
			@loadListPromise = deferred.promise

			@_doRefreshList().then((models) =>
				@_setListData(models)
				@_setPaginationData(models)
				deferred.resolve(@listModels)
			, =>
				deferred.reject()
			)
			return @loadListPromise


		###
		# This is useful if we need to store 2 or more lists instead of default one
		# Call this method somewhere ( init() method of data service is preferable) and use array of names of sub lists
		#
		# @param {Array} subLists - array with names of sub lists (eg. ['email_data', 'ip_data'])
		###
		setSubLists: (subLists) ->
			if Util.isArray(subLists) then @subLists = subLists


		###
		# Sets ist data on the @listModels object
		###
		_setListData: (listModels) ->
			@listModels.length = 0

			if not listModels then return

			if @subLists.length
				@listModels = {}
				for subModel in @subLists
					@listModels[subModel] = []

					if listModels[subModel]
						for model in listModels[subModel]
							@listModels[subModel].push(model)
			else
				for model in listModels
					@_addModel model


		###
		# Sets pagination data for current data service
		#
		# @param {Object} listModels - object representing the list
		###
		_setPaginationData: (listModels) ->

			if not listModels then return

			# we assume that backend returned appropriate pagination info and doesn't check its correctness here
			@pagination = listModels.pagination if listModels.pagination
			# in case of no data from backend just skip pagination step
			if Util.isEmpty(@pagination) then return

			if @subLists.length
				for subModel in @subLists
					@pagination[subModel].page = @pagination[subModel].page || "1"
					@pagination[subModel].page_nums = []
					for i in [0...@pagination[subModel].num_pages]
						@pagination[subModel].page_nums.push(i + 1)

			else
				@pagination.page_nums = []
				for i in [0..@pagination.num_pages]
					@pagination.page_nums.push(i + 1)

		###
		#	@return {Object} returns information about pagination
		###
		getPagination: ->
			return @pagination

		###
		# Find a model that has been loaded into the list
		#
		# @param {Integer} id
		# @return {Object}
		###
		findListModelById: (id) ->
			if @subLists.length
				for subModel in @subLists
					for model in @listModels[subModel]
						if model[@idProp] == id
							return model

			else
				for model in @listModels
					if model[@idProp] == id
						return model
					if model.children
						for child in model.children
							if child[@idProp] == id
								return child
			return null

		###
		# Find children of specified object
		#
		# @param {Object} obj - specified object in which we'll search
		# @param {Integet} id - id of children we want to search
		###
		findChildModelById: (obj, id) ->

			if obj.children
				for model in obj.children
					if model[@idProp] == id
						return model

			return null

		###
		# Returns index of specified model
		#
		# @param {Object} obj
		# @return {Object}
		###
		returnIndexForModel: (obj) ->

			for model, idx in @listModels

				if model[@idProp] == obj[@idProp]
					return idx

			return null

		###
		# Checks whether an object has children or not
		#
		# @param {Object} obj
		# @return {Boolean}
		###
		hasChildren: (obj) ->

			model = @findListModelById(obj.id)

			if model and model.children and model.children.length
				return true

			return false

		###
		# Checks whether obj has children and form changed its value since it was created, could be useful in some cases
		#
		#	@param {Object} obj - model object
		# @param {Object} form - form object
		# @return {Boolean}
		###
		hasChildrenAndChangedParent: (obj, form) ->
			value1 = parseInt(obj.original_parent_id || 0)
			value2 = parseInt(form.parent_id)

			if @hasChildren(obj) and parseInt(value1) != parseInt(value2)
				return true

			return false

		###
		# This method should be overriden.
		#
		# This method needs to load the list data and needs to
		# resolve to an array of models that will be set on the list collection.
		#
		# This method must return a promise
		#
		# @return {promise}
		###
		_doLoadList: ->
			throw new Exception("This method must be implemented by a sub-class")


		###
		# Takes a data model and updates the list.
		# For example, you would use this when you want to apply changes from the Edit pane into the List pane.
		# By merging the data model, this will either 1) update the list model (eg the title) or 2) create
		# a new list model and append it to the list.
		#
		# You should always supply a dataMapper. The default implementation is to just get the id/title properties
		# from teh dataModel which may not be sufficient.
		#
		# @param {Object} dataModel
		# @param {Function} dataMapper Optionally supply a function that can create the listModel for cases we need to append it to the list
		# @param {String} subList Optional parameter in case we want to update only sub list
		###
		mergeDataModel: (dataModel, dataMapper = null, subList = null) ->

			if not @isListLoaded then return

			listModel = null
			oldParent = null

			if subList
				for model, idx in @listModels[subList]
					if model[@idProp] == dataModel[@idProp]
						listModel = model
						break
					if dataModel.old_id and model[@idProp] == dataModel.old_id
						listModel = model
						break

			else
				for model, idx in @listModels
					if model[@idProp] == dataModel[@idProp]
						listModel = model
						break
					if dataModel.old_id and model[@idProp] == dataModel.old_id
						listModel = model
						break

					if model.children
						for child in model.children
							if child[@idProp] == dataModel[@idProp]
								oldParent = model
								listModel = child
								break

			# if this model is already in list then we some options
			if listModel != null
				for k, v of listModel
					if dataModel[k]?
						listModel[k] = dataModel[k]

				# case of changing the parent AND if model already has parent - have to re-populate sub-tree with children
				if oldParent? and oldParent[@idProp] != dataModel.parent_id
					for model, idx in oldParent.children
							if model[@idProp] == dataModel[@idProp]
									removeIdx = idx
									break

					if removeIdx?
						oldParent.children.splice(removeIdx, 1)

					if dataModel.parent_id?
						parent = @findListModelById(dataModel.parent_id)
						parent.children.push(dataModel)

					else
						@listModels.push(dataModel)

				# case of changing the parent AND if model previously didn't have a parent - should add this model as child
				else
					if dataModel.parent_id?
						# first - add new model as child to the parent model (if it's not already there - this is for cases when parent not changed)
						parent = @findListModelById(dataModel.parent_id)
						existingChild =  @findChildModelById(parent, dataModel.id)

						if !existingChild then parent.children.push(dataModel)

						# second - delete this child from top-level list
						removeIdx = @returnIndexForModel(dataModel)
						if Util.isNumber(removeIdx) then @listModels.splice(removeIdx, 1)

			else
				# this is case of model that doesn't exist in the list yet
				if dataMapper
					newListModel = dataMapper(dataModel)
				else
					if dataModel.parent_id?
						parent = @findListModelById(dataModel.parent_id)
						if !parent.children then parent.children = []
						parent.children.push(dataModel)

					else
						newListModel = dataModel
						newListModel.children = []

				@listModels.push(newListModel) if newListModel and !subList
				@listModels[subList].push(newListModel) if newListModel and subList

			if dataModel.old_id then dataModel.old_id = dataModel[@idProp]


		###
		# Remove a model from the list by ID.
		#
		# @return {Object/null} The removed object or null if object could not be found
		###
		removeListModelById: (id) ->

			if not @isListLoaded then return

			removeIdx = null
			subModelIdx = null

			if @subLists.length
				for subModel in @subLists
					for model, idx in @listModels[subModel]
						if model[@idProp] == id
							removeIdx = idx
							subModelIdx = subModel
							break

			else
				for model, idx in @listModels
					if model[@idProp] == id
						removeIdx = idx
						break

			result = null

			if removeIdx != null
				result = @listModels[subModelIdx].splice(removeIdx, 1) if subModelIdx
				result = @listModels.splice(removeIdx, 1) if not subModelIdx
				result = result[0]

			return result


		###
		# Re-orders the list collection
		###
		reorderList: ->
			if not @isListLoaded then return

			@listModels.sort( (data1, data2) =>
				if data1[@orderField]
					o1 = data1[@orderField]
				else
					o1 = data[@idProp]

				if data2[@orderField]
					o2 = data2[@orderField]
				else
					o2 = data2[@idProp]

				if o1 == o2
					return 0

				return (o1 < o2) ? -1 : 1
			)
			@listModels.reverse()



		# add new model to list/map
		_addModel: (model) ->
			return null if !model[@idProp]?
			if @map[model[@idProp]]?
				angular.copy model, @map[model[@idProp]] # update exist model
				if @listModels.indexOf(model) == -1
					@listModels.push(model)
			else
				@map[model[@idProp]] = model
				@listModels.push model

			model



		# remove model from list/map
		_removeModel: (model) ->
			return null if !model[@idProp]?
			model = @map[model[@idProp]]
			return null if !model?

			delete @map[model[@idProp]]
			idx = @listModels.indexOf model
			@listModels.splice(idx, 1) if idx > -1



		# simple proxy
		all: ->
			@loadList()



		# simple proxy
		get: (id) ->
			if !id? then return null

			deferred = @$q.defer()
			@loadList().then =>
				deferred.resolve @map[id] || null

			deferred.promise



		# update/create model
		set: (data) ->
			def = @$q.defer()

			@_doSave(data).then(
				(data) =>
					def.resolve @_addModel data
				(res) =>
					def.reject res
			)

			def.promise



		# remove model
		remove: (model) ->
			def = @$q.defer()

			@_doRemove(model).then(
				() =>
					def.resolve @_removeModel(model)
				(res) =>
					def.reject res
			)

			def.promise



		url: ->
			throw new Exception("This method must be implemented by a sub-class")



		resolveResponse: (response) ->
			response



		# overriden by child classes for back compatibiliy
		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet(@url()).success (data) =>
				deferred.resolve @resolveResponse(data)
			.error (data, status, headers, config) ->
					deferred.reject(data)

			deferred.promise



		_doSave: (model) ->
			deferred = @$q.defer()
			method = 'sendPostJson' # is new
			method = 'sendPutJson' if model[@idProp]? and model[@idProp]

			id = model[@idProp] || 0
			@Api[method](@url() + "/#{id}", model).success (data) =>
				deferred.resolve @resolveResponse(data)
			.error (data, status, headers, config) =>
					deferred.reject
						info: data.error_message
						status: status

			deferred.promise



		_doRemove: (model) ->
			deferred = @$q.defer()

			id = model[@idProp] || 0
			@Api.sendDelete(@url() + "/#{id}").success =>
				deferred.resolve()
			.error (data, status, headers, config) =>
					deferred.reject
						info: data.error_message
						status: status

			deferred.promise
