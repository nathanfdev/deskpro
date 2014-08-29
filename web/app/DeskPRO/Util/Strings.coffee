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

		@ENTITY_MAP = {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
			'"': '&quot;',
			"'": '&#39;',
			"/": '&#x2F;'
		};

		@TPL_MATCHER = /<%-([\s\S]+?)%>|<%=([\s\S]+?)%>|<%([\s\S]+?)%>|$/g
		@TPL_ESCAPES = {
			"'":      "'",
			'\\':     '\\',
			'\r':     'r',
			'\n':     'n',
			'\t':     't',
			'\u2028': 'u2028',
			'\u2029': 'u2029'
		}
		@TPL_ESCAPER = /\\|'|\r|\n|\t|\u2028|\u2029/g


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
			return string.charAt(0).toUpperCase() + string.slice(1)


		###
    	# Escape special regex chars in a string
    	#
    	# @param {String} regexString
    	# @return {String}
    	###
		escapeRegex: (regexString) ->
			return regexString.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")


		###
    	# Escape special HTML chars in a string
    	#
    	# @param {String} htmlString
    	# @return {String}
    	###
		escapeHtml: (htmlString) ->
			return (htmlString+'').replace(/[&<>"'\/]/g, (s) ->
				return DeskPRO_Util_Strings.ENTITY_MAP[s];
			);


		###
    	# Repeat a str num times
    	#
    	# @param {String} str
    	# @param {Integer} num
    	# @return {String}
    	###
		repeat: (str, num) ->
			if num < 1 then return ''
			res = ''
			while (num--  > 0)
				res += str

			return res


		###
    	# Simple formatter replaces {0}, {1} etc in a string with args passed.
    	#
    	# @param {String} str
    	# @param {mixed} args...  Args to place back in to str
    	# @return {String}
    	###
		format: (str, args...) ->
			return str.replace(/\{(\d+)\}/g, (m, num) ->
				if args[num]?
					return args[num]
				else
					return m
			)


		###
    	# Pad the start of a string with padStr until it is len characters long.
    	#
    	# @param {String} str
    	# @param {String} padStr
    	# @param {Integer} len
    	# @return {String}
    	###
		prePad: (str, padStr, len = 2) ->
			str = str+""
			padStr = padStr+""

			while str.length < len
				str = str + padStr

			if str.length > len
				return str.substring(0, len)

			return str


		###
    	# Pad the end of a string with padStr until it is len characters long.
    	#
    	# @param {String} str
    	# @param {String} padStr
    	# @param {Integer} len
    	# @return {String}
    	###
		pad: (str, padStr, len = 2) ->
			str = str+""
			padStr = padStr+""

			while str.length < len
				str += padStr

			if str.length > len
				return str.substring(0, len)

			return str

		###
    	# Escapes control characters in strings
    	#
    	# @param {String} str
    	# @return {String}
    	###
		addslashes: (str) ->
			return str.
				replace(/\\/g, '\\\\').
				replace(/\u0008/g, '\\b').
				replace(/\t/g, '\\t').
				replace(/\n/g, '\\n').
				replace(/\f/g, '\\f').
				replace(/\r/g, '\\r').
				replace(/'/g, '\\\'').
				replace(/"/g, '\\"')

		###
    	# Generate a MurmurHash3 hash. This is a very very fast non-crypto hash (eg can be used for hash tables etc)
    	#
    	# See: https://github.com/garycourt/murmurhash-js
    	#
    	# @param {String} key
    	# @param {String} seed
    	# @return {String}
		###
		murmurhash3: (key, seed, asHex = true) ->
			remainder = key.length & 3
			bytes = key.length - remainder
			h1 = seed
			c1 = 0xcc9e2d51
			c2 = 0x1b873593
			i = 0

			while (i < bytes)
				k1 = ((key.charCodeAt(i) & 0xff)) |
					((key.charCodeAt(++i) & 0xff) << 8) |
					((key.charCodeAt(++i) & 0xff) << 16) |
					((key.charCodeAt(++i) & 0xff) << 24)

				++i

				k1 = ((((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16))) & 0xffffffff
				k1 = (k1 << 15) | (k1 >>> 17)
				k1 = ((((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16))) & 0xffffffff

				h1 ^= k1
				h1 = (h1 << 13) | (h1 >>> 19)
				h1b = ((((h1 & 0xffff) * 5) + ((((h1 >>> 16) * 5) & 0xffff) << 16))) & 0xffffffff
				h1 = (((h1b & 0xffff) + 0x6b64) + ((((h1b >>> 16) + 0xe654) & 0xffff) << 16))

			k1 = 0;

			if remainder == 3
				k1 ^= (key.charCodeAt(i + 2) & 0xff) << 16
			if remainder == 3 or remainder == 2
				k1 ^= (key.charCodeAt(i + 1) & 0xff) << 8
			if remainder == 3 or remainder == 2 or remainder == 1
				k1 ^= (key.charCodeAt(i) & 0xff)
				k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff
				k1 = (k1 << 15) | (k1 >>> 17)
				k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff
				h1 ^= k1

			h1 ^= key.length

			h1 ^= h1 >>> 16
			h1 = (((h1 & 0xffff) * 0x85ebca6b) + ((((h1 >>> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff
			h1 ^= h1 >>> 13
			h1 = ((((h1 & 0xffff) * 0xc2b2ae35) + ((((h1 >>> 16) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff
			h1 ^= h1 >>> 16

			res = h1 >>> 0

			if asHex
				return res.toString(16)
			else
				return res

		###
    	# Simple JS templates.
    	#
    	# This is based on Underscore.template: http://underscorejs.org/#template
    	#
    	# Except there are added features that make it more lenient to errors.
    	# - Prefix an escape or interpolation string with '@' to wrap it in a try/catch:
    	#     <%- @possibleFailure() %>
    	#
    	# - If you are outputting simple variables, they are automatically wrapped with undefined checks
    	#     <%- this.doesnt.exist %>
    	#   The above wont result in an error, just an empty string. Pass settings.disableSafeVar to disable this.
    	#
    	# @param {String} text The template text
    	# @param {Object} settings
    	# @return Function
		###
		simpleTemplate: (text, settings = {}) ->
			STRINGS = @
			index = 0
			source = "var __t, __p = '', __j=Array.prototype.join, __escape=Strings.escapeHtml, "
			source += "print=function(){__p+=__j.call(arguments,'');};\n"
			source += "with(obj||{}){\n";
			source += "__p+='"

			resolveVar = (varname) ->
				if settings.disableSafeVar then return varname
				m = varname.match(/^\s*([a-zA-Z0-9\_\\$\\.]+)\s*$/)
				if not m then return varname

				testparts = []
				names = []
				parts = m[1].split('.')
				for p in parts
					names.push(p)
					testparts.push("typeof " + names.join('.') + " !== 'undefined' && " + names.join('.') + " !== null")

				return "((" + testparts.join(' && ') + ") ? " + varname + " : '')";

			text.replace(DeskPRO_Util_Strings.TPL_MATCHER, (match, escape, interpolate, evaluate, offset) ->
				source += text.slice(index, offset).replace(DeskPRO_Util_Strings.TPL_ESCAPER, (m) -> return '\\' + DeskPRO_Util_Strings.TPL_ESCAPES[m])

				if escape
					escape = STRINGS.trim(escape)
					if escape[0] == '@'
						source += "';\ntry { __p+= ((__t=(" + escape.substring(1) + "))==null?'':__escape(__t)); } catch (e) {}\n__p+='";
					else
						source += "'+\n((__t=(" + resolveVar(escape) + "))==null?'':__escape(__t))+\n'";

				if interpolate
					interpolate = STRINGS.trim(interpolate)
					if interpolate[0] == '@'
						source += "';\ntry { __p+= ((__t=(" + interpolate.substring(1) + "))==null?'':__t+''); } catch (e) {}\n__p+='";
					else
						source += "'+\n((__t=(" + resolveVar(interpolate) + "))==null?'':__t+'')+\n'";

				if evaluate
					source += "';\n" + evaluate + "\n__p+='";

				index = offset + match.length
				return match
			)

			source += "';\n}\nreturn __p;\n"

			try
				render = new Function('obj', 'Strings', source);
			catch e
				console.log("Error compiling source: " + source)
				e.source = source
				throw e

			fn = (data) ->
				return render.call(this, data, window.STRINGS)

			fn.source = source

			return fn


	window.STRINGS = new DeskPRO_Util_Strings()
	return new DeskPRO_Util_Strings()