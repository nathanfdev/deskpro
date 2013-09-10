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
		constructor: (Api, $q) ->
			super()
			@$q   = $q
			@Api  = Api

			@deps = null
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
			deferred = @$q.defer()

			http_def = @Api.sendGet('/ticket_deps').success( (data, status, headers, config) =>
				@_setDepData(data.departments)
				@default_dep = new Admin_Main_Model_Base()
				@default_dep.default_id = data.default_id

				deferred.resolve(@deps, @default_dep)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise


		###*
		* Initialises the models to keep track of department data
    	*
    	* @return {Promise}
		###
		_setDepData: (departments) ->
			@deps = null
			@parent_to_children = {}
			@default_dep = null

			@deps = new Admin_Main_Collection_OrderedDictionary()

			for dep in departments
				model = new Admin_Main_Model_Base()

				if not dep.parent_id
					dep.parent_id = 0

				model.setData(dep)
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
			@deps = null
			@parent_to_children = {}
			@default_dep = null
			return