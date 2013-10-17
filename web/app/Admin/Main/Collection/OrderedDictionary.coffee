define ['Admin/Main/Util/EventsMixin'], (EventsMixin) ->
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

		count: ->
			return @order.length

		addArray: (array, id_prop = 'id') ->
			for r in array
				id = r[id_prop]
				if id? then @set(id, r)

		set: (k, v) ->
			@_touch = (new Date()).getTime();

			@data[k] = v

			exist_pos = @order.indexOf(k)
			if exist_pos != -1
				@order.splice(exist_pos, 1)

			@order.push(k)
			@notifyListeners('changed')
			return v

		get: (k, default_val = null) ->
			if not @data[k]?
				return default_val

			return @data[k]

		remove: (k) ->
			@_touch = (new Date()).getTime();

			if @data[k]?
				delete @data[k]
				exist_pos = @order.indexOf(k)
				@order.splice(exist_pos, 1)
				@notifyListeners('changed')

			return null

		has: (k) ->
			return !!@data[k]?

		forEach: (fn) ->
			for key in @order
				val = @data[key]
				ret = fn(key, val)
				if ret == false
					break

		getOrderedPair: ->
			ret = []
			for key in @order
				val = @data[key]
				ret.push([key, val])

			return ret

		keys: ->
			return @order

		values: ->
			ret = []
			for key in @order
				val = @data[key]
				ret.push(val)

			return ret