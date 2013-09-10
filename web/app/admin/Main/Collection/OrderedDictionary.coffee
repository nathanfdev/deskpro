define ->
	###*
	* Save an ordered k=>v
	###
	class Admin_Main_Collection_OrderedDictionary
		constructor: ->
			@data = {}
			@order = []

		set: (k, v) ->
			@data[k] = v

			exist_pos = @order.indexOf(k)
			if exist_pos != -1
				@order.splice(exist_pos, 1)

			@order.push(k)

		get: (k, default_val = null) ->
			if not @data[k]?
				return default_val

			return @data[k]

		remove: (k) ->
			if @data[k]?
				delete @data[k]
				exist_pos = @order.indexOf(k)
				@order.splice(exist_pos, 1)

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