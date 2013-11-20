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
			return ->
				self = @
				args = arguments
				time = (new Date()).getTime()

				later = ->
					last = (new Date()).getTime() - time
					if last < wait
						timeout = setTimeout(later, wait - last)
					else
						timeout = null
						if not immediate
							res = fn.apply(self, args)
							self = null
							args = null

				if not timeout
					timeout = setTimeout(later, wait)

				if immediate and not timeout
					res = fn.apply(self, args)
					self = null
					args = null

				return res

	return new DeskPRO_Util_Functions()