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
			@default_dep = null

		loadDepList: ->
			deferred = @$q.defer()

			http_def = @Api.sendGet('/ticket_deps').success( (data, status, headers, config) =>

				@deps = new Admin_Main_Collection_OrderedDictionary()
				for dep in data.departments
					model = new Admin_Main_Model_Base()
					model.setData(dep)
					@deps.set(model.id, model)

				@default_dep = new Admin_Main_Model_Base()
				@default_dep.default_id = data.default_id

				deferred.resolve(@deps, @default_dep)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise

		_cleanup: ->
			@deps = null
			return