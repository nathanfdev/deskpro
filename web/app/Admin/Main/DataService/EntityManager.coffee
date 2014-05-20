define [
	'Admin/Main/DataService/Base',
	'Admin/Main/Model/Base',
	'Admin/Main/Collection/OrderedDictionary'
], (
	Admin_Main_DataService_Base,
	Admin_Main_Model_Base
)  ->
	class Admin_Main_DataService_EntityManager
		constructor: ->
			@entity_cache = {}


		###*
		* Creates a new managed entity
    	*
    	* @param {String} type_id
    	* @param {String} id_prop
    	* @param {Object} data
    	* @return {Admin_Main_Model_Base}
		###
		createEntity: (type_id, id_prop, data) ->
			entity = @createUnmanagedEntity(type_id, id_prop, data)
			return @add(entity, true)


		###*
		* Creates a new managed entity
    	*
    	* @param {String} type_id
    	* @param {String} id_prop
    	* @param {Object} data
    	* @return {Admin_Main_Model_Base}
		###
		createUnmanagedEntity: (type_id, id_prop, data) ->
			entity = new Admin_Main_Model_Base(type_id, id_prop)

			if data
				entity.setData(data)

			return entity


		###*
		* Adds an entity to the manager
    	*
    	* @param {Admin_Main_Model_Base} entity
    	* @return {Admin_Main_Model_Base} The entity added to the manager
		###
		add: (entity, merge = false) ->
			entity_type = entity.getTypeId()
			entity_id   = entity.getEntityId()

			if not entity_type then throw new Error("entity must have a type_id")
			if not entity_id   then throw new Error("entity must have a id_prop")

			entity_id = entity_id+""
			if not @entity_cache[entity_type]?
				@entity_cache[entity_type] = {}

			exist_entity = null
			if @entity_cache[entity_type][entity_id]?
				exist_entity = @entity_cache[entity_type][entity_id]
				if not merge
					throw new Error("entity already exists in the manager")

			if exist_entity
				for own k, v of entity.getData()
					exist_entity[k] = v

				return exist_entity

			@entity_cache[entity_type][entity_id] = entity
			return entity

		###*
		* Copy properties on one object to other similar objects.
		###
		propogate: (entity) ->
			return

		###*
		* Get an entity from the manager
    	*
    	* @param {Admin_Main_Model_Base} entity
		* @return {Admin_Main_Model_Base}
		###
		get: (entity) ->
			entity_type = entity.getTypeId()
			entity_id   = entity.getEntityId()

			if not entity_type then throw new Error("entity must have a type_id")
			if not entity_id   then throw new Error("entity must have a id_prop")

			entity_id = entity_id+""
			if not @entity_cache[entity_type]?[entity_id]? then throw new Error("entity does not exist in manager")

			return @entity_cache[entity_type][entity_id]


		###*
		* Get an entity from the manager
    	*
    	* @param {Admin_Main_Model_Base} entity
    	* @return {Admin_Main_Model_Base}
		###
		getById: (entity_type, entity_id) ->
			if not entity_type then throw new Error("entity must have a type_id")
			if not entity_id   then throw new Error("entity must have a id_prop")
			entity_id = entity_id+""

			if not @entity_cache[entity_type]?[entity_id]? then throw new Error("entity does not exist in manager")

			return @entity_cache[entity_type][entity_id]


		###*
		* Remove an entity from the manager
    	*
    	* @param {Admin_Main_Model_Base} entity
		###
		remove: (entity) ->
			entity_type = entity.getTypeId()
			entity_id   = entity.getEntityId()

			if not entity_type then throw new Error("entity must have a type_id")
			if not entity_id   then throw new Error("entity must have a id_prop")
			entity_id = entity_id+""

			if @entity_cache[entity_type]?[entity_id]?
				delete @entity_cache[entity_type][entity_id]


		###*
		* Remove an entity from the manager
    	*
    	* @param {Admin_Main_Model_Base} entity
		###
		removeById: (entity_type, entity_id) ->
			entity_id = entity_id+""

			if @entity_cache[entity_type]?[entity_id]?
				delete @entity_cache[entity_type][entity_id]


		###*
		* Check if the manager has an entity
    	*
    	* @param {Admin_Main_Model_Base} entity
    	* @return {Boolean}
		###
		hasById: (entity_type, entity_id) ->
			entity_id = entity_id+""

			if @entity_cache[entity_type]?[entity_id]?
				return true

			return false


		###*
		* Check if the manager has an entity
    	*
    	* @param {Admin_Main_Model_Base} entity
    	* @return {Boolean}
		###
		has: (entity) ->
			entity_type = entity.getTypeId()
			entity_id   = entity.getEntityId()

			if not entity_type then throw new Error("entity must have a type_id")
			if not entity_id   then throw new Error("entity must have a id_prop and valid ID")
			entity_id = entity_id+""

			if @entity_cache[entity_type]?[entity_id]?
				return true

			return false


		###*
		* Runs the auto-release which removes entities that are no longer in use
		###
		autoRelease: ->
			time_cut = (new Date()).getTime() - 10000
			for own type, entities of @entity_cache
				for ent_id, ent of entities
					if ent._obj_refc < 1 and ent._obj_time < time_cut
						delete @entity_cache[type][ent_id]


		###*
		* Clears entities from the manager
    	*
    	* @param {String} type A specific type
		###
		clear: (type = null) ->
			if type
				delete @entity_cache[type]
			else
				@entity_cache = {}