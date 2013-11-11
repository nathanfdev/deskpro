define ->
	class DeskPRO_Util_Util
		@UID_COUNTER = 0

		constructor: ->
			@optIsfunc = false
			if typeof /./ != 'function'
				@optIsfunc = true

			@nativeIsArray = false
			if Array.isArray?
				@nativeIsArray = true

			@nativeObjKeys = false
			if Object.keys?
				@nativeObjKeys = true

		###
		# Gets a unique number for the current page
		#
		# @param {String} prefix Optional prefix
		# @return {String}
		###
		uid: (prefix = '') ->
			DeskPRO_Util_Util.UID_COUNTER++
			return prefix + DeskPRO_Util_Util.UID_COUNTER

		###
		# Get a random number between min and max inclusive.
		#
		# @param {Integer} min
		# @param {Integer} max
		# @return {Integer}
		###
		random: (min, max = null) ->
			if max == null
				max = min
				min = 0

			return min + Math.floor(Math.random() * (max - min + 1));

		###
		# Get an array of [key, value] in an object
		#
		# @param {Object} obj
		# @return {Array}
		###
		keyValuePair: (obj) ->
			pairs = []
			for own k, v of obj
				pairs.push([k, v])

			return pairs


		###
		# Get an array of keys in an object
		#
		# @param {Object} obj
		# @return {Array}
		###
		keys: (obj) ->
			if @nativeObjKeys
				return obj.keys()
			else
				keys = []
				for own k, v of obj
					keys.push(k)

				return keys


		###
		# Get an array of values in an object
		#
		# @param {Object} obj
		# @return {Array}
		###
		values: (obj) ->
			values = []
			for own k, v of obj
				values.push(v)

			return values


		###
		# Check if a value is a function
		#
		# @param {Object} obj
		# @return {bool}
		###
		isFunction: (obj) ->
			if @optIsfunc
				return typeof obj == 'function'
			else
				return Object.prototype.toString.call(obj) == '[object Function]';

		###
		# Check if a value is a string
		#
		# @param {Object} obj
		# @return {bool}
		###
		isString: (obj) ->
			Object.prototype.toString.call(obj) == '[object String]';


		###
		# Check if a value is empty (empty array, empty string, empty object)
		#
		# @param {Object} obj
		# @return {bool}
		###
		isEmpty: (obj) ->
			if obj == null
				return true
			if @isArray(obj) and obj.length
				return obj.length == 0
			if @isString(obj) and val.length
				return val.length == 0

			for own k, v of obj
				return false

			return true


		###
		# Check if a value is an object
		#
		# @param {Object} obj
		# @return {bool}
		###
		isObject: (obj) ->
			return obj != null && typeof obj == 'object'


		###
		# Check if a value is an array
		#
		# @param {Object} obj
		# @return {bool}
		###
		isArray: (obj) ->
			if @nativeIsArray
				return Array.isArray(obj)
			else
				return Object.prototype.toString.call(obj) == '[object Array]'


		###
		# Copy properties from other_objects to destObj, returning destObj.
		#
		# @param {Object} destObj
		# @param {Object} other_objects...
		# @return {Object}
		###
		extend: (destObj, other_objects...) ->
			for other_obj in other_objects
				for own k, v of other_obj
					destObj[k] = v

			return destObj


		###
		# Merge all objects into a new object
		#
		# @param {Object} objects...
		# @return {Object}
		###
		merge: (objects...) ->
			args = objects
			args.unshift({})
			return @extend.apply(@, args)


		###
		# Clones an object
		#
		# @param {Object} obj
		# @param {bool} deep True to do a deep clone
		# @return {Object}
		###
		clone: (obj, deep = false) ->
			if not @isObject(obj) then return obj
			if @isArray(obj)
				result = obj.slice(0)
				if deep
					for index, value in result
						result[index] = @clone(value, true)
			else
				result = {}
				for own key, value of obj
					if deep
						result[key] = @clone(value, true)
					else
						result[key] = value

			return result


		###
    	# Compares two values to see if they are equal.
    	#
    	# If objects, every property of the object is compared with equals()
    	#
    	# @return {bool}
		###
		equals: (obj1, obj2) ->
			if obj1 == obj2
				return true

			if obj1 == null and obj2 == null
				return true

			# NaN
			if obj1 != obj1 && obj2 != obj2
				return true

			if @isObject(obj1)
				if @isArray(obj1)
					if not @isArray(obj2)
						return false

					if obj1.length != obj2.length
						return false

					for k, v in obj1
						if not @equals(obj1[k], obj2[k])
							return false

					return true
				else
					for own k, v of obj1
						if not obj2[k]
							return false
						if obj1[k] != obj2[k]
							return false
					for own k, v of obj2
						if not obj1[k]
							return false
						if obj1[k] != obj2[k]
							return false

					return true

			return false

	return new DeskPRO_Util_Util()