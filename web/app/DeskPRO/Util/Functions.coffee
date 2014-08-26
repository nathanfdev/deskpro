define ->
	class DeskPRO_Util_Functions
		###
    	# Returns a function that will be called wait ms after the last time it was
    	# invoked. E.g., if it was called 3 times in a row, it wouldnt actually be invoked
		# 3 times because it happened before wait time had passed.
    	#
    	# @param {Function} fn
    	# @param {Integer} wait
    	# @param {bool} immediate
		###
		debounce: (fn, wait, immediate) ->
			timeout = null
			res = null

			return ->
				self = this
				args = arguments

				later = ->
					timeout = null
					if not immediate then res = fn.apply(self, args)

				callNow = immediate && !timeout
				clearTimeout(timeout)
				timeout = setTimeout(later, wait)
				if callNow then res = fn.apply(self, args)
				return res

	return new DeskPRO_Util_Functions()