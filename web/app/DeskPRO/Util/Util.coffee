define [
	'DeskPRO/Util/Strings'
], (
	Strings
) ->
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
			return Object.prototype.toString.call(obj) == '[object String]';


		###
		# Check if a value is a string
		#
		# @param {Object} obj
		# @return {bool}
		###
		isBoolean: (obj) ->
			return typeof obj == 'boolean'


		###
		# Check if a value is an integer
		#
		# @param {Object} obj
		# @return {bool}
		###
		isInteger: (obj) ->
			return obj == parseInt(obj)


		###
		# Check if a value is an float
		#
		# @param {Object} obj
		# @return {bool}
		###
		isFloat: (obj) ->
			return obj == parseFloat(obj)


		###
		# Check if a value is a number
		#
		# @param {Object} obj
		# @return {bool}
		###
		isNumber: (obj) ->
			return typeof obj == 'number'


		###
		# Check if a value is undefined
		#
		# @param {Object} obj
		# @return {bool}
		###
		isUndefined: (obj) ->
			return typeof obj == 'undefined'

		###
		# Check if a value is empty (empty array, empty string, empty object, NaN).
    	# Non-collection types like numbers and booleans are never considered empty.
    	# If you need to catch things like an integer 0, use isBlank() instead.
		#
		# @param {Object} obj
		# @return {bool}
		###
		isEmpty: (obj) ->
			if obj == null
				return true
			if @isUndefined(obj)
				return true
			if @isArray(obj) and obj.length?
				return obj.length == 0
			if @isString(obj) and obj.length?
				return obj.length == 0
			if @isNumber(obj)
				return false
			if @isBoolean(obj)
				return false
			if @isFunction(obj)
				return false
			if @isNumber(obj) and isNaN(obj)
				return true

			for own k, v of obj
				return false

			return true


		###
		# Check if a value is blank. This means roughly the same as PHP's "falsey" values: 0, "0", [], ""
		#
		# @param {Object} obj
		# @return {bool}
		###
		isBlank: (obj) ->
			return true if @isEmpty(obj)

			if @isString(obj)
				obj = Strings.trim(obj)
				return true if obj == "" or obj == "0"

			if @isNumber(obj)
				return (obj == 0 or obj == 0.0)

			if @isBoolean(obj)
				return !obj

			return false


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
					for value, index in result
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
		equals: (obj1, obj2, ignorePrivate = true) ->
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

					for v, k in obj1
						if not @equals(obj1[k], obj2[k])
							return false

					return true
				else
					for own k, v of obj1
						continue if k == '__proto__' or k == 'prototype'
						if ignorePrivate
							continue if k.substr(0, 1) == '_' or k.substr(0, 2) == '$$'

						if not @equals(obj1[k], obj2[k])
							return false
					for own k, v of obj2
						continue if k == '__proto__' or k == 'prototype'
						if ignorePrivate
							continue if k.substr(0, 1) == '_' or k.substr(0, 2) == '$$'

						if not @equals(obj1[k], obj2[k])
							return false

					return true

			return false

		###
    	# Dump a variable to a string repr
    	#
    	# @param {mixed} obj
    	# @param {Integer} maxLvl How deep down nested structures to recurse
    	# @return {String}
		###
		dump: (obj, maxLvl = 5, _rlvl = 0, _visited = null) ->
			out = ''
			out += Strings.repeat("\t", _rlvl)

			if obj == null
				out += 'null'
			else if typeof obj == 'number' and isNaN(obj)
				out += 'NaN'
			else if @isInteger(obj)
				out += "int:#{obj}"
			else if @isFloat(obj)
				out += "float:#{obj}"
			else if @isString(obj)
				out += "string:#{obj}"
			else if @isBoolean(obj)
				out += "bool:" + (if obj then "true" else "false")
			else if typeof obj == "undefined"
				out += "undefined"
			else if typeof obj == "function"
				out += "function"
			else if obj instanceof Date
				out += "Date(#{obj})"
			else if obj instanceof RegExp
				out += "RegExp(#{obj})"
			else
				if _visited and _visited.indexOf(obj) != -1
					out += "object(*RECURSION*)"
				else
					if not _visited
						_visited = []

					_visited.push(obj)

					if _rlvl >= maxLvl
						if @isArray(obj)
							out += "array:*MAX LEVEL REACHED*"
						else
							out += "object:*MAX LEVEL REACHED*"
					else
						if _visited and _visited.length
							vis = @clone(_visited)
						else
							vis = []

						if @isArray(obj)
							out += "array:#{obj.length}"
							if obj.length
								out += "\n"
								for v in obj
									out += @dump(v, maxLvl, _rlvl+1, vis)
									out += ",\n"
						else
							out += "object:\n"
							for own k, v of obj
								subs = @dump(v, maxLvl, _rlvl+1, vis)
								out += Strings.repeat("\t", _rlvl+1) + "#{k}: " + Strings.trim(subs) + ",\n"

			return out

	return new DeskPRO_Util_Util()