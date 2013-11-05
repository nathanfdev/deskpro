define ->
	class DeskPRO_Util_Strings
		@CHARS_ALPHANUM     = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
		@CHARS_ALPHANUM_I   = '0123456789abcdefghijklmnopqrstuvwxyz'
		@CHARS_ALPHANUM_IU  = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
		@CHARS_NUM          = '0123456789'
		@CHARS_ALPHA        = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
		@CHARS_ALPHA_I      = 'abcdefghijklmnopqrstuvwxyz'
		@CHARS_ALPHA_IU     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
		@CHARS_SECURE       = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()-_=+{}|[]:;,./<>?'
		@CHARS_KEY          = '23456789ABCDGHJKMNPQRSTWXYZ'
		@CHARS_KEY_ALPHA    = 'ABCDGHJKMNPQRSTWXYZ'
		@CHARS_KEY_NUM      = '23456789'


		###
    	# Generates a random string.
    	#
    	# @param {Integer} len     How long the generated string should be
    	# @param {String}  chars   A string of characters to choose form, or the name of a preset
    	# @return {String}
		###
		random: (len = 8, chars = null) ->
			if not chars
				chars = DeskPRO_Util_Strings.CHARS_ALPHANUM
			else
				charsSet = "CHARS_" + chars.toUpperCase()
				if DeskPRO_Util_Strings[charsSet]?
					chars = DeskPRO_Util_Strings[charsSet]

			string = ""
			maxRange = chars.len - 1

			for i in [0..len]
				rnd = Math.floor((Math.random()*maxRange+1));
				string += chars.charAt(rnd)

			return string


		###
    	# Removes leading and trailing whitespace
    	#
    	# @param {String} string
    	# @return {String}
		###
		trim: (string) ->
			if string.trim?
				return string.trim()

			return string.replace(/^\s+|\s+$/g, '')


		###
    	# Removes leading whitespace

    	# @param {String} string
    	# @return {String}
		###
		trimLeft: (string) ->
			if string.trimLeft?
				return string.trimLeft()

			return string.replace(/^\s+/,'')


		###
    	# Removes trailing whitespace
    	#
    	# @param {String} string
    	# @return {String}
		###
		trimRight: (string) ->
			if string.trimRight?
				return string.trimRight()

			return string.replace(/\s+$/,'')


		###
    	# Given a string with words separated by dashes, underscores or spaces, convert it into
    	# camel case. For example "my-string" and "my_string" becomes myString
    	#
    	# @param {String} string
    	# @return {String}
		###
		toCamelCase: (string) ->
			return string.toLowerCase().replace(/[\-_ ]{1}([a-zA-Z])/g, (match, group1) ->
				return group1.toUpperCase()
			)


		###
    	# Uppercase the first letter of a string
    	#
    	# @param {String} string
    	# @return {String}
		###
		ucFirst: (string) ->
			return string.charAt(0).toUpperCase() + string.slice(1);

	return new DeskPRO_Util_Strings()