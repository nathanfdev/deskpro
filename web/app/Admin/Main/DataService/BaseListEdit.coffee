define [
	'DeskPRO/Util/Angular',
	'DeskPRO/Util/Arrays',
	'DeskPRO/Util/Util'
], (
	Util_Angular,
	Arrays,
	Util
) ->
	###
    # This is a simple base data service that implements some default functionality for
    # loading the "list" collection, and some methods for keeping the list up to date.
    ###
	class Admin_Main_DataService_BaseListEdit
		constructor: ->
			Util_Angular.setInjectedProperties(this, arguments)
			@loadListPromise   = null
			@isListLoaded      = false
			@listModels        = []
			@idProp            = 'id'
			@orderField        = 'display_order'
			@init()


		###
    	# An empty hook method for sub-classes
    	###
		init: ->
			return


		###
		# Loads list of accounts
    	#
    	# @return {Promise}
		###
		loadList: (reload) ->
			if reload
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
				deferred.resolve(@listModels)
			, =>
				deferred.reject()
			)

			return @loadListPromise


		###
    	# Sets ist data on the @listModels object
    	###
		_setListData: (listModels) ->
			@listModels.length = 0
			for model in listModels
				@listModels.push(model)


		###
    	# Find a model that has been loaded into the list
    	#
    	# @param {Integer} id
    	# @return {Object}
    	###
		findListModelById: (id) ->

			for model in @listModels

				if model[@idProp] == id
					return model

				if model.children
					for child in model.children
						if child[@idProp] == id
							return child

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
		###
		mergeDataModel: (dataModel, dataMapper = null) ->

			if not @isListLoaded then return

			listModel = null
			oldParent = null

			for model, idx in @listModels

				if model[@idProp] == dataModel[@idProp]
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

						# first - add new model as child to the parent model

						parent = @findListModelById(dataModel.parent_id)
						parent.children.push(dataModel)

						# second - delete this child from top-level list

						removeIdx = @returnIndexForModel(dataModel)
						if Util.isNumber(removeIdx) then @listModels.splice(removeIdx, 1)

			else

				# this is case of model that doens't exist in the list yet

				if dataMapper

					newListModel = dataMapper(dataModel)

				else

					newListModel = dataModel
					newListModel.children = []

				@listModels.push(newListModel)


		###
    	# Remove a model from the list by ID.
    	#
    	# @return {Object/null} The removed object or null if object could not be found
		###
		removeListModelById: (id) ->
			if not @isListLoaded then return
			removeIdx = null
			for model, idx in @listModels
				if model[@idProp] == id
					removeIdx = idx
					break

			result = null
			if removeIdx != null
				result = @listModels.splice(removeIdx, 1)
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