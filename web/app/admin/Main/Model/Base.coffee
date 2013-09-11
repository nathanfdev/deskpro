define ['angular'], (angular) ->
	###*
    * A model holds data about some kind of entity.
    * Our model class does nothing special except tries to make it easier
    * to dirty-check.
    *
    * Warning: All fields on this object are considered data unless
    * they begin with an underscore. E.g., @title is considered
    * to be a data title on the entity while @_title is considered
    * an internal state representation.
    *
    * Note that we do NOT handle any kind of nesting of models,
    * and these models are completely unaware of other models already loaded.
    * That means you could have two records with ID 5, or a record with a "parent" of 5 etc.
    * These models by themselves are not a repository.
	###
	class Admin_Main_Model_Base
		constructor: (type_id, id_prop = 'id') ->
			@_obj_time   = (new Date()).getTime()
			@_obj_refc   = 0
			@_type_id    = type_id
			@_id_prop    = id_prop
			@_is_mult_id = angular.isArray(id_prop)
			@_dp_uid     = dp_get_uid()
			@_is_model   = true
			@_data_checkpoints = []


		###*
    	# Add to the ref counter
    	#
    	# @param {Object} obj Optionally set up auto-release on obj
    	###
		retain: (obj) ->
			@_obj_refc += 1
			@_obj_time  = (new Date()).getTime()

			if obj? and obj._configureAutoReleaseObject?
				obj._configureAutoReleaseObject(obj)


		###*
    	# Remove from the ref counter
    	###
		release: ->
			@_obj_refc -= 1
			@_obj_time  = (new Date()).getTime()


		###*
    	* Get the type of model this is
    	*
    	* @return {String}
		###
		getTypeId: ->
			return @_type_id

		###*
    	* Get the ID of the entity this object represents (typically a numeric ID)
    	*
    	* @return {Integer}
		###
		getEntityId: ->
			if @[@_id_prop]?
				if @_is_mult_id
					id_parts = []
					for idp in @_id_prop
						id_parts.push(idp)
					return id_parts.join('::')

				else
					return @[@_id_prop]

			return null

		###*
    	* Create a new checkpoint. Checkpoints allow you to revert data to previous states or compare
    	* with previous states.
    	*
    	* @param {String} chk_id Optionally provide an ID to refer to the checkpoint later
		###
		setCheckpoint: (chk_id = null) ->
			data = {}
			for own key, value of @
				if key.substr(0, 1) != '_'
					data[key] = value

			@_data_checkpoints.push([chk_id, data])


		###*
    	* Get data for a checkpoint.
    	*
    	* @param {String} chk_id Optionally provide an ID, else the latest checkpoint is returned
		###
		getCheckpoint: (chk_id = null) ->
			if chk_id
				for cp in @_data_checkpoints
					return cp[1] if cp[0] == id
			else
				return @_data_checkpoints[@_data_checkpoints.length - 1][1]

			return null


		###*
    	* Revert to a previous checkpoint
    	*
    	* @param {String} chk_id Optionally provide an ID, else the latest checkpoint is returned
		###
		revertCheckpoint: (chk_id = null) ->
			if chk_id
				for cp, i in @_data_checkpoints
					if cp[0] == chk_id
						data = cp[1]
						@_data_checkpoints = @_data_checkpoints.splice(i, 0)
						break

				if not data
					throw new Error("No checkpoint found with that ID")
			else
				data = @_data_checkpoints.pop()

			@setCheckpoint()
			@setData(data)


		###*
    	* Revert to the first checkpoint (e.g., the initial data)
		###
		revertAllCheckpoints: ->
			data = @_data_checkpoints.shift()
			@clearCheckpoints()
			@setData(data)


		###*
    	* Clears all checkpoints. The data set now is considered the initial data.
		###
		clearCheckpoints: ->
			@_data_checkpoints = []
			@setCheckpoint()


		###*
    	* Set data on this model
    	*
    	* @param {Object} data
		###
		setData: (data) ->
			for own key, value of data
				@[key] = value

			if not @_data_checkpoints.length
				@setCheckpoint()


		###*
    	* Gets all data on this model.
    	*
    	* @return {Object}
		###
		getData: ->
			data = {}
			for own key, value of @
				if key.substr(0, 1) != '_'
					data[key] = value

			return data


		###*
    	* Return an array of field names that have changed.
    	*
    	* @return {Array}
		###
		getChangedFields: (chk_id = null) ->
			changed = []

			last_data = @getCheckpoint(chk_id)
			if not last_data
				throw new Error("No checkpoint to compare against")

			for own key, value of @
				if value != last_data[key]
					changed.push(key)

			return changed