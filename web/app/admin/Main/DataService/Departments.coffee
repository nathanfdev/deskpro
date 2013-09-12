define [
	'Admin/Main/DataService/Base',
	'Admin/Main/Model/Base',
	'Admin/Main/Collection/OrderedDictionary'
], (
	Admin_Main_DataService_Base,
	Admin_Main_Model_Base,
	Admin_Main_Collection_OrderedDictionary
)  ->
	class Admin_Main_DataService_Departments extends Admin_Main_DataService_Base
		constructor: (em, Api, $q) ->
			super(em)
			@$q   = $q
			@Api  = Api

			@loadDepListPromise = null
			@deps = new Admin_Main_Collection_OrderedDictionary()
			@parent_to_children = {}
			@default_dep = null


		###*
		* Loads the whole department structure
    	* Returns a promise.
    	* After loaded, you can use getDepartments()
    	*
    	* @return {Promise}
		###
		loadDepList: (reload) ->

			if @loadDepListPromise
				return @loadDepListPromise

			deferred = @$q.defer()
			if not reload and @deps.count()
				deferred.resolve(@deps)
				return deferred.promise

			http_def = @Api.sendGet('/ticket_deps').success( (data, status, headers, config) =>
				@_setDepData(data.departments)
				@default_dep = new Admin_Main_Model_Base()
				@default_dep.default_id = data.default_id

				deferred.resolve(@deps)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			@loadDepListPromise = deferred.promise

			return @loadDepListPromise


		###*
		* Adds a new model to the existing department list (eg a dep was just created)
    	*
    	* @return {Admin_Main_Model_Base}
		###
		addToList: (dep) ->
			if not dep._is_model
				model = @em.createEntity('department', 'id', dep)
			else
				model = @em.add(dep, true)

			@deps.set(model.id, model)
			return model


		###*
		* Initialises the models to keep track of department data
    	*
    	* @return {Promise}
		###
		_setDepData: (departments) ->
			@parent_to_children = {}
			@default_dep = null

			for dep in departments
				if not dep.parent_id
					dep.parent_id = 0

				model = @em.createEntity('department', 'id', dep)
				model.retain()
				@deps.set(model.id, model)

			for dep in @deps.values()
				if dep.parent_id
					parent_dep = @deps.get(dep.parent_id)
					if parent_dep
						if not @parent_to_children[parent_dep.id]?
							@parent_to_children[parent_dep.id] = []

						@parent_to_children[parent_dep.id].push(dep.id)
						dep._depth = 1


		###*
		* Cleans up models that are sitting in memory
		###
		_cleanup: ->
			if @deps
				for dep in @deps.values()
					dep.release()

			@deps = null
			@parent_to_children = {}
			@default_dep = null
			return