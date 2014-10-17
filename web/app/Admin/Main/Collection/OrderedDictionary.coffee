define ['Admin/Main/Util/EventsMixin', 'DeskPRO/Util/Numbers'], (EventsMixin, Numbers) ->
	###*
	* Save an ordered k=>v
	###
	class Admin_Main_Collection_OrderedDictionary
		constructor: ->
			EventsMixin(this)
			@_touch = (new Date()).getTime();
			@scope = null
			@data = {}
			@order = []
			@orderFn = null

		###
    	# Clears all data from the collection
    	###
		clear: ->
			for key in @order
				delete @data[key]
			@order.length = 0


		###
    	# Re-order the collection with a callback comparison function.
    	#
    	# @param {Function} callback The callback function that returns 0, -1 or 1
    	###
		reorder: (callback) ->
			callback = callback || @orderFn
			return if not callback

			@order.sort( (k1, k2) =>
				v1 = @data[k1]
				v2 = @data[k2]

				return callback(v1, v2)
			)


		###
    	# Count how many items are in the collection
    	#
    	# @return {Integer}
    	###
		count: ->
			return @order.length


		###
    	# Add an array of objects, getting the key as id_prop from the object
    	#
    	# @param {Array} array The array of objects to add
    	# @param {String} id_prop The property of th eobject to use as the key
    	###
		addArray: (array, id_prop = 'id') ->
			for r in array
				id = r[id_prop]
				if id? then @set(id, r)


		###
    	# Set a value in the collection
    	#
    	# @param {String} k The key to set
    	# @param {mixed} v The value to set
    	# @return {mixed} The v that was set
    	###
		set: (k, v) ->
			@_touch = (new Date()).getTime();

			if Numbers.isNumber(k) then k = parseInt(k)

			@data[k] = v

			exist_pos = @order.indexOf(k)
			if exist_pos != -1
				@order.splice(exist_pos, 1)

			@order.push(k)
			@reorder()
			@notifyListeners('changed')
			return v


		###
    	# Get the value by key. If k does not exist, returns default_val
    	#
    	# @param {String} k The key to get
    	# @param {mixed} default_val The value to return if k does not exist
    	# @return {mixed}
    	###
		get: (k, default_val = null) ->
			if Numbers.isNumber(k) then k = parseInt(k)
			if not @data[k]?
				return default_val

			return @data[k]


		###
    	# Remove a key from the collection
    	#
    	# @param {String} k The key to remove
    	# @return {mixed} The value removed, or null if no key
    	###
		remove: (k) ->
			@_touch = (new Date()).getTime()
			if Numbers.isNumber(k) then k = parseInt(k)

			val = null
			if @data[k]?
				val = @data[k]
				delete @data[k]
				exist_pos = @order.indexOf(k)
				@order.splice(exist_pos, 1)
				@reorder()
				@notifyListeners('changed')

			return val


		###
    	# Check if collection contains a key
    	#
    	# @param {String} k
    	# @return {Booleab}
    	###
		has: (k) ->
			return !!@data[k]?


		###
    	# Call fn over every key,val of the collection
    	#
    	# @param {Function} A function that should accept two args: key and val. Return false to stop the loop.
    	# @return void
    	###
		forEach: (fn) ->
			for key in @order
				val = @data[key]
				ret = fn(key, val)
				if ret == false
					break


		###
    	# Get an ordered array of [key, val]
    	#
    	# @return {Array}
    	###
		getOrderedPair: ->
			ret = []
			for key in @order
				val = @data[key]
				ret.push([key, val])

			return ret


		###
    	# Get collection keys as an ordered array
    	#
    	# @return {Array}
    	###
		keys: ->
			return @order


		###
    	# Get the collection as an array
    	#
    	# @return {Array}
		###
		values: ->
			ret = []
			for key in @order
				val = @data[key]
				ret.push(val)

			return ret