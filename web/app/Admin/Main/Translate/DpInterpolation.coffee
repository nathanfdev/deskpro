define ->
	Admin_Main_Translate_DpInterpolation = [ ->
		choosePlural = (text, number) ->
			parts = text.split('|');

			if number == 0 || number != 1
				return parts[1]
			else
				return parts[0]

		regexQuote = (strRegex) ->
			strRegex.replace(/([.?*+^$[\]\\(){}-])/g, "\\$1")

		return {
			setLocale: (locale) ->
				return

			getInterpolationIdentifier: ->
				return 'dp'

			interpolate: (text, vars) ->
				if not vars then return text

				if vars.count_length?
					vars.count = vars.count_length.length

				if vars.count?
					text = choosePlural(text, parseInt(vars.count))

				is_raw = vars.as_raw?

				for own key, value of vars
					re = new RegExp('\{\{\s*' + regexQuote(key) + '\s*\}\}' , 'g')

					if is_raw
						text = text.replace(re, value)
					else
						text = text.replace(re, _.escape(value))

				return text;
		}
	]

	return Admin_Main_Translate_DpInterpolation